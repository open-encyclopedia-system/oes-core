<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('oes_update_archive_cache', 'oes_update_archive_cache');


/**
 * Update the archive cache option.
 *
 * @param string $object The object. Valid options are 'all', 'index' post type or taxonomy.
 * @return void
 */
function oes_update_archive_cache(string $object = 'all'): void
{
    global $oes;
    if($object === 'all'){

        /* update post types and taxonomies */
        foreach ($oes->post_types as $singlePostType => $ignore) oes_set_cache($singlePostType);
        foreach ($oes->taxonomies as $singleTaxonomy => $ignore) oes_set_cache($singleTaxonomy);
    }
    else{

        $cacheSet = false;
        if($oes->theme_index_pages)
            foreach($oes->theme_index_pages as $index => $indexPage)
                if($object === $index) {
                    oes_set_cache($index, $indexPage['objects'] ?? []);
                    $cacheSet = true;
                    break;
                }

        if(!$cacheSet) oes_set_cache($object);
    }
}