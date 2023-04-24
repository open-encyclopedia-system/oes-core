<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Labels')) :

    /**
     * Class Theme_Labels
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Labels extends Config
    {

        /** @var array the language row */
        private array $language_row = [];

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('If you are using an OES theme you can define labels for the templates that will be rendered ' .
                    'on certain part of the pages or for specific languages if you are using the OES feature ' .
                    '<b>Bilingualism</b>. Most of the labels are defined by your OES project plugin.', 'oes') .
                '</p></div>' .
                '<div class="oes-tool-information-wrapper"><p>' .
                sprintf(__('The following labels will affect the labels of custom post types, custom taxonomies and their ' .
                    'fields inside the frontend layer. Some labels will overwrite the labels defined for the editorial ' .
                    'layer (admin labels). ' .
                    'Find the admin labels %shere%s.',
                    'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_settings_writing&select=admin-labels') . '">',
                    '</a>'
                ) .
                '</p></div>' .
                '<div class="oes-config-warning oes-warning">' .
                sprintf(__('Warning: if you want to use double quotes use the unicode notation &#8220; (%s) or &#8222; (%s).', 'oes'),
                    htmlspecialchars('&#8220;'),
                    htmlspecialchars('&#8222;')) .
                '</div>' .
                get_expand_button();
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();
            $languages = array_keys($oes->languages);

            /* prepare table head */
            $cells = [[
                'type' => 'th',
                'value' => '<strong>' . __('Label', 'oes') . '</strong>'
            ]];
            foreach ($oes->languages as $language) {
                $cells[] = [
                    'type' => 'th',
                    'class' => 'oes-table-transposed',
                    'value' => '<strong>' . $language['label'] . '</strong>'
                ];
            }
            $this->language_row = [[
                'cells' => $cells
            ]];

            $this->table_data[] = [
                'type' => 'thead',
                'rows' => [
                    [
                        'class' => 'oes-config-table-hide',
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '',
                                'class' => 'oes-expandable-row-20'
                            ],
                            [
                                'type' => 'th',
                                'value' => ''
                            ]
                        ]
                    ]
                ]
            ];

            /* General -----------------------------------------------------------------------------------------------*/

            $nestedRows = [];
            if (!empty($oes->theme_labels)) {
                $storeForSorting = $oes->theme_labels;
                ksort($storeForSorting);
                foreach ($storeForSorting as $key => $label) {

                    $cells = [[
                        'type' => 'th',
                        'value' => '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                            __('Location: ', 'oes') . '</em>' . ($label['location'] ?? '') .
                            '</div>' .
                            '<div><code class="oes-object-identifier-br">' . $key . '</code></div>'
                    ]];


                    foreach ($languages as $language)
                        $cells[] = [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                'oes_config[theme_labels][' . $key . '][' . $language . ']',
                                'oes_config-theme_labels-' . $key . '-' . $language,
                                $label[$language] ?? '')
                        ];

                    $nestedRows[] = [
                        'cells' => $cells
                    ];
                }
            }

            $this->table_data[] = [
                'rows' => [
                    [
                        'type' => 'trigger',
                        'cells' => [
                            [
                                'value' => '<strong>' . __('General', 'oes') . '</strong>'
                            ]
                        ]
                    ],
                    [
                        'type' => 'target',
                        'nested_tables' => [
                            [
                                'rows' => array_merge($this->language_row, $nestedRows)
                            ]
                        ]
                    ]
                ]
            ];


            /* get theme labels for media ----------------------------------------------------------------------------*/

            /* get acf group image fields */
            $mediaRows = [];
            foreach ($oes->media_groups['image'] ?? [] as $fieldKey => $field) {

                $label = $field['label'] ?? ([
                            'title' => 'Title',
                            'alt' => 'Alternative Text',
                            'caption' => 'Caption',
                            'description' => 'Description',
                            'date' => 'Publication Date'
                        ][$fieldKey] ?? $fieldKey);
                $cells = [[
                    'type' => 'th',
                    'value' => '<strong>' . $label . '</strong>' .
                        '<code class="oes-object-identifier">' . $fieldKey . '</code>'
                ]];

                foreach ($languages as $language)
                    $cells[] = [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('text',
                            'oes_config[media][image][' . $fieldKey . '][' . $language . ']',
                            'oes_config-media-image-' . $fieldKey . '-' . $language,
                            $field[$language] ?? ''
                        )
                    ];

                $mediaRows[] = [
                    'cells' => $cells
                ];
            }

            /* add acf fields */
            foreach ($oes->media_groups['fields'] ?? [] as $fieldKey => $mediaField) {

                $cells = [[
                    'type' => 'th',
                    'value' => '<strong>' . ($mediaField['label'] ?? '') . '</strong>' .
                        '<code class="oes-object-identifier">' . ($mediaField['key'] ?? '') . '</code>'
                ]];

                foreach ($languages as $language)
                    $cells[] = [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('text',
                            'oes_config[media][acf_add_local_field_group][fields][' . $fieldKey . '][' . $language . ']',
                            'oes_config-media-acf_add_local_field_group-fields-' . $fieldKey . '-' . $language,
                            $mediaField[$language] ?? ''
                        )
                    ];

                $mediaRows[] = [
                    'cells' => $cells
                ];
            }

            $this->table_data[] = [
                'rows' => [
                    [
                        'type' => 'trigger',
                        'cells' => [
                            [
                                'value' => '<strong>' . __('Media', 'oes') . '</strong>'
                            ]
                        ]
                    ],
                    [
                        'type' => 'target',
                        'nested_tables' => [
                            [
                                'rows' => array_merge($this->language_row, $mediaRows)
                            ]
                        ]
                    ]
                ]
            ];


            /* get theme labels for post types -----------------------------------------------------------------------*/
            if(!empty($oes->post_types)) {
                $this->table_data[] = [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '',
                                    'class' => 'oes-expandable-row-20'
                                ],
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Post Types', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
                foreach ($oes->post_types as $postTypeKey => $postType)
                    $this->add_table('post_types', $postTypeKey, $postType);
            }


            /* get theme labels for taxonomies -----------------------------------------------------------------------*/
            if (!empty($oes->taxonomies)) {
                $this->table_data[] = [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '',
                                    'class' => 'oes-expandable-row-20'
                                ],
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Taxonomies', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
                foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                    $this->add_table('taxonomies', $taxonomyKey, $taxonomy);
            }
        }


        /**
         * Add table for object.
         *
         * @param string $identifier Post types or taxonomies.
         * @param string $objectKey The post type key or the taxonomy key.
         * @param array $object The post type object or the taxonomy object.
         */
        function add_table(string $identifier, string $objectKey, array $object)
        {

            $oes = OES();
            $languages = array_keys($oes->languages);

            /* prepare general labels --------------------------------------------------------------------------------*/
            $generalRows = [];
            $cells = [[
                'type' => 'th',
                'value' => '<strong>' . __('Label (Singular)', 'oes') . '</strong>'
            ]];

            foreach ($languages as $language)
                $cells[] = [
                    'class' => 'oes-table-transposed',
                    'value' => oes_html_get_form_element('text',
                        $identifier . '[' . $objectKey . '][oes_args][label_translations][' . $language . ']',
                        $identifier . '-' . $objectKey . '-oes_args-label_translations-' . $language,
                        $object['label_translations'][$language] ?? '')
                ];

            $generalRows[] = [
                'cells' => $cells
            ];

            /* plural */
            $cellsPlural = [[
                'type' => 'th',
                'value' => '<strong>' . __('Label (Plural)', 'oes') . '</strong>'
            ]];

            foreach ($languages as $language)
                $cellsPlural[] = [
                    'class' => 'oes-table-transposed',
                    'value' => oes_html_get_form_element('text',
                        $identifier . '[' . $objectKey . '][oes_args][label_translations_plural][' . $language . ']',
                        $identifier . '-' . $objectKey . '-oes_args-label_translations_plural-' . $language,
                        $object['label_translations_plural'][$language] ?? '')
                ];

            $generalRows[] = [
                'cells' => $cellsPlural
            ];


            /* add field labels --------------------------------------------------------------------------------------*/
            $fieldsRows = [];
            if (isset($object['field_options']) && !empty($object['field_options']))
                foreach ($object['field_options'] as $fieldKey => $field)
                    if (isset($field['type']) && !in_array($field['type'], ['tab', 'message'])) {

                        $cells = [[
                            'type' => 'th',
                            'value' => '<div><strong>' . ($field['label'] ?? $fieldKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $fieldKey . '</code></div>'
                        ]];

                        foreach ($languages as $language)
                            $cells[] = [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                    'fields[' . $objectKey . '][' . $fieldKey . '][label_translation_' .
                                    $language . ']',
                                    'fields-' . $objectKey . '-' . $fieldKey . '-label_translation_' . $language,
                                    $field['label_translation_' . $language] ?? ($field['label'] ?? $fieldKey)
                                )
                            ];

                        $fieldsRows[] = [
                            'cells' => $cells
                        ];
                    }


            /* other labels ------------------------------------------------------------------------------------------*/
            $archiveRows = $singleRows = [];
            if (isset($object['theme_labels']) && !empty($object['theme_labels'])) {
                ksort($object['theme_labels']);
                foreach ($object['theme_labels'] as $key => $label) {

                    $cells = [[
                        'type' => 'th',
                        'value' => '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                            __('Location: ', 'oes') . '</em>' . $label['location'] .
                            '</div>' .
                            '<div><code class="oes-object-identifier-br">' . $key . '</code></div>'
                    ]];

                    foreach ($languages as $language)
                        $cells[] = [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                $identifier . '[' . $objectKey . '][oes_args][theme_labels][' . $key . '][' .
                                $language . ']',
                                $identifier . '-' . $objectKey . '-oes_args-theme_labels-' . $key . '-' . $language,
                                $label[$language] ?? '')
                        ];

                    /* add to related table */
                    if (oes_starts_with($key, 'archive__'))
                        $archiveRows[] = [
                            'cells' => $cells
                        ];
                    elseif (oes_starts_with($key, 'single__'))
                        $singleRows[] = [
                            'cells' => $cells
                        ];
                    else
                        $generalRows[] = [
                            'cells' => $cells
                        ];
                }
            }

            /* prepare table */
            $nestedTable = [];
            if (!empty($generalRows)){
                $nestedTable[] = [
                    'type' => 'trigger',
                    'cells' => [
                        [
                            'value' => '<strong>' . __('General', 'oes') . '</strong>'
                        ]
                    ]
                ];
                $nestedTable[] = [
                    'type' => 'target',
                    'nested_tables' => [
                        [
                            'rows' => array_merge($this->language_row, $generalRows)
                        ]
                    ]
                ];
            }

            if (!empty($singleRows)){
                $nestedTable[] = [
                    'type' => 'trigger',
                    'cells' => [
                        [
                            'value' => '<strong>' . __('Single View', 'oes') . '</strong>'
                        ]
                    ]
                ];
                $nestedTable[] = [
                    'type' => 'target',
                    'nested_tables' => [
                        [
                            'rows' => array_merge($this->language_row, $singleRows)
                        ]
                    ]
                ];
            }

            if (!empty($archiveRows)){
                $nestedTable[] = [
                    'type' => 'trigger',
                    'cells' => [
                        [
                            'value' => '<strong>' . __('Archive View', 'oes') . '</strong>'
                        ]
                    ]
                ];
                $nestedTable[] = [
                    'type' => 'target',
                    'nested_tables' => [
                        [
                            'rows' => array_merge($this->language_row, $archiveRows)
                        ]
                    ]
                ];
            }

            if (!empty($fieldsRows)){
                $nestedTable[] = [
                    'type' => 'trigger',
                    'cells' => [
                        [
                            'value' => '<strong>' . __('Fields', 'oes') . '</strong>'
                        ]
                    ]
                ];
                $nestedTable[] = [
                    'type' => 'target',
                    'nested_tables' => [
                        [
                            'rows' => array_merge($this->language_row, $fieldsRows)
                        ]
                    ]
                ];
            }


            $this->table_data[] = [
                'rows' => [
                    [
                        'type' => 'trigger',
                        'cells' => [
                            [
                                'value' => '<strong>' . $object['label'] . '</strong>' .
                                    '<code class="oes-object-identifier">' . $objectKey . '</code>'
                            ]
                        ]
                    ],
                    [
                        'type' => 'target',
                        'nested_tables' => [
                            [
                                'rows' => $nestedTable
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels', 'theme-labels');
endif;