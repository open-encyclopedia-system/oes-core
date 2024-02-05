<?php

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

        //Overwrite parent
        function set_table_data_for_display()
        {
            /* get global OES instance */
            $this->table_data[] = [
                    'rows' => [
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Show OES Objects', 'oes') . '</strong>' .
                                        '<div>' .
                                        __('Show the post type "OES Objects". This post type stores the post type ' .
                                            'configurations.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_admin-show_oes_objects',
                                        'oes_admin-show_oes_objects',
                                        get_option('oes_admin-show_oes_objects'))
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Hide Versioning Tab', 'oes') . '</strong>' .
                                        '<div>' .
                                        __('Hide the post fields that hold version information.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_admin-hide_version_tab',
                                        'oes_admin-hide_version_tab',
                                        get_option('oes_admin-hide_version_tab'))
                                ]
                            ]
                        ]
                    ]
                ];
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            /* add or delete option */
            $options = [
                'oes_admin-show_oes_objects',
                'oes_admin-hide_version_tab'
            ];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, isset($_POST[$option]));
                else update_option($option, isset($_POST[$option]));
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin', 'admin');

endif;