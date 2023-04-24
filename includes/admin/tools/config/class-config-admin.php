<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

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
                                    'value' => '<strong>' . __('Show OES Objects') . '</strong>' .
                                        '<div>' . __('Show the post type "OES Objects". This post type stores the post type ' .
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
                                    'value' => '<strong>' . __('Suspend OES Data Model Registration') . '</strong>' .
                                        '<div>' . __('Techy option, will temporarily skip the OES data model (data will not be touched, I ' .
                                            'promise). Suspend data model registration to operate on post type "OES Objects" or execute ' .
                                            'delete options.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_admin-suspend_data_model',
                                        'oes_admin-suspend_data_model',
                                        get_option('oes_admin-suspend_data_model'))
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Hide Versioning Tab') . '</strong>' .
                                        '<div>' . __('Hide the post fields that hold version information.', 'oes') .
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
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Use WordPress Date Format') . '</strong>' .
                                        '<div>' . __('Use the WordPress date format for all ACF date picker fields.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_admin-use_date_format',
                                        'oes_admin-use_date_format',
                                        get_option('oes_admin-use_date_format'))
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
                'oes_admin-suspend_data_model',
                'oes_admin-show_oes_objects',
                'oes_admin-hide_version_tab',
                'oes_admin-use_date_format'
            ];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, isset($_POST[$option]));
                else update_option($option, isset($_POST[$option]));
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin', 'admin');

endif;