<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Cache_Empty')) :

    /**
     * Class Cache_Empty
     *
     * Empty archive cache.
     */
    class Cache_Empty extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = [])
        {
            $this->form_action = admin_url('admin-post.php');
        }


        //Implement parent
        function html()
        {
            ?>
            <div id="tools">
                <div class="oes-tool-wrapper-cache-empty">
                    <h3><?php _e('Empty Cache ', 'oes');?></h3>
                    <p><?php _e('By emptying the cache the archive pages will compute the current data ' .
                            'every time the page is called until a new cache is stored.',
                            'oes'); ?></p>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Empty Cache', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //Overwrite
        function admin_post_tool_action()
        {
            oes_empty_archive_cache();
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Cache_Empty', 'cache-empty');

endif;


/**
 * Delete all archive cache options.
 *
 * @param string $option The option name part after 'oes_cache_' (a post type, a taxonomy or 'index').
 */
function oes_empty_archive_cache(string $option = ''){

    /* delete all options if no option specified */
    if(empty($option)){

        global $oes;
        foreach ($oes->post_types as $singlePostTypeKey => $ignore)
            delete_option('oes_cache_' . $singlePostTypeKey);

        foreach ($oes->taxonomies as $singleTaxonomyKey => $ignore)
            delete_option('oes_cache_' . $singleTaxonomyKey);

        /* get current cache */
        delete_option('oes_cache_index');
    }
    else {
        delete_option('oes_cache_' . $option);
    }
}