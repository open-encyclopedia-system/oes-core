<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Logos')) :

    /**
     * Class Theme_Logos
     *
     * Implement the config tool for theme configurations: logos.
     */
    class Theme_Logos extends Config
    {

        /** @inheritdoc */
        function set_table_data_for_display()
        {

            $logos = [
                'oes_theme-header_logo' => __('Header Logo', 'oes'),
                'oes_theme-header_logo_print' => __('Header Logo (Print)', 'oes'),
                'oes_theme-favicon' => __('Favicon', 'oes'),
                'oes_theme-favicon_16_16' => __('Favicon (16x16)', 'oes'),
                'oes_theme-favicon_32_32' => __('Favicon (32x32)', 'oes'),
                'oes_theme-favicon_apple' => __('Favicon (apple touch)', 'oes')
            ];

            foreach ($logos as $optionKey => $title) {
                $this->add_table_row(
                    [
                        'title' => $title,
                        'key' => $optionKey,
                        'value' => get_option($optionKey) ?? ''
                    ]
                );
            }
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            foreach ([
                         'oes_theme-header_logo',
                         'oes_theme-header_logo_print',
                         'oes_theme-favicon',
                         'oes_theme-favicon_16_16',
                         'oes_theme-favicon_32_32',
                         'oes_theme-favicon_apple'
                     ] as $option) {
                if (!oes_option_exists($option)) {
                    add_option($option, $_POST[$option]);
                } else {
                    update_option($option, $_POST[$option]);
                }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Logos', 'theme-logos');
endif;