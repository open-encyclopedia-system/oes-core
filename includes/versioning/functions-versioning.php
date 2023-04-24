<?php

namespace OES\Versioning;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post;
use function OES\ACF\oes_get_field;


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
 * @return void
 */
function set_current_version_id($parentID, $currentPostID): void
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
 * @return void
 */
function set_parent_id($postID, $parentID): void
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
 * @return void
 */
function set_all_version_ids($parentID, array $versionIDs): void
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
 * @return void
 */
function set_translation_id($postID, $translationPostID): void
{
    update_field('field_connected_parent', $postID, $translationPostID);
}


/**
 * Set translation parent for post.
 *
 * @param string|int $postID The post ID.
 * @param string|int $translationPostID The post ID of the translation parent.
 * @return void
 */
function set_post_language($postID, $translationPostID): void
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
 * @return void
 */
function display_version_table(array $postIDs, $currentVersionID): void
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
            <td><?php echo oes_get_html_anchor($versionPost->post_title, get_edit_post_link($versionPostID)); ?></td>
            <td><?php echo get_version_field($versionPost->ID); ?></td>
            <td><?php echo $versionPost->post_status; ?></td>
            <td><?php echo get_the_author_meta('display_name', $versionPost->post_author); ?></td>
            </tr><?php
        endif;
    endforeach; ?>
    </tbody>
    </table><?php
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


/**
 * Callback function for the meta box of "parent" post types.
 *
 * @param WP_Post $post The current post.
 * @param array $callbackArgs Custom arguments passed by add_meta_box.
 * @return void
 */
function meta_box_parent_post(WP_Post $post, array $callbackArgs): void
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
    $translationLanguageLabel = '';
    $translationLanguage = 'language0';
    if ($thisLanguage)
        foreach (OES()->languages as $key => $language)
            if ($key !== $thisLanguage) {
                $translationLanguageLabel = $language['label'];
                $translationLanguage = $key;
            }

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
            if (empty($translationParent) && $translationLanguageLabel) :?>
                <a href="admin.php?action=oes_create_translation&post=<?php echo $post->ID;
                ?>&language=<?php echo $translationLanguage;?>&nonce=<?php echo wp_create_nonce('oes-create-translation-' . $post->ID); ?>"
                   class="button button-large"
                   title="Create Language Version" rel="permalink"><?php
                printf(__('Create Language Version [%s]', 'oes'), $translationLanguageLabel); ?></a><?php
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
                printf(__('Language Versions [%s]', 'oes'), $translationLanguageLabel); ?></div>
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