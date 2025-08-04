<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Prepare data before display according to data type.
 *
 * @return void
 */
function oes_prepare_data(): void
{
    $class = oes_get_project_class_name('OES_Template_Redirect');
    $templateRedirect = new $class();
    $templateRedirect->prepare_data();
}

/**
 * Set post data for OES_Post object. (Prepare rendered content to derive table of content etc).
 *
 * @param int $postID The post id. Default is current post ID.
 * @return void
 */
function oes_set_post_data(int $postID = 0): void
{
    global $oes_post, $post_type;
    if (!$postID) $postID = get_the_ID();
    $oes_post = class_exists($post_type) ?
        new $post_type($postID) :
        new OES_Post($postID);
}

/**
 * Set post data for OES_Post object "Page". (Prepare rendered content to derive table of content etc.)
 *
 * @param int $postID The post id. Default is current post ID.
 * @return void
 */
function oes_set_page_data(int $postID = 0): void
{
    global $oes_post;
    if (!$postID) {
        $postID = get_the_ID();
    }
    $class = oes_get_project_class_name('OES_Page');
    $oes_post = new $class($postID);
}

/**
 * Set data for OES_Post object "Attachment".
 *
 * @param int $postID The post id. Default is current post ID.
 * @return void
 */
function oes_set_attachment_data(int $postID = 0): void
{
    global $oes_post;
    if (!$postID) {
        $postID = get_the_ID();
    }
    $class = oes_get_project_class_name('OES_Attachment');
    $oes_post = new $class($postID);
}

/**
 * Set term data for OES Taxonomy object.
 *
 * @param int $termID The term id.
 * @return void
 */
function oes_set_term_data(int $termID = 0): void
{
    global $taxonomy, $term, $oes_term;
    if (!$termID || !get_term($termID)) $termID = get_term_by('slug', $term, $taxonomy)->term_id ?? false;
    $oes_term = class_exists($taxonomy) ?
        new $taxonomy($termID) :
        new OES_Term($termID);
}

/**
 * Set archive parameters and data for post types and taxonomies.
 *
 * @param string $class The archive class.
 * @param array $args Additional parameters.
 * @param bool $setGlobals Set global parameters.
 *
 * @return void
 */
function oes_set_archive_data(string $class = '', array $args = [], bool $setGlobals = true): void
{
    global $post_type, $oes_is_index;

    $postType = $args['post-type'] ?? $post_type;

    if (empty($class)) {
        $class = $postType . '_Post_Archive';
        if (!class_exists($class)) {
            $class = 'OES_Post_Archive';
        }
    }

    $cachingEnabled = function_exists('\OES\Caching\get_cache') && !($args['ignore_cache'] ?? false);
    if($cachingEnabled) {

        global $oes_language;

        // Build cache key
        $language = empty($oes_language) ? ($args['language'] ?? 'language0') : $oes_language;
        $cacheKeyType = $oes_is_index ?? ($args['taxonomy'] ?? $postType);
        $cacheKey = 'oes_cache-' . $cacheKeyType . '-' . sanitize_key($class) . '-' . sanitize_key($language);

        $cached = \OES\Caching\get_cache($cacheKey);
        if ($cached) {
            global $oes_archive, $oes_archive_data, $oes_filter, $oes_archive_count;
            $oes_archive = $cached['archive'];
            $oes_archive_data = $cached['archive_data'];
            $oes_filter = $cached['filter'];
            $oes_archive_count = $cached['count'];
            return;
        }
    }

    // Not in cache, generate archive and set global parameters
    $oesArchive = class_exists($class) ? new $class($args) : new OES_Archive($args);

    if($setGlobals) {
        global $oes_archive, $oes_archive_data, $oes_filter, $oes_archive_count;
    }

    $oes_archive = [
        'characters' => $oesArchive->characters ?? [],
        'post_type' => $oesArchive->post_type ?? '',
        'taxonomy' => $oesArchive->taxonomy ?? '',
        'term' => $oesArchive->term ?? '',
        'filtered_language' => $oesArchive->filtered_language ?? '',
        'label' => $oesArchive->label ?? '',
        'page_title' => $oesArchive->page_title ?? '',
        'title_is_link' => $oesArchive->title_is_link ?? false,
        'filter' => $oesArchive->filter ?? [],
        'hide_on_empty' => $oesArchive->hide_on_empty ?? true,
        'childless' => $oesArchive->childless ?? true,
        'only_first_level' => $oesArchive->only_first_level ?? false,
        'display_content' => $oesArchive->display_content ?? false
    ];

    $oes_filter = $oesArchive->filter_array;

    $oes_archive_count = (
        $oesArchive->characters &&
        sizeof($oesArchive->characters) > 0 &&
        $oesArchive->count
    ) ? $oesArchive->count : false;


    global $oes;
    if($oes->legacy) {
        $oes_archive_data = [
            'archive' => (array)$oesArchive,
            'table-array' => $oesArchive->get_data_as_table()
        ];
    }
    else {
        $oes_archive_data = $oesArchive->get_data_as_table();
    }

    // Store in cache
    if($cachingEnabled) {
        \OES\Caching\set_cache($cacheKey, [
            'archive' => $oes_archive,
            'archive_data' => $oes_archive_data,
            'filter' => $oes_filter,
            'count' => $oes_archive_count
        ]);
    }
}

/**
 * Get a localized label for a given object, such as a post type or taxonomy.
 *
 * @param string $object   The object name (post type or taxonomy).
 * @param string $language Optional. The language code for localization. Default is 'language0'.
 * @return string The localized label or the object name as a fallback.
 */
function oes_get_object_label(string $object, string $language = ''): string
{
    if(empty($language)){
        global $oes_language;
        $language = $oes_language ?? 'language0';
    }

    if (post_type_exists($object)) {
        return oes_get_post_type_label($object, $language);
    }

    if (taxonomy_exists($object)) {
        return oes_get_taxonomy_label($object, $language);
    }

    return $object;
}

/**
 * Get a localized label for a given post type.
 *
 * @param string $postType The post type key.
 * @param string $language Optional. The language key used to retrieve the label. Default is 'language0'.
 * @return string The localized label for the post type, or fallback label if not found.
 */
function oes_get_post_type_label(string $postType, string $language = ''): string
{
    global $oes;

    if(empty($language)){
        global $oes_language;
        $language = $oes_language ?? 'language0';
    }

    if (!empty($oes->post_types[$postType]['label_translations_plural'][$language])) {
        return $oes->post_types[$postType]['label_translations_plural'][$language];
    }

    if (!empty($oes->post_types[$postType]['theme_labels']['archive__header'][$language])) {
        return $oes->post_types[$postType]['theme_labels']['archive__header'][$language];
    }

    if (!empty($oes->post_types[$postType]['label'])) {
        return $oes->post_types[$postType]['label'];
    }

    // Fallback to WordPress post type object label
    $postTypeObject = get_post_type_object($postType);
    return $postTypeObject->label ?? $postType;
}

/**
 * Get a localized label for a given taxonomy.
 *
 * @param string $taxonomy The taxonomy key.
 * @param string $language Optional. The language key used to retrieve the label. Default is 'language0'.
 * @return string The localized label for the taxonomy, or fallback label if not found.
 */
function oes_get_taxonomy_label(string $taxonomy, string $language = ''): string
{
    global $oes;

    if(empty($language)){
        global $oes_language;
        $language = $oes_language ?? 'language0';
    }

    if (!empty($oes->taxonomies[$taxonomy]['label_translations_plural'][$language])) {
        return $oes->taxonomies[$taxonomy]['label_translations_plural'][$language];
    }

    if (!empty($oes->taxonomies[$taxonomy]['label_translations'][$language])) {
        return $oes->taxonomies[$taxonomy]['label_translations'][$language];
    }

    if (!empty($oes->taxonomies[$taxonomy]['label'])) {
        return $oes->taxonomies[$taxonomy]['label'];
    }

    // Fallback to WordPress taxonmy object label
    $taxonomyObject = get_taxonomy($taxonomy);
    return $taxonomyObject->label ?? $taxonomy;
}
