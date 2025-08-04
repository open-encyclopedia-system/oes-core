<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Date')) :

    /**
     * Class Theme_Date
     *
     * Implement the config tool for theme configurations: date.
     */
    class Theme_Date extends Config
    {

        /** @var string The option name. */
        public string $option = 'oes_admin-date_format';

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            $this->add_table_row(
                [
                    'title' => __('Date Format', 'oes'),
                    'key' => $this->option,
                    'value' => get_option($this->option) ?? 1,
                    'type' => 'select',
                    'args' => [
                        'options' => [
                            1 => 'Long (January 12, 1952)',
                            0 => 'Full (Tuesday, April 12, 1952 AD)',
                            2 => 'Medium (Jan 12, 1952)',
                            3 => 'Short (12/13/52)'
                        ]
                    ]
                ]
            );
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            if (!oes_option_exists($this->option)) {
                add_option($this->option, $_POST[$this->option]);
            }
            else {
                update_option($this->option, $_POST[$this->option]);
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Date', 'theme-date');
endif;