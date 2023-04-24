<?php

namespace OES\Versioning;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post;
use function OES\ACF\oes_get_field;
use function OES\ACF\get_all_object_fields;


/**
 * Versioning
 *
 * This feature hooks processing concerning the feature "Versioning" to post actions.
 * "Versioning" includes a version controlling post type "Parent" and a version controlled post type "Version".
 *
 * The feature "Translating" is a special case of the feature "Versioning" where a version controlling post
 * "Origin Post" is linked to a post "Translation" of the same post type.
 *
 */
add_filter('get_sample_permalink_html', 'OES\Versioning\get_sample_permalink_html', 10, 2);
add_action('add_meta_boxes', 'OES\Versioning\add_meta_boxes');
add_action('save_post', 'OES\Versioning\save_post', 10, 2);
add_action('admin_action_oes_copy_version', 'OES\Versioning\admin_action_oes_copy_version');
add_action('admin_action_oes_create_version', 'OES\Versioning\admin_action_oes_create_version');
add_action('admin_action_oes_create_translation', 'OES\Versioning\admin_action_oes_create_translation');


/**
 * Hide permalink for post types that are controlled by a parent post type.
 *
 * @param string $return The permalink before it is being displayed.
 * @param int $post_id The post ID.
 * @return string Return the permalink or empty string.
 */
function get_sample_permalink_html(string $return, int $post_id): string
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
 * @return void
 */
function add_meta_boxes(string $post_type): void
{
    /* check if post type is controlling other posts */
    if (get_version_post_type($post_type)) {

        /* set callback arguments */
        $args['post'] = $post_type;
        $args['versionPostType'] = get_version_post_type($post_type);
        add_meta_box('oes-version-parent',
            '<span>' . __('Versions', 'oes') . '</span>',
            '\OES\Versioning\meta_box_parent_post',
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
            add_action(
            /**
             * Notice for "version" post types with block editor. For post types that are controlled by a parent
             * post type display a link to the parent post and current version after the title.
             */
                'admin_footer',
                function () {
                    $noticeString = \OES\Versioning\get_notice_string();
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
                }) :
            add_action(
            /**
             * Notice for "version" post types with classic editor. For post types that are controlled by a parent
             * post type display a link to the parent post and current version after the title.
             */
                'admin_notices',
                function () {
                    echo '<div class="notice notice-warning"><p>' . \OES\Versioning\get_notice_string() . '</p></div>';
                }
            );
    }


    /* show version control tab according to option */
    if (get_option('oes_admin-hide_version_tab'))
        add_action(
        /**
         * Notice for "version" post types with block editor. For post types that are controlled by a parent
         * post type display a link to the parent post and current version after the title.
         */
            'admin_footer',
            function () {
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
        );
}


/**
 * Add post meta information when updating a version controlling or version controlled post.
 *
 * @param int $post_id The current post id.
 * @param WP_Post $post The current post.
 * @return void
 */
function save_post(int $post_id, WP_Post $post): void
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
        if ($post->post_status === 'publish') {
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
            if ($max && $max !== $currentVersionID) set_current_version_id($post_id, $max);
        }
    }
}


/**
 * Copy from current version.
 * @return void
 */
function admin_action_oes_copy_version(): void
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
                    wp_set_object_terms($newPostID, $postTerms, $taxonomy);
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
 * @return void
 */
function admin_action_oes_create_version(): void
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
 * @return void
 */
function admin_action_oes_create_translation(): void
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
            oes_set_post_language($newPostID, ($_GET['language'] ?? 'language0'));


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
