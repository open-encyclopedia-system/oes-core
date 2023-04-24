<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post_Type;
use WP_Taxonomy;

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Admin_Labels')) :

    /**
     * Class Admin_Labels
     *
     * Implement the config tool for admin labels configurations.
     */
    class Admin_Labels extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The following labels will affect the labels of custom post types, custom taxonomies and their ' .
                    'fields inside the editorial layer. This labels can be overwritten inside the frontend layer by ' .
                    'the frontend labels. You can add instructions for fields that will be displayed beneath the ' .
                    'field label.', 'oes') . '<br>' .
                sprintf(__('Find the frontend labels %shere%s.',
                    'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_settings_reading&select=theme-labels') . '">',
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
            $oes = OES();

            if (!empty($oes->other_fields)) {

                $nestedRows = [[
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Field', 'oes') . '</strong>'
                        ],
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Label', 'oes') .
                                '</strong><div>' . __('General name for this field.', 'oes') . '</div>'
                        ],
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Instructions', 'oes') .
                                '</strong><div>' . __('Add instructions to form editor in editorial layer.', 'oes') . '</div>'
                        ]
                    ]
                ]];
                foreach($oes->other_fields as $fieldKey => $field)
                    $nestedRows[] = ['cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' .
                                ($oes->other_fields[$fieldKey]['label'] ?? '') .
                                '</strong><div><code>' . $fieldKey . '</code></div>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                'oes_config[other_fields][' . $fieldKey . '][label]',
                                'oes_config-other_fields-' . $fieldKey . '-label',
                                $oes->other_fields[$fieldKey]['label'] ?? '')
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => ((isset($field['type']) && $field['type'] === 'tab') ?
                                '' :
                                oes_html_get_form_element('text',
                                    'oes_config[other_fields][' . $fieldKey . '][instructions]',
                                    'oes_config-other_fields-' . $fieldKey . '-instructions',
                                    $oes->other_fields[$fieldKey]['instructions'] ?? ''))
                        ]
                    ]];

                $this->table_data = [
                    [
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
                                        'value' => '<strong>' . __('General', 'oes') . '</strong>'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'rows' => [
                            [
                                'type' => 'trigger',
                                'cells' => [
                                    [
                                        'value' => '<strong>' . __('General Fields', 'oes') . '</strong>'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'target',
                                'nested_tables' => [
                                    [
                                        'rows' => $nestedRows
                                    ]
                                ]
                            ]
                        ]]];
            }

            /* get theme labels from post types and taxonomies */
            if (!empty($oes->post_types))
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

            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* get post type object */
                $postTypeObject = get_post_type_object($postTypeKey);

                $nestedRows = [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Label (Plural)', 'oes') .
                                    '</strong><div>' . __('General name for the post type.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'colspan' => 2,
                                'value' => oes_html_get_form_element('text',
                                    'post_types[' . $postTypeKey . '][register_args][labels][name]',
                                    'post_types-' . $postTypeKey . '-register_args-label_name',
                                    $postTypeObject->labels->name ?? '')
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Label (Singular)', 'oes') .
                                    '</strong><div>' . __('Name for one post of the post type.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'colspan' => 2,
                                'value' => oes_html_get_form_element('text',
                                    'post_types[' . $postTypeKey . '][register_args][labels][singular_name]',
                                    'post_types-' . $postTypeKey . '-register_args-labels-singular_name',
                                    $postTypeObject->labels->singular_name ?? '')
                            ]
                        ]
                    ]
                ];

                $rows = $this->get_object_rows($postTypeKey, $postTypeData);
                if (!empty($rows)) $nestedRows = array_merge($nestedRows, $rows);

                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                                        '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => $nestedRows
                                ]
                            ]
                        ]
                    ]
                ];
            }


            if (!empty($oes->taxonomies))
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

            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData) {

                $taxonomyObject = get_taxonomy($taxonomyKey);

                $nestedRows = [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Label (Plural)', 'oes') .
                                    '</strong><div>' . __('General name of the taxonomy.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'colspan' => 2,
                                'value' => oes_html_get_form_element('text',
                                    'taxonomies[' . $taxonomyKey . '][register_args][labels][name]',
                                    'taxonomies-' . $taxonomyKey . '-register_args-labels-name',
                                    $taxonomyObject->labels->name ?? '')
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Label (Singular)', 'oes') .
                                    '</strong><div>' . __('Name for one term of the taxonomy.', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'colspan' => 2,
                                'value' => oes_html_get_form_element('text',
                                    'taxonomies[' . $taxonomyKey . '][register_args][labels][singular_name]',
                                    'taxonomies-' . $taxonomyKey . '-register_args-labels-singular_name',
                                    $taxonomyObject->labels->singular_name ?? '')
                            ]
                        ]
                    ]
                ];

                $rows = $this->get_object_rows($taxonomyKey, $taxonomyData);
                if (!empty($rows)) $nestedRows = array_merge($nestedRows, $rows);

                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . ($taxonomyData['label'] ?? $taxonomyKey) . '</strong>' .
                                        '<code class="oes-object-identifier">' . $taxonomyKey . '</code>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => $nestedRows
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }


        /**
         * Get row data for object.
         *
         * @param string $objectKey The post type key or taxonomy key.
         * @param WP_Post_Type|WP_Taxonomy|null $object The post type object or taxonomy object.
         * @return array Returns the object field table.
         */
        function get_object_rows(string $objectKey, $object): array
        {
            $rows = [];

            /* loop through fields */
            if (isset($object['field_options']) && !empty($object['field_options'])) {

                /* add header row */
                $rows = [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Field', 'oes') . '</strong>'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Label', 'oes') .
                                    '</strong><div>' . __('General name for this field.', 'oes') . '</div>'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Instructions', 'oes') .
                                    '</strong><div>' . __('Add instructions to form editor in editorial layer.', 'oes') . '</div>'
                            ]
                        ]
                    ]
                ];

                foreach ($object['field_options'] as $fieldKey => $field)
                    if (isset($field['type']) && $field['type'] === 'tab' && $fieldKey !== 'field_oes_tab_editorial')
                        $rows[] = [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => ((isset($field['label']) && !empty($field['label'])) ?
                                        ('<strong>' . $field['label'] . __(' (Tab Name)', 'oes') .
                                            '</strong><div><code>' . $fieldKey . '</code></div>') :
                                        $fieldKey)
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'fields[' . $objectKey . '][' . $fieldKey . '][label]',
                                        'fields-' . $objectKey . '-' . $fieldKey . '-label',
                                        $field['label'] ?? '')
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => ''
                                ]
                            ]
                        ];
                    elseif (!in_array($fieldKey, ['field_oes_tab_editorial', 'field_oes_status', 'field_oes_comment', 'field_language_group_message']))
                        $rows[] = [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => ((isset($field['label']) && !empty($field['label'])) ?
                                        ('<strong>' . $field['label'] . '</strong><div><code>' . $fieldKey . '</code></div>') :
                                        $fieldKey)
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'fields[' . $objectKey . '][' . $fieldKey . '][label]',
                                        'fields-' . $objectKey . '-' . $fieldKey . '-label',
                                        $field['label'] ?? '')
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'fields[' . $objectKey . '][' . $fieldKey . '][instructions]',
                                        'fields-' . $objectKey . '-' . $fieldKey . '-instructions',
                                        get_field_object($fieldKey)['instructions'] ?? '')
                                ]
                            ]
                        ];
            }

            return $rows;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Labels', 'admin-labels');

endif;