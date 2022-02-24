<?php

namespace OES\Versioning;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post;
use function OES\ACF\oes_get_field;
use function OES\ACF\get_all_object_fields;


if (!class_exists('Versioning')) :

    /**
     * Class Versioning
     *
     * This class hooks processing concerning the feature "Versioning" to post actions.
     * "Versioning" includes a version controlling post type "Parent" and a version controlled post type "Version".
     *
     * The feature "Translating" is a special case of the feature "Versioning" where a version controlling post
     * "Origin Post" is linked to a post "Translation" of the same post type.
     *
     */
    class Versioning
    {

        /**
         * Versioning constructor.
         */
        function __construct()
        {
            /* Hide permalink for parent post type. */
            add_filter('get_sample_permalink_html', [$this, 'admin_hide_permalink_for_parent_posts'], 10, 2);

            /* Register a new meta box for parent post types and post types that are controlled by a parent post
            type. */
            add_action('add_meta_boxes', [$this, 'admin_version_meta_boxes']);

            /* Add post meta information for parent post types. */
            add_action('save_post', [$this, 'processing_add_parent_post_meta'], 10, 2);

            /* add actions */
            add_action('admin_action_oes_copy_version', [$this, 'oes_copy_version']);
            add_action('admin_action_oes_create_version', [$this, 'oes_create_version']);
            add_action('admin_action_oes_create_translation', [$this, 'oes_create_translation']);
        }


        /**
         * Hide permalink for post types that are controlled by a parent post type.
         *
         * @param string $return The permalink before it is being displayed.
         * @param int $post_id The post ID.
         * @return string Return the permalink or empty string.
         */
        function admin_hide_permalink_for_parent_posts(string $return, int $post_id): string
        {
            /* check if post type is controlled by a parent post type and return empty string */
            $post = get_post($post_id);
            if (get_version_post_type($post->post_type)) return '';

            /* if post type is not controlled by a parent post type, return string as it was before the hook. */
            return $return;
        }


        /**
         * Register a meta box for a post type that is controlling other post types ("parent" post type) and post types
         * that are controlled by parent post types.
         * Meta boxes are sorted alphabetically by default. ACF meta boxes have the context 'normal' and priority
         * 'high'. To place the meta box above the acf form fields use the same parameters.
         * Start new meta box slug with '0' to appear after the title.
         *
         * @param string $post_type The current post type, passed by hook.
         */
        function admin_version_meta_boxes(string $post_type)
        {
            /* check if post type is controlling other posts */
            if (get_version_post_type($post_type)) {

                /* set callback arguments */
                $args['post'] = $post_type;
                $args['versionPostType'] = get_version_post_type($post_type);
                add_meta_box('oes-version-parent',
                    '<span>' . __('Versions', 'oes') . '</span>',
                    [$this, 'meta_box_parent_post'],
                    null,
                    'normal',
                    'high',
                    $args
                );

            } /* check if post type is controlled by a parent post type or allows translation */
            elseif (get_parent_post_type($post_type)) {

                /* set callback arguments */
                $args['post'] = $post_type;

                /* check if post type has gutenberg editor */
                oes_check_if_gutenberg($post_type) ?
                    add_action('admin_footer', [$this, 'notice_version_post']) :
                    add_action('admin_notices', [$this, 'notice_version_post_classic']);
            }


            /* show version control tab according to option */
            if(get_option('oes_admin-hide_version_tab')) add_action('admin_footer', [$this, 'hide_versioning_tab']);
        }


        /**
         * Add post meta information when updating a version controlling or version controlled post.
         *
         * @param int $post_id The current post id.
         * @param WP_Post $post The current post.
         * @return void
         */
        function processing_add_parent_post_meta(int $post_id, WP_Post $post)
        {
            /* check if post type has parent post type, post is published */
            if (get_parent_post_type($post->post_type) && 'trash' !== $post->post_status) {

                /* update parent post */
                $parentID = get_parent_id($post_id);

                /* break if post has no parent post and therefore is orphaned post */
                if (!$parentID) return;

                /* get versions and current version */
                $currentVersionID = get_current_version_id($parentID);

                /* check if new post should be current version by comparing the version number */
                if($post->post_status === 'publish'){
                    if (!$currentVersionID) set_current_version_id($parentID, $post_id);
                    elseif ($currentVersionID !== $post_id) {

                        /* compare versions */
                        $currentVersionNumber = get_version_field($currentVersionID);
                        $postVersionNumberText = get_version_field($post_id);
                        $postVersionNumber = get_version_number_from_string($postVersionNumberText);

                        /* update parent post if version number is bigger than current version number */
                        if (floatval($currentVersionNumber) < floatval($postVersionNumber[0] ?? $postVersionNumberText))
                            set_current_version_id($parentID, $post_id);
                    }
                }

                /* add hook */
                do_action('oes/modify_parent_when_saving_child', $parentID, $post_id, $post->post_type);

            } elseif (get_version_post_type($post->post_type) && 'trash' !== $post->post_status) {

                /* check if current version is set */
                $currentVersionID = get_current_version_id($post_id);
                $versions = get_all_version_ids($post_id);
                if (!empty($versions)) {

                    /* get version with the highest version number */
                    $max = get_max_version_number($post_id, true);
                    if($max && $max !== $currentVersionID) set_current_version_id($post_id, $max);
                }
            }
        }


        /**
         * Copy from current version.
         */
        function oes_copy_version()
        {

            /* get post id */
            $parentID = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));

            /* validate nonce */
            $nonce = $_REQUEST['nonce'];
            if (wp_verify_nonce($nonce, 'oes-copy-version-' . $parentID)
                && current_user_can('edit_posts')) {

                /* throw error if no post found or wrong action */
                if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action'])
                        && 'oes_copy_version' == $_REQUEST['action']))) wp_die('No post!');

                /* prepare post */
                $currentPostID = get_current_version_id($parentID);
                if ($currentPost = get_post($currentPostID)) {

                    /* check if gutenberg */
                    $gutenberg = oes_check_if_gutenberg($currentPost->post_type);

                    /* get author ------------------------------------------------------------------------------------*/
                    $currentUser = wp_get_current_user();

                    /* compute title ---------------------------------------------------------------------------------*/

                    /* get parent post title */
                    $parentPostDisplay = get_the_title($parentID);

                    /* get version number */
                    $maxVersion = get_max_version_number($parentID);
                    $newVersion = increment_version_number($maxVersion);

                    /* set title */
                    $postTitle = $parentPostDisplay . ' (Version ' . $newVersion . ')';


                    /**
                     * Filters the post title when copying from an older version before inserting.
                     *
                     * @param string $postTitle The post title.
                     * @param string $parentPostDisplay The title of the parent post.
                     * @param string $newVersion The post version.
                     */
                    if (has_filter('oes/create_child_version_title'))
                        $postTitle = apply_filters('oes/create_child_version_title',
                            $postTitle, $parentPostDisplay, $newVersion);


                    /* prepare post data array -----------------------------------------------------------------------*/
                    $args = [
                        'comment_status' => $currentPost->comment_status,
                        'ping_status' => $currentPost->ping_status,
                        'post_author' => $currentUser->ID,
                        'post_content' => $gutenberg ? wp_slash($currentPost->post_content) : $currentPost->post_content,
                        'post_excerpt' => $currentPost->post_excerpt,
                        'post_parent' => $currentPost->post_parent,
                        'post_password' => $currentPost->post_password,
                        'post_status' => 'draft',
                        'post_title' => $postTitle,
                        'post_type' => $currentPost->post_type,
                        'to_ping' => $currentPost->to_ping,
                        'menu_order' => $currentPost->menu_order
                    ];


                    /**
                     * Filters the post args when copying from an older version before inserting.
                     *
                     * @param array $args The post args.
                     */
                    if (has_filter('oes/copy_version')) $args = apply_filters('oes/copy_version', $args);


                    /* add post --------------------------------------------------------------------------------------*/
                    $newPostID = wp_insert_post($args);

                    /* copy tags -------------------------------------------------------------------------------------*/
                    $taxonomies = get_object_taxonomies($currentPost->post_type);
                    if (!empty($taxonomies) && is_array($taxonomies))
                        foreach ($taxonomies as $taxonomy) {
                            $postTerms = wp_get_object_terms($currentPostID, $taxonomy, ['fields' => 'slugs']);
                            wp_set_object_terms($newPostID, $postTerms, $taxonomy, false);
                        }

                    /* copy post meta (acf fields) -------------------------------------------------------------------*/

                    /* get all fields for this post type */
                    $postFields = get_all_object_fields($currentPost->post_type, false);

                    /* loop through fields and store values for new version */
                    foreach ($postFields as $field) {
                        $fieldValue = oes_get_field($field['key'], $currentPostID);

                        /* strip textarea of html tags */
                        if ($field['type'] == 'textarea') $fieldValue = strip_tags($fieldValue);

                        update_field($field['key'], $fieldValue, $newPostID);
                    }

                    /* link post to parent post ----------------------------------------------------------------------*/
                    set_parent_id($newPostID, $parentID);

                    /* update parent fields --------------------------------------------------------------------------*/

                    /* add this post id to version ids of parent post */
                    $currentValue = get_all_version_ids($parentID) ? get_all_version_ids($parentID) : [];
                    if (!in_array($newPostID, $currentValue)) $currentValue = array_merge([$newPostID], $currentValue);
                    set_all_version_ids($parentID, $currentValue);

                    /* add hook */
                    do_action('oes/modify_parent_when_saving_child', $parentID, $newPostID, $currentPost->post_type);

                    /* overwrite version field -----------------------------------------------------------------------*/
                    set_version_field($newPostID, $newVersion);

                    /* redirect --------------------------------------------------------------------------------------*/
                    $redirectLink = 'post.php?action=edit&post=' . $newPostID;


                    /**
                     * Filters the redirect link when copying from an older version before inserting.
                     *
                     * @param string $redirectLink The redirect link.
                     * @param string $newPostID The post ID.
                     * @param string $parentID The parent post ID.
                     */
                    if (has_filter('oes/copy_version_redirect_link'))
                        $redirectLink = apply_filters('oes/copy_version_redirect_link',
                            $redirectLink, $newPostID, $parentID);


                    wp_redirect(admin_url($redirectLink));

                } else
                    wp_die('Post creation failed while copying from post with ID ' . $currentPostID . '.');
            } else  wp_die('Security check issue. Please try again.');
        }


        /**
         * Create a new version.
         */
        function oes_create_version()
        {

            /* get post id */
            $parentID = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));

            /* validate nonce */
            $nonce = $_REQUEST['nonce'];
            if (wp_verify_nonce($nonce, 'oes-create-version-' . $parentID)
                && current_user_can('edit_posts')) {

                /* throw error if no post found or wrong action */
                if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action'])
                        && 'oes_create_version' == $_REQUEST['action']))) wp_die('No post!');

                /* prepare post */
                if ($parent = get_post($parentID)) {

                    /* get author ------------------------------------------------------------------------------------*/
                    $currentUser = wp_get_current_user();

                    /* compute title ---------------------------------------------------------------------------------*/

                    /* get parent post title */
                    $parentPostDisplay = get_the_title($parentID);

                    /* get version number */
                    $maxVersion = get_max_version_number($parentID);
                    $newVersion = increment_version_number($maxVersion);

                    /* set title */
                    $postTitle = $parentPostDisplay . ' (Version ' . $newVersion . ')';


                    /**
                     * Filters the post title when copying from an older version before inserting.
                     *
                     * @param string $postTitle The post title.
                     * @param string $parentPostDisplay The title of the parent post.
                     * @param string $newVersion The post version.
                     */
                    if (has_filter('oes/create_child_version_title'))
                        $postTitle = apply_filters('oes/create_child_version_title',
                            $postTitle, $parentPostDisplay, $newVersion);


                    /* prepare post data array -----------------------------------------------------------------------*/
                    $args = [
                        'post_author' => $currentUser->ID,
                        'post_status' => 'draft',
                        'post_title' => $postTitle,
                        'post_type' => get_version_post_type($parent->post_type),
                    ];


                    /**
                     * Filters the post args when creating version before inserting.
                     *
                     * @param array $args The post args.
                     */
                    if (has_filter('oes/create_version')) $args = apply_filters('oes/create_version', $args);


                    /* add post --------------------------------------------------------------------------------------*/
                    $newPostID = wp_insert_post($args);

                    /* update parent fields --------------------------------------------------------------------------*/
                    $currentVersions = get_all_version_ids($parentID);
                    if (!empty($currentVersions)) $currentVersions[] = $newPostID;
                    else $currentVersions = [$newPostID];
                    set_all_version_ids($parentID, $currentVersions);

                    /* add hook */
                    do_action('oes/modify_parent_when_saving_child', $parentID, $newPostID, $parent->post_type);

                    /* overwrite version field -----------------------------------------------------------------------*/
                    set_version_field($newPostID, $newVersion);

                    /* link post to parent post ----------------------------------------------------------------------*/
                    set_parent_id($newPostID, $parentID);

                    /* redirect --------------------------------------------------------------------------------------*/
                    $redirectLink = 'post.php?action=edit&post=' . $newPostID;


                    /**
                     * Filters the redirect link when creating a version before inserting.
                     *
                     * @param string $redirectLink The redirect link.
                     * @param string $newPostID The post ID.
                     * @param string $parentID The parent post ID.
                     */
                    if (has_filter('oes/create_version_redirect_link'))
                        $redirectLink = apply_filters('oes/create_version_redirect_link',
                            $redirectLink, $newPostID, $parentID);


                    wp_redirect(admin_url($redirectLink));

                } else wp_die('Post creation failed for parent post with ID ' . $parentID . '.');
            } else wp_die('Security check issue. Please try again.');
        }


        /**
         * Create a translation parent.
         */
        function oes_create_translation()
        {

            /* get post id */
            $parentID = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));

            /* validate nonce */
            $nonce = $_REQUEST['nonce'];
            if (wp_verify_nonce($nonce, 'oes-create-translation-' . $parentID)
                && current_user_can('edit_posts')) {

                /* throw error if no post found or wrong action */
                if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action'])
                        && 'oes_create_version' == $_REQUEST['action']))) {
                    wp_die('No post!');
                }

                /* prepare post */
                if ($parent = get_post($parentID)) {

                    /* get author ------------------------------------------------------------------------------------*/
                    $currentUser = wp_get_current_user();

                    /* compute title ---------------------------------------------------------------------------------*/
                    $postTitle = '(Translation) ' . get_the_title($parentID);


                    /**
                     * Filters the post title when creating a translation version before inserting.
                     *
                     * @param string $postTitle The post title.
                     * @param string $parentID The parent post ID.
                     */
                    if (has_filter('oes/create_translation_title'))
                        $postTitle = apply_filters('oes/create_translation_title', $postTitle, $parentID);


                    /* prepare post data array -----------------------------------------------------------------------*/
                    $args = [
                        'post_author' => $currentUser->ID,
                        'post_status' => 'draft',
                        'post_title' => $postTitle,
                        'post_type' => $parent->post_type,
                    ];


                    /**
                     * Filters the post args when creating a translation version before inserting.
                     *
                     * @param array $args The post args.
                     */
                    if (has_filter('oes/create_translation_version'))
                        $args = apply_filters('oes/create_translation_version', $args);


                    /* add post --------------------------------------------------------------------------------------*/
                    $newPostID = wp_insert_post($args);

                    /* update translation field ----------------------------------------------------------------------*/
                    set_translation_id($parentID, $newPostID);
                    set_translation_id($newPostID, $parentID);


                    /* redirect --------------------------------------------------------------------------------------*/
                    $redirectLink = 'post.php?action=edit&post=' . $newPostID;


                    /**
                     * Filters the redirect link when creating a translation version before inserting.
                     *
                     * @param string $redirectLink The redirect link.
                     * @param string $newPostID The post ID.
                     * @param string $parentID The parent post ID.
                     */
                    if (has_filter('oes/create_translation_version_redirect_link'))
                        $redirectLink = apply_filters('oes/create_translation_version_redirect_link',
                            $redirectLink, $newPostID, $parentID);


                    wp_redirect(admin_url($redirectLink));

                } else wp_die('Translation creation failed for parent post with ID ' . $parentID . '.');
            } else wp_die('Security check issue. Please try again.');
        }


        /**
         * Callback function for the meta box of "parent" post types.
         *
         * @param WP_Post $post The current post.
         * @param array $callbackArgs Custom arguments passed by add_meta_box.
         */
        function meta_box_parent_post(WP_Post $post, array $callbackArgs)
        {
            /* get all versions of this parent post. */
            $currentValue = get_all_version_ids($post->ID);

            /* sort current versions by ID */
            if (!empty($currentValue)) rsort($currentValue);

            /* get current version of this parent post. */
            $currentVersion = get_current_version_id($post->ID);

            /* check if translation exists */
            $translationParent = get_translation_id($post->ID);
            if ($translationParent && !$translationParent instanceof WP_Post && is_string($translationParent))
                $translationParent = get_post($translationParent) ?? false;

            /* Set languages */
            $thisLanguage = oes_get_post_language($post->ID) ?? false;
            $translationLanguage = '';
            if ($thisLanguage)
                foreach (OES()->languages as $key => $language)
                    if ($key !== $thisLanguage) $translationLanguage = $language['label'];

            /* display version information after the title */
            ?>
            <div id="oes-post-parent-version-information"><?php

                if (!empty($currentValue)) :
                    ?>
                    <div><?php
                        display_version_table($currentValue, $currentVersion); ?>
                    </div>
                <?php
                else :?>
                    <div class="parent-no-version"><?php
                    _e('No versions for this parent found.', 'oes'); ?></div><?php
                endif; ?>
                <div class="oes-versioning-button-groups">
                    <a href="admin.php?action=oes_create_version&post=<?php echo $post->ID;
                    ?>&nonce=<?php echo wp_create_nonce('oes-create-version-' . $post->ID);
                    ?>" class="button button-primary button-large" title="Create new version" rel="permalink"><?php
                        _e('Create New Version', 'oes'); ?></a><?php
                    if (!empty($currentVersion)) :?>
                        <a href="admin.php?action=oes_copy_version&post=<?php echo $post->ID;
                        ?>&nonce=<?php echo wp_create_nonce('oes-copy-version-' . $post->ID);
                        ?>" class="button-primary button-large" title="Clone current post" rel="permalink"><?php
                        _e('Duplicate Current Version', 'oes'); ?></a><?php
                    endif;
                    if (empty($translationParent) && $translationLanguage) :?>
                        <a href="admin.php?action=oes_create_translation&post=<?php echo $post->ID;
                        ?>&nonce=<?php echo wp_create_nonce('oes-create-translation-' . $post->ID); ?>"
                           class="button button-large"
                           title="Create Language Version" rel="permalink"><?php
                        printf(__('Create Language Version [%s]', 'oes'), $translationLanguage); ?></a><?php
                    endif;
                    ?>
                </div><?php

                if (!empty($translationParent)) :

                    /* get all versions of this translating parent post. */
                    $currentTranslationValue = get_all_version_ids($translationParent);

                    /* get translated current version of this parent post. */
                    $currentTranslationVersion = get_current_version_id($translationParent);

                    /* sort current versions by ID */
                    if (!empty($currentTranslationValue)) rsort($currentTranslationValue);
                    ?>
                    <hr>
                    <div class="oes-versioning-translation-info">
                    <div class="oes-replace-postbox-h2"><?php
                        printf(__('Language Versions [%s]', 'oes'), $translationLanguage); ?></div>
                    <div><?php _e('Parent: ', 'oes'); ?>
                    <a href="<?php echo get_edit_post_link($translationParent); ?>">
                        <?php echo get_post($translationParent)->post_title; ?>
                    </a>
                    </div><?php
                    if (!empty($currentTranslationValue)):?>
                        <div class="oes-versioning-translation-posts"><?php
                        display_version_table($currentTranslationValue, $currentTranslationVersion); ?>
                        </div><?php
                    endif; ?>
                    </div><?php
                endif; ?>
            </div>
            <?php
        }


        /**
         * Notice for "version" post types with block editor. For post types that are controlled by a parent
         * post type display a link to the parent post and current version after the title.
         */
        function hide_versioning_tab()
        {
            ?>
                <script>
                    let tabs = document.querySelectorAll("[data-key = 'oes_versioning_tab']");
                    let i = 0, n = tabs.length;
                    for (; i < n; i++) {
                        tabs[i].style.display = "none";
                    }
                </script>
            <?php
        }


        /**
         * Notice for "version" post types with block editor. For post types that are controlled by a parent
         * post type display a link to the parent post and current version after the title.
         */
        function notice_version_post()
        {
            $noticeString = $this->get_notice_string();
            if (!empty($noticeString)):?>
                <script>
                    (function (wp) {
                        wp.data.dispatch('core/notices').createNotice(
                            'warning',
                            '<?php echo str_replace('\'', '\\\'', $noticeString);?>',
                            {
                                isDismissible: false,
                                __unstableHTML: true,
                            }
                        );
                    })(window.wp);
                </script>
            <?php
            endif;
        }


        /**
         * Notice for "version" post types with classic editor. For post types that are controlled by a parent
         * post type display a link to the parent post and current version after the title.
         */
        function notice_version_post_classic()
        {
            echo '<div class="notice notice-warning"><p>' . $this->get_notice_string() . '</p></div>';
        }


        /**
         * Get the notice string with versioning information for the post.
         */
        function get_notice_string(): string
        {
            global $post;

            /* get parent post */
            if ($parentID = get_parent_id($post->ID)) {

                /* get information about parent post */
                $parentPost = get_post($parentID);

                /* get current version from parent post */
                $currentPost = get_post(get_current_version_id($parentID));

                $noticeString = '<span>' . __('This post is version controlled. ', 'oes') . '</span>' .
                    '<div><strong>' .
                    (get_post_type_object($parentPost->post_type)->labels->singular_name ?? $parentPost->post_type) .
                    ': </strong>' .
                    sprintf('<a href="%s">%s</a>', get_edit_post_link($parentID), $parentPost->post_title) .
                    '</div>';

                /* check for most current version */
                if ($currentPost && $currentPost->ID && $currentPost->ID != $post->ID)
                    $noticeString .= '<div><strong>' .
                        __('Currently Displayed Version: ', 'oes') .
                        '</strong>' .
                        sprintf('<a href="%s">%s</a>', get_edit_post_link($currentPost), $currentPost->post_title) .
                        '</div>';
                else $noticeString .= '<div>' . __('This is the currently displayed version.', 'oes') . '</div>';
            }
            else $noticeString = '<div>' . __('This post is not version controlled and will not be displayed on the ' .
                    'website. Create a parent post first.', 'oes') . '</div>';

            return $noticeString;
        }
    }

    /* create new instance of feature 'Versioning' and hook processing to post actions. */
    new Versioning();

endif;


/**
 * Get the version post type for this post type from global settings.
 *
 * @param string $postType The post type.
 * @return false|mixed Returns the version post type from global settings or false if not found.
 */
function get_version_post_type(string $postType)
{
    return OES()->post_types[$postType]['version'] ?? false;
}


/**
 * Get the parent post type for this post type from global settings.
 *
 * @param string $postType The post type.
 * @return false|mixed Returns the parent post type from global settings or false if not found.
 */
function get_parent_post_type(string $postType)
{
    return OES()->post_types[$postType]['parent'] ?? false;
}


/**
 * If the post is a parent post and controls post versions get the post ID of the current version.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns the post ID of the current version.
 */
function get_current_version_id($postID)
{
    return oes_get_field('field_oes_versioning_current_post', $postID);
}


/**
 * Connect a parent post to the current post version by updating the parent post field.
 *
 * @param string|int $parentID The post ID.
 * @param string|int $currentPostID The current version post ID.
 */
function set_current_version_id($parentID, $currentPostID)
{
    update_field('field_oes_versioning_current_post', $currentPostID, $parentID);
}


/**
 * Get the version field of this post.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns the value of the version field.
 */
function get_version_field($postID)
{
    return oes_get_field('field_oes_post_version', $postID);
}


/**
 * Set the version field of this post.
 *
 * @param string|int $postID The post ID.
 * @param string|int $version The post ID of the parent post.
 */
function set_version_field($postID, $version)
{
    update_field('field_oes_post_version', $version, $postID);
}


/**
 * If the post is connected to a parent post, get the post ID of the parent post.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns the post ID of the parent post or empty.
 */
function get_parent_id($postID)
{
    return oes_get_field('field_oes_versioning_parent_post', $postID);
}


/**
 * Connect a post to a parent post by adding the post ID to the parent post field.
 *
 * @param string|int $postID The post ID.
 * @param string|int $parentID The post ID of the parent post.
 */
function set_parent_id($postID, $parentID)
{
    update_field('field_oes_versioning_parent_post', $parentID, $postID);
}


/**
 * If the post is a parent post and controls post versions get all post IDs of connected post versions.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns an array of all connected post IDs or empty.
 */
function get_all_version_ids($postID)
{
    return oes_get_field('field_oes_versioning_posts', $postID);
}


/**
 * Connect a parent post to post versions by updating the parent post field.
 *
 * @param string|int $parentID The post ID.
 * @param array $versionIDs The post version IDs.
 */
function set_all_version_ids($parentID, array $versionIDs)
{
    update_field('field_oes_versioning_posts', $versionIDs, $parentID);
}


/**
 * If the post is a parent post and controls post versions get translation parents.
 *
 * @param string|int $postID The post ID.
 * @return mixed Returns the post ID of the translation parent or empty.
 */
function get_translation_id($postID)
{
    return oes_get_field('field_connected_parent', $postID);
}


/**
 * Set translation parent for post.
 *
 * @param string|int $postID The post ID.
 * @param string|int $translationPostID The post ID of the translation parent.
 */
function set_translation_id($postID, $translationPostID)
{
    update_field('field_connected_parent', $postID, $translationPostID);
}


/**
 * Get a version number from a string. A version number must match the pattern 0.9, 1, 1.3, 2.50, etc.
 *
 * @param string $versionText The version text.
 * @return array Return the extracted version number or the split version text.
 */
function get_version_number_from_string(string $versionText): array
{
    preg_match('/(\d+).?(\d*)/', $versionText, $splitVersionText);
    return $splitVersionText;
}


/**
 * Get the maximum version number of all connected post version of a parent post.
 *
 * @param string|int $parentID The post ID of the parent post.
 * @return false|int|string Return the maximum version number.
 */
function get_max_version_number($parentID, bool $returnPostID = false)
{

    /* get all version */
    $currentValue = get_all_version_ids($parentID);

    /* loop through all versions and check version field */
    $versionForComparison = [];
    $versionForComparisonPublished = [];
    if (!empty($currentValue)) {
        foreach ($currentValue as $versionPostID) {

            /* strip to #.#*/
            $versionString = get_version_field($versionPostID);
            $versionText = get_version_number_from_string($versionString) ?: [];
            $prepareInt = (sizeof($versionText) > 1) ? $versionText[1] * 1000 + $versionText[2] : 1;

            $versionForComparison[$prepareInt] = $versionString;

            if(get_post($versionPostID)->post_status === 'publish')
                $versionForComparisonPublished[$prepareInt] = $versionPostID;
        }
    }
    $maxValue = !empty($versionForComparison) ? $versionForComparison[max(array_keys($versionForComparison))] : 1;
    $maxValuePublish = !empty($versionForComparisonPublished) ?
        $versionForComparisonPublished[max(array_keys($versionForComparisonPublished))] : false;

    /* return maximum value of all version numbers */
    return empty($versionForComparison) ? false : ($returnPostID ? $maxValuePublish : $maxValue);
}


/**
 * Increment the version text by incrementing the number after the digit, e.g. increment 1.4 to 1.41 and 1.21 to 1.22.
 *
 * @param string $versionText The version text.
 * @return string Returns the incremented version number.
 */
function increment_version_number(string $versionText): string
{
    $splitVersion = get_version_number_from_string($versionText);

    /* no recognized pattern */
    if (empty($splitVersion)) {
        return '1.0';
    } /* max version has the pattern #.# */
    elseif (!empty($splitVersion[2])) {
        $newInteger = intval($splitVersion[2]) + 1;
        return $splitVersion[1] . '.' . $newInteger;
    } /* max version has the pattern # */
    else {
        return $splitVersion[1] . '.1';
    }

}


/**
 * Display posts as table.
 *
 * @param array $postIDs The version post IDs.
 * @param int|string $currentVersionID The current version post ID.
 */
function display_version_table(array $postIDs, $currentVersionID)
{
    ?>
    <table class="oes-versioning-post-list">
    <thead>
    <tr>
        <th><?php _e('ID', 'oes'); ?></th>
        <th><?php _e('Title', 'oes'); ?></th>
        <th><?php _e('Version', 'oes'); ?></th>
        <th><?php _e('Status', 'oes'); ?></th>
        <th><?php _e('Author', 'oes'); ?></th>
    </tr>
    </thead>
    <tbody><?php

    /* loop through all versions and link to the post */
    foreach ($postIDs as $versionPostID) :
        if ($versionPost = get_post($versionPostID)): ?>
        <tr class="<?php echo ($currentVersionID == $versionPostID) ? 'oes-current-post' : ''; ?>">
            <td><?php echo $versionPostID; ?></td>
            <td><?php echo($versionPost ?
                    oes_get_html_anchor($versionPost->post_title, get_edit_post_link($versionPostID)) :
                    'Title missing'); ?></td>
            <td><?php echo get_version_field($versionPost->ID); ?></td>
            <td><?php echo $versionPost->post_status; ?></td>
            <td><?php echo get_the_author_meta('display_name', $versionPost->post_author); ?></td>
            </tr><?php
        endif;
    endforeach; ?>
    </tbody>
    </table><?php
}