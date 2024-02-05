<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin_Container')) :

    /**
     * Class Admin_Container
     *
     * Implement the config tool for admin menu configurations.
     */
    class Admin_Container extends Config
    {

        //Set parameters.
        public bool $empty_allowed = true;
        public bool $empty_input = true;


        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <strong>Container</strong> allows you to organize admin menu items ' .
                    'into a new top menu with sub menu items and to display currently worked on post objects. ' .
                    'The container have to be initialized inside your OES project plugin to be available for ' .
                    'configuration, see user manual for more information.',
                    'oes'
                ) .
                '</p></div>';
        }


        //Overwrite parent
        function additional_html(): string
        {
            return '<div class="oes-button-wrapper">' .
                '<input type="submit" name="add_new_container" id="add_new_container" ' .
                'class="button button-secondary button-large" ' .
                'value="' . __('Add New Container', 'oes') . '">' .
                '</div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();

            /* add container page for versioning posts */
            $containerAll = $oes->admin_pages['container'] ?? [];
            foreach ($oes->post_types as $postTypeKey => $postType)
                if (!empty($postType['parent'] ?? '') && !isset($containerAll[$postTypeKey])) {
                    $containerAll[$postTypeKey] = \OES\Admin\prepare_container_page_args($postTypeKey);
                }

            /* loop through container */
            foreach ($containerAll as $containerKey => $container) {

                $containerName = $container['page_parameters']['menu_title'] ?? 'Recently worked on';

                /* prepare elements */
                $objectOptions = ['none' => '-'];
                foreach ($oes->post_types as $key => $postType) $objectOptions[$key] = $postType['label'];
                foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                $rows = [
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
                                    'oes_config[container][' . $containerKey . '][page_parameters][menu_title]',
                                    'oes_config-container-' . $containerKey . '-page_parameters-menu_title',
                                    $containerName)
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Position', 'oes') .
                                    '</strong><div>' . __('Change the position in the editorial layer menu. Default ' .
                                        'on empty or 0 is between 26 and 59.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('number',
                                    'oes_config[container][' . $containerKey . '][page_parameters][position]',
                                    'oes_config-container-' . $containerKey . '-page_parameters-position',
                                    $container['page_parameters']['position'] ?? 20,
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
                                    __('The menu icon. Valid options are: "default", "parent", "second", "admin" or ' .
                                        'a path.', 'oes') .
                                    '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                    'oes_config[container][' . $containerKey . '][page_parameters][icon_url]',
                                    'oes_config-container-' . $containerKey . '-page_parameters-icon_url',
                                    $container['page_parameters']['icon_url'] ?? 'default')
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Elements', 'oes') . '</strong>' .
                                    '<div>' . __('Elements included as submenu in this container.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'oes_config[container][' . $containerKey . '][subpages]',
                                    'oes_config-container-' . $containerKey . '-subpages',
                                    $container['subpages'] ?? [],
                                    [
                                        'options' => $objectOptions,
                                        'multiple' => true,
                                        'class' => 'oes-replace-select2',
                                        'reorder' => true,
                                        'hidden' => true
                                    ])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Info Page Elements', 'oes') . '</strong>' .
                                    '<div>' . __('Elements displayed on an info page. Only elements that are set ' .
                                        'in the "Elements" option can be displayed.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'oes_config[container][' . $containerKey . '][info_page][elements]',
                                    'oes_config-container-' . $containerKey . '-info_page-elements',
                                    $container['info_page']['elements'] ?? [],
                                    [
                                        'options' => $objectOptions,
                                        'multiple' => true,
                                        'class' => 'oes-replace-select2',
                                        'reorder' => true,
                                        'hidden' => true
                                    ])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Info Page Text', 'oes') . '</strong>' .
                                    '<div>' .
                                    __('The menu name of the info page (if info page elements are set)', 'oes') .
                                    '</div>'
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
                ];

                if ($container['generated'] ?? false)
                    $rows[] = [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Hide', 'oes') .
                                    '</strong><div>' . __('Hide generated container in menu.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('checkbox',
                                        'oes_config[container][' . $containerKey . '][hide]',
                                        'oes_config-container-' . $containerKey . '-hide',
                                        $container['hide'] ?? false,
                                        ['hidden' => true]
                                    ) .
                                    '<input type="hidden" ' .
                                    'name="oes_config[container][' . $containerKey . '][generated]" value="true">'
                            ]
                        ]
                    ];
                else $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'colspan' => 2,
                            'class' => 'oes-table-transposed',
                            'value' => '<a href="javascript:void(0);" ' .
                                'onClick="oesConfigTableDeleteRow(this)" ' .
                                'class="oes-highlighted button">' .
                                __('Delete This Container', 'oes') .
                                '</a>'
                        ]
                    ]
                ];

                $this->table_data[] = [
                    'standalone' => (array_key_first($oes->admin_pages['container'] ?? []) === $containerKey),
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
                                    'rows' => $rows
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }


        //Overwrite parent.
        function get_modified_post_data(array $data): array
        {
            if (isset($data['add_new_container']))
                $data['oes_config']['container'][] = [
                    'page_parameters' => [
                        'menu_title' => 'New Container Title',
                        'position' => 20,
                        'icon_url' => 'default'
                    ],
                    'subpages' => [],
                    'info_page' => [
                        'elements' => [],
                        'label' => 'Recently worked on'
                    ]
                ];
            elseif ($data['oes_hidden'] && (empty($data['oes_config']['container'])))
                $data['oes_config']['container'] = [];


            /* replace hidden "hide" param */
            foreach ($data['oes_config']['container'] as $key => $container)
                if (isset($container['hide']))
                    $data['oes_config']['container'][$key]['hide'] = (
                    $container['hide'] == 'hidden' ?
                        false :
                        $container['hide']);

            return $data;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Container', 'admin-container');

endif;