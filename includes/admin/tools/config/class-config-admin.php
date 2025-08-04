<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin')) :

    /**
     * Class Admin
     *
     * Implement the config tool for admin configurations.
     */
    class Admin extends Config
    {

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            $this->add_table_row(
                [
                    'title' => __('Show OES Objects', 'oes'),
                    'key' => 'oes_admin-show_oes_objects',
                    'value' => get_option('oes_admin-show_oes_objects') ?? 0,
                    'type' => 'checkbox'
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Hide Versioning Tab', 'oes'),
                    'key' => 'oes_admin-hide_version_tab',
                    'value' => get_option('oes_admin-hide_version_tab') ?? 0,
                    'type' => 'checkbox'
                ]
            );
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            $options = [
                'oes_admin-show_oes_objects',
                'oes_admin-hide_version_tab'
            ];
            foreach ($options as $option) {
                if (!oes_option_exists($option)) {
                    add_option($option, isset($_POST[$option]));
                } else {
                    update_option($option, isset($_POST[$option]));
                }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin', 'admin');

endif;