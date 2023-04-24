<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_all_object_fields;

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Single_View')) :

    /**
     * Class Theme_Single_View
     *
     * Implement the config tool for theme configurations: single view.
     */
    class Theme_Single_View extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can choose the field that will be displayed as title of a post object with the OES feature ' .
                    '<b>Titles</b> (The field to be displayed as title on the single page).', 'oes') . '<br>' .
                __('The single view of a post object includes a table of metadata. You can define which post data ' .
                    'is to be considered as metadata in the OES feature <b>Metadata</b>.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();
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
                foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                    /* get all fields for this post type */
                    $allFields = get_all_object_fields($postTypeKey, false, false);

                    /* prepare html for title options */
                    $titleOptions= [];
                    $titleOptions['wp-title'] = __('Post Title (WordPress)', 'oes');
                    foreach ($allFields as $fieldKey => $singleField)
                        if ($singleField['type'] == 'text') $titleOptions[$fieldKey] = empty($singleField['label']) ? $fieldKey : $singleField['label'];

                    /* prepare options */
                    $options = [];
                    $postTypesRelationships = [];
                    if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                        foreach ($postTypeData['field_options'] as $fieldKey => $field)
                            if (isset($field['type']) && !in_array($field['type'], ['tab', 'message', 'accordion', 'clone', 'group', 'flexible_content', 'repeater'])) {
                                $options[$fieldKey] = empty($field['label']) ? $fieldKey : $field['label'];
                                if (in_array($field['type'], ['relationship', 'post_object'])) {
                                    $checkForPostTypes = get_field_object($fieldKey)['post_type'] ?? [];
                                    if (is_string($checkForPostTypes)) $checkForPostTypes = [$checkForPostTypes];
                                    if (!empty($checkForPostTypes))
                                        foreach ($checkForPostTypes as $singlePostType)
                                            $postTypesRelationships['post_type__' . $singlePostType] =
                                                __('Post Type: ', 'oes') .
                                                ($oes->post_types[$singlePostType]['label'] ?? $singlePostType);
                                }
                            }
                    if (!empty($postTypesRelationships)) $options = array_merge($options, $postTypesRelationships);


                    /* add taxonomies */
                    if (get_post_type_object($postTypeKey)->taxonomies)
                        foreach (get_post_type_object($postTypeKey)->taxonomies as $taxonomy)
                            $options['taxonomy__' . $taxonomy] = __('Taxonomy: ', 'oes') .
                                ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);

                    /* add parent options */
                    if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                        isset($oes->post_types[$postTypeData['parent']]['field_options']) &&
                        !empty($oes->post_types[$postTypeData['parent']]['field_options']))
                        foreach ($oes->post_types[$postTypeData['parent']]['field_options'] as $parentFieldKey => $parentField)
                            if (isset($parentField['type']) && !in_array($parentField['type'], ['tab', 'message', 'accordion', 'clone', 'group', 'flexible_content', 'repeater']))
                                $options['parent__' . $parentFieldKey] = __('Parent: ', 'oes') . (empty($parentField['label']) ? $parentFieldKey : $parentField['label']);

                    /* add parent taxonomies */
                    if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                        get_post_type_object($postTypeData['parent'])->taxonomies)
                        foreach (get_post_type_object($postTypeData['parent'])->taxonomies as $taxonomy)
                            $options['parent_taxonomy__' . $taxonomy] = __('Parent Taxonomy: ', 'oes') .
                                ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);


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
                                        'rows' => [
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Title for single display', 'oes') .
                                                            '</strong><div>' . __('The field to be displayed as title on the single page', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][display_titles][title_display]',
                                                            'post_types-' . $postTypeKey . '-oes_args-display_titles-title_display',
                                                            $postTypeData['display_titles']['title_display'] ?? 'wp-title',
                                                            ['options' => $titleOptions])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Metadata', 'oes') . '</strong>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][metadata]',
                                                            'post_types-' . $postTypeKey . '-oes_args-metadata',
                                                            $postTypeData['metadata'] ?? [],
                                                            ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2', 'reorder' => true])
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

            /* taxonomies --------------------------------------------------------------------------------------------*/
            if(!empty($oes->taxonomies)) {
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

                    $rows = [];

                    /* redirect option */
                    $redirectOptions = ['none' => '-'];
                    foreach($oes->post_types as $postTypeKey => $postTypeData)
                        $redirectOptions[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
                    $rows[] = [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Redirect', 'oes') .
                                    '</strong><div>' . __('Redirect the term to an archive page', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'taxonomies[' . $taxonomyKey . '][oes_args][redirect]',
                                    'taxonomies-' . $taxonomyKey . '-oes_args-redirect',
                                    (isset($taxonomyData['redirect']) && is_string($taxonomyData['redirect']) ? $taxonomyData['redirect'] : 'none'),
                                    ['options' => $redirectOptions])
                            ]
                        ]
                    ];

                    /* get all fields for this post type */
                    $allFields = get_all_object_fields($taxonomyKey, false, false);
                    if (!empty($allFields)) {

                        /* prepare html for title options */
                        $titleOptions['wp-title'] = __('Name (WordPress)', 'oes');
                        foreach ($allFields as $fieldKey => $singleField)
                            if ($singleField['type'] == 'text') $titleOptions[$fieldKey] = $singleField['label'];

                        $rows[] = [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Title for single display', 'oes') .
                                        '</strong><div>' . __('The field to be displayed as title on the single page', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('select',
                                        'taxonomies[' . $taxonomyKey . '][oes_args][display_titles][title_display]',
                                        'taxonomies-' . $taxonomyKey . '-oes_args-display_titles-title_display',
                                        $taxonomyData['display_titles']['title_display'] ?? 'wp-title',
                                        ['options' => $titleOptions])
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
                                        'value' => '<strong>' . ($taxonomyData['label'] ?? $taxonomyKey) . '</strong>' .
                                            '<code class="oes-object-identifier">' . $taxonomyKey . '</code>'
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
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Single_View', 'theme-single-view');
endif;