<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Prepare data before display according to data type.
 *
 * @return void
 */
function oes_prepare_data(): void
{
    oes_prepare_language();
    if (is_front_page() || is_page()) oes_set_page_data();
    elseif (is_single()) oes_prepare_single();
    elseif (is_tax()) oes_prepare_tax();
    elseif (is_archive()) oes_set_archive_data();
    elseif (is_search()) oes_prepare_search();
    else oes_prepare_data_other();
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
    if (!$postID) $postID = get_the_ID();
    $projectClass = str_replace(['oes-', '-'], ['', '_'], OES_BASENAME_PROJECT) . '_Page';
    $oes_post = class_exists($projectClass) ?
        new $projectClass($postID) :
        new OES_Page($postID);
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
        new OES_Taxonomy($termID);
}


/**
 * Set archive parameters and data for post types and taxonomies.
 *
 * @param string $class The archive class.
 * @param array $args Additional parameters.
 *
 * @return void
 */
function oes_set_archive_data(string $class = '', array $args = []): void
{

    if (empty($class)) {
        global $post_type;
        $class = $post_type . '_Post_Archive';
        if (!class_exists($class)) $class = 'OES_Post_Archive';
    }

    /* execute the loop */
    $oesArchive = class_exists($class) ?
        new $class($args) :
        new OES_Archive($args);

    /* store archive in global variable */
    global $oes_archive_data;
    $oes_archive_data = [
        'archive' => (array)$oesArchive,
        'table-array' => $oesArchive->get_data_as_table()
    ];

    /* prepare archive count */
    global $oes_filter, $oes_archive_count;
    $oes_filter = $oesArchive->filter_array;

    $oes_archive_count = (($oesArchive->characters && sizeof($oesArchive->characters) > 0 && $oesArchive->count) ?
        $oesArchive->count :
        false);
}


/**
 * Prepare the page language by deriving the cookie value and evaluating the global language switched variable.
 *
 * @return void
 */
function oes_prepare_language(): void
{
    global $oes, $oes_language, $oes_language_switched;
    if(sizeof($oes->languages) < 2) $oes_language = 'language0';
    else {
        if ($oes_language_switched) $oes_language = $oes_language_switched;
        if (empty($oes_language)) $oes_language = $_COOKIE['oes_language'] ?? 'language0';
    }
}


/**
 * Prepare page data for single post.
 * Check if archive is "flat" (redirect to archive), else set single post data.
 *
 * @return void
 */
function oes_prepare_single(): void
{
    global $oes, $post;
    if ($oes->post_types[$post->post_type]['archive_on_single_page'] ?? false)
        oes_redirect(get_post_type_archive_link($post->post_type) . '#' . $post->post_type . '-' . $post->ID);
    else oes_set_post_data();
}


/**
 * Prepare page data for tax (term) page.
 * Check if redirect to archive (use term as filter), else prepare term data.
 *
 * @return void
 */
function oes_prepare_tax(): void
{
    global $taxonomy, $term, $oes;
    if ($taxonomy &&
        isset($oes->taxonomies[$taxonomy]['redirect']) &&
        (($oes->taxonomies[$taxonomy]['redirect'] ?? false) !== 'none') &&
        !isset($_GET['oesf_' . $taxonomy]) &&
        $termObject = get_term_by('slug', $term, $taxonomy)) {
        oes_redirect(get_post_type_archive_link(
                $oes->taxonomies[$taxonomy]['redirect']) . '?oesf_' . $taxonomy . '=' . $termObject->term_id);
    } else oes_set_term_data();
}


/**
 * Prepare page data for search page.
 *
 * @return void
 */
function oes_prepare_search(): void
{

    global $oes_search;
    global $oes_archive_count;
    $oes_search = new OES_Search(['language' => 'all']);
    $oes_archive_count = $oes_search->count;

    global $oes_is_search;
    if (!empty($oes_search->search_term)) {
        global $oes, $oes_archive_data, $oes_archive_count, $oes_filter;
        if($oes->block_theme) {
            $oes_search->get_results();
            $oes_archive_count = $oes_search->count;
            $oes_filter = $oes_search->filter_array;
            $oes_archive_data = [
                'archive' => (array)$oes_search,
                'table-array' => $oes_search->get_data_as_table()
            ];
        }
    }
    $oes_is_search = true;
}


/**
 * Prepare page data for index page.
 *
 * @param string $indexPageKey The index page key.
 * @return void
 */
function oes_prepare_index(string $indexPageKey): void
{
    global $oes, $oes_is_index, $oes_is_index_page;
    if ($oes->theme_index_pages[$indexPageKey]['slug'] !== 'hidden') {
        $oes_is_index = $indexPageKey;
        $oes_is_index_page = true;

        $archiveClass = $indexPageKey . '_Index_Archive';
        if (!class_exists($archiveClass)) $archiveClass = 'OES_Index_Archive';
        oes_set_archive_data($archiveClass);

        /* check if additional redirect action */
        do_action('oes/redirect_template', 'prepare_index');
    }
}


/**
 * Prepare page data for taxonomy archive.
 *
 * @return void
 */
function oes_prepare_taxonomies(): void
{
    global $oes;
    foreach ($oes->taxonomies as $taxonomyKey => $singleTaxonomy) {

        $taxonomyObject = get_taxonomy($taxonomyKey);

        /* Archive pages */
        if (($taxonomyObject->rewrite['slug'] ?? false) &&
            oes_get_current_url(false) ==
            (get_site_url() . '/' . ($taxonomyObject->rewrite['slug'] ?? $taxonomyKey) . '/') &&
            !is_page(($taxonomyObject->rewrite['slug'] ?? $taxonomyKey))) {

            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                    if (in_array($taxonomyKey, $indexPage['objects'] ?? [])) {
                        global $oes_is_index;
                        $oes_is_index = $indexPageKey;
                    }

            $archiveClass = $taxonomyKey . '_Taxonomy_Archive';
            if (!class_exists($archiveClass)) $archiveClass = 'OES_Taxonomy_Archive';
            oes_set_archive_data($archiveClass, ['taxonomy' => $taxonomyKey]);

            /* check if additional redirect action */
            do_action('oes/redirect_template', 'prepare_taxonomies');
        }
    }
}


/**
 * Prepare other page data.
 *
 * @return void
 */
function oes_prepare_data_other(): void
{
    /* check if index page */
    global $oes;
    if (!empty($oes->theme_index_pages))
        foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
            if (oes_get_current_url(false) === get_site_url() . '/' . ($indexPage['slug'] ?? 'index') . '/')
                oes_prepare_index($indexPageKey);

    /* check if page is taxonomy archive */
    oes_prepare_taxonomies();
}