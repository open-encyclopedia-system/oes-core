<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Theme_Labels')) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists('Theme_Labels_Objects')) :

    /**
     * Class Theme_Labels_Objects
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Labels_Objects extends Theme_Labels
    {

        public bool $expand_button = true;


        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();
            $this->set_language_row();

            /* prepare table head */
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


            /* get theme labels for post types -----------------------------------------------------------------------*/
            if (!empty($oes->post_types)) {
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
            if (!empty($generalRows)) {
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

            if (!empty($singleRows)) {
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

            if (!empty($archiveRows)) {
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

            if (!empty($fieldsRows)) {
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
    register_tool('\OES\Admin\Tools\Theme_Labels_Objects', 'theme-labels-objects');
endif;