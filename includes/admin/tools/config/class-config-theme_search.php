<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Search')) :

    /**
     * Class Theme_Search
     *
     * Implement the config tool for theme configurations: search.
     */
    class Theme_Search extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Search</b> allows you to define which parts of the encyclopdia can be ' .
                    'searched and how they are to be displayed.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();

            /* general options */
            $innerRows[] = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Maximum paragraphs in search result', 'oes') . '</strong>' .
                            '<div>' .
                            __('The maximum of paragraphs when displaying the preview in search results',
                                'oes') .
                            '</div>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('number',
                            'oes_config[search][max_preview_paragraphs]',
                            'oes_config-search-max_preview_paragraphs',
                            $oes->search['max_preview_paragraphs'] ?? 1,
                            ['min' => 0, 'max' => 100])
                    ]
                ]
            ];

            /* Prepare order */
            $orderSelects = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData)
                $orderSelects[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData)
                $orderSelects[$taxonomyKey] = $taxonomyData['label'] ?? $taxonomyKey;
            $innerRows[] = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Order of searched objects', 'oes') . '</strong>' .
                            '<div>' .
                            __('The order of the post types and taxonomies in which the results are output',
                                'oes') .
                            '</div>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('select',
                            'oes_config[search][order]',
                            'oes_config-search-order',
                            $oes->search['order'] ?? [],
                            [
                                'options' => $orderSelects,
                                'multiple' => true,
                                'class' => 'oes-replace-select2',
                                'reorder' => true,
                                'hidden' => true
                            ])
                    ]
                ]
            ];


            foreach($oes->languages as $languageKey => $languageData){
                $innerRows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Type Filter Label', 'oes') .
                                ' (' . ($languageData['label'] ?? $languageKey) . ')'.
                                '</strong><div>' . __('Label of the type filter', 'oes') . '</div>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                'oes_config[search][type_label][' . $languageKey . ']',
                                'oes_config-search-type_label-' . $languageKey,
                                $oes->search['type_label'][$languageKey] ?? '')
                        ]
                    ]
                ];
            }


            $this->table_data = [
                [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'colspan' => '2',
                                    'value' => '<strong>' . __('General', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'rows' => $innerRows
                ],
                [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'colspan' => '2',
                                    'value' => '<strong>' . __('Search In', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ]
            ];


            /* add header and options for page */
            $rows = [
                [
                    'cells' => [
                        [
                            'type' => 'th',
                            'colspan' => '2',
                            'value' => '<strong>' . __('Post Types', 'oes') . '</strong>'
                        ]
                    ]
                ],
                [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Page', 'oes') . '</strong>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'oes_config[search][postmeta_fields][page]',
                                'oes_config-search-postmeta_fields-page',
                                $oes->search['postmeta_fields']['page'] ?? [],
                                [
                                    'options' => ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'],
                                    'multiple' => true,
                                    'class' => 'oes-replace-select2',
                                    'reorder' => true,
                                    'hidden' => true
                                ])
                        ]
                    ]
                ]];


            /* add options for post types */
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* prepare options */
                $options = ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message']))
                            $options[$fieldKey] = $field['label'];

                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'oes_config[search][postmeta_fields][' . $postTypeKey . ']',
                                'oes_config-search-postmeta_fields-' . $postTypeKey,
                                $oes->search['postmeta_fields'][$postTypeKey] ?? [],
                                [
                                    'options' => $options,
                                    'multiple' => true,
                                    'class' => 'oes-replace-select2',
                                    'reorder' => true,
                                    'hidden' => true
                                ])
                        ]
                    ]
                ];
            }

            /* add options for taxonomies */
            if (!empty($oes->taxonomies))
                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'colspan' => '2',
                            'value' => '<strong>' . __('Taxonomies', 'oes') . '</strong>'
                        ]
                    ]
                ];
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData) {

                /* prepare options */
                $options = ['name' => 'Title (Name)', 'slug' => 'Slug'];

                //@oesDevelopment Add one field option for alternative names.

                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . ($taxonomyData['label'] ?? $taxonomyKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $taxonomyKey . '</code>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'oes_config[search][taxonomies][' . $taxonomyKey . ']',
                                'oes_config-search-taxonomies-' . $taxonomyKey,
                                $oes->search['taxonomies'][$taxonomyKey] ?? [],
                                [
                                    'options' => $options,
                                    'multiple' => true,
                                    'class' => 'oes-replace-select2',
                                    'reorder' => true,
                                    'hidden' => true
                                ])
                        ]
                    ]
                ];
            }

            $this->table_data[] = [
                'rows' => $rows
            ];

        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Search', 'theme-search');
endif;