<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Admin_Menu')) :

    /**
     * Class Admin_Menu
     *
     * Implement the config tool for admin menu configurations.
     */
    class Admin_Menu extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can modify the WordPress admin menu structure to the left by organizing your custom ' .
                    'post types and custom taxonomies inside the menu.',
                    'oes') .
                '</p><p>' .
                sprintf(__('The post type parameter <strong>Show in menu</strong> adds the post type as top level ' .
                    'menu item to the menu. Find more information %shere%s.',
                    'oes'),
                '<a href="https://developer.wordpress.org/reference/functions/register_post_type/#show_in_menu" target="_blank">',
                '</a>'
                ) . '<br>' .
                sprintf(__('You can define the menu order position with the parameter <strong>Position</strong>. ' .
                    'Find more information %shere%s.',
                    'oes'),
                    '<a href="https://developer.wordpress.org/reference/functions/register_post_type/#menu_position" target="_blank">',
                    '</a>'
                ) .
                '</p><p>' .
                __('The OES feature <strong>Container</strong> allows you to organize admin menu items ' .
                    'into a new top menu with sub menu items and to display currently worked on post objects. ' .
                    'The container have to be initialized inside your OES project plugin to be available for ' .
                    'configuration, see user manual for more information.',
                    'oes'
                ) .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display(){
            $this->set_menu_structure_options();
            $this->set_container_options();
        }


        function set_menu_structure_options()
        {

            $oes = OES();

            /* header */
            $this->table_data[] = [
                'type' => 'thead',
                'rows' => [
                    [
                        'class' => 'oes-config-table-separator',
                        'cells' => [
                            [
                                'type' => 'th',
                                'colspan' => 3,
                                'value' => '<strong>' . __('Menu Structure', 'oes') . '</strong>'
                            ]
                        ]
                    ]
                ]
            ];


            $rows = [
                [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Post Type', 'oes') . '</strong>'
                        ],
                        [
                            'type' => 'th',
                            'class' => 'oes-table-transposed',
                            'value' => '<strong>' . __('Show in Menu', 'oes') .
                                '</strong><div>' . __('Show in the editorial layer menu.', 'oes') . '</div>'
                        ],
                        [
                            'type' => 'th',
                            'class' => 'oes-table-transposed',
                            'value' => '<strong>' . __('Menu Position', 'oes') .
                                '</strong><div>' . __('Change the position in the editorial layer menu. Default on empty or 0 is ' .
                                    'between 26 and 59.', 'oes') . '</div>'
                        ]
                    ]
                ]
            ];
            foreach ($oes->post_types as $postTypeKey => $postType) {

                /* get post type object */
                $postTypeObject = get_post_type_object($postTypeKey);

                $partOfContainer = [];
                if(!empty($oes->admin_pages['container']))
                foreach ($oes->admin_pages['container'] as $container)
                if(in_array($postTypeKey, $container['sub_pages']))
                    $partOfContainer[] = $container['page_args']['menu_title'];


                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . ($postType['label'] ?? $postTypeKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('checkbox',
                                'post_types[' . $postTypeKey . '][register_args][show_in_menu]',
                                'post_types-' . $postTypeKey . '-register_args-show_in_menu',
                                $postTypeObject->show_in_menu ?? false,
                                ['hidden' => true]) .
                                (empty($partOfContainer) ?
                                    '' :
                                    '<code class="oes-object-identifier">' . __('Part of Container:', 'oes') . implode(',', $partOfContainer) . '</code>')
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('number',
                                'post_types[' . $postTypeKey . '][register_args][menu_position]',
                                'post_types-' . $postTypeKey . '-register_args-menu_position',
                                $postTypeObject->menu_position ?? 0,
                                ['min' => 0, 'max' => 70])
                        ]
                    ]
                ];
            }

            $this->table_data[] = [
                'class' => 'oes-toggle-checkbox',
                'rows' => $rows
            ];
        }

        function set_container_options(){

            $oes = OES();
            if (!empty($oes->admin_pages['container'])) {

                /* header */
                $this->table_data[] = [
                    'standalone' => true,
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Container', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];


                foreach ($oes->admin_pages['container'] as $containerKey => $container) {

                    $containerName = $container['page_args']['menu_title'] ?? 'Recently worked on';

                    /* prepare elements */
                    $objectOptions = ['none' => '-'];
                    foreach ($oes->post_types as $key => $postType) $objectOptions[$key] = $postType['label'];
                    foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                    $this->table_data[] = [
                        'standalone' => (array_key_first($oes->admin_pages['container']) === $containerKey),
                        'rows' => [
                            [
                                'type' => 'trigger',
                                'cells' => [
                                    [
                                        'value' => '<strong>' . $containerName . '</strong>'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'target',
                                'nested_tables' => [
                                    [
                                        'rows' => [
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Name', 'oes') .
                                                            '</strong><div>' . __('Name of the container as displayed in menu', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('text',
                                                            'oes_config[container][' . $containerKey . '][page_args][menu_title]',
                                                            'oes_config-container-' . $containerKey . '-page_args-menu_title',
                                                            $containerName)
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Position', 'oes') .
                                                            '</strong><div>' . __('Change the position in the editorial layer menu. Default on empty or 0 is ' .
                                                                'between 26 and 59.', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('number',
                                                            'oes_config[container][' . $containerKey . '][page_args][position]',
                                                            'oes_config-container-' . $containerKey . '-page_args-position',
                                                            $container['page_args']['position'] ?? 20,
                                                            ['min' => 1, 'max' => 101])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Icon', 'oes') .
                                                            '</strong><div>' .
                                                            __('The menu icon. Valid options are: "default", "parent", "second", "admin" or a path.', 'oes') .
                                                            '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('text',
                                                            'oes_config[container][' . $containerKey . '][page_args][icon_url]',
                                                            'oes_config-container-' . $containerKey . '-page_args-icon_url',
                                                            $container['page_args']['icon_url'] ?? 'default')
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Elements', 'oes') .
                                                            '</strong><div>' . __('Elements included as submenu in this container.', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'oes_config[container][' . $containerKey . '][sub_pages]',
                                                            'oes_config-container-' . $containerKey . '-sub_pages',
                                                            $container['sub_pages'] ?? [],
                                                            ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Info Page Elements', 'oes') .
                                                            '</strong><div>' . __('Elements displayed on an info page. Only elements that are set in the ' .
                                                                '"Elements" option can be displayed.', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'oes_config[container][' . $containerKey . '][info_page][elements]',
                                                            'oes_config-container-' . $containerKey . '-info_page-elements',
                                                            $container['info_page']['elements'] ?? [],
                                                            ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Info Page Text', 'oes') .
                                                            '</strong><div>' . __('The menu name of the info page (if info page elements are set)', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('text',
                                                            'oes_config[container][' . $containerKey . '][info_page][label]',
                                                            'oes_config-container-' . $containerKey . '-info_page-label',
                                                            $container['info_page']['label'] ?? 'Title missing')
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Menu', 'admin-menu');

endif;