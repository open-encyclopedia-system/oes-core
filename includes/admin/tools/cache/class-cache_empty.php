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
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }


        //Implement parent
        function html(): void
        {
            ?>
            <div id="tools">
                <div class="oes-tool-wrapper-cache-empty">
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
        function admin_post_tool_action(): void
        {
            oes_empty_archive_cache();
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Cache_Empty', 'cache-empty');

endif;