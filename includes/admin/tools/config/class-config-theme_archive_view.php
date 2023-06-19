<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_all_object_fields;

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Archive_View')) :

    /**
     * Class Theme_Archive_View
     *
     * Implement the config tool for theme configurations: archive view.
     */
    class Theme_Archive_View extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can choose the field that will be displayed as title of a post object with the OES feature ' .
                    '<b>Titles</b>. You can also choose which field will be used for sorting the list of post objects ' .
                    'alphabetically.', 'oes') . '<br>' .
                __('The post type parameter <b>Has Archive</b> enables the archive view inside the frontend layer.',
                    'oes') . '<br>' .
                __('When the OES feature <b>Display archive as list</b> is enabled the archive will not be displayed ' .
                    'as list of posts linking to the single view but instead as list of all posts including the post ' .
                    'content without the single view option.', 'oes') . '<br>' .
                __('You can define data to be included on the archive page in a ' .
                    'dropdown table in the OES feature <b>Archive</b>. The OES feature <b>Archive Filter</b> ' .
                    'enables considered facet filters for the archive page.', 'oes') .
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
                    $titleOptions = [];
                    $titleOptions['wp-title'] = __('Post Title (WordPress)', 'oes');
                    foreach ($allFields as $fieldKey => $singleField)
                        if (in_array($singleField['type'], ['text', 'date_picker']))
                            $titleOptions[$fieldKey] = empty($singleField['label']) ? $fieldKey : $singleField['label'];

                    /* prepare options */
                    $options = [];
                    $postTypesRelationships = [];
                    if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                        foreach ($postTypeData['field_options'] as $fieldKey => $field)
                            if (isset($field['type']) && !in_array($field['type'], ['tab', 'message'])) {
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
                            if (isset($parentField['type']) && !in_array($parentField['type'], ['tab', 'message']))
                                $options['parent__' . $parentFieldKey] = __('Parent: ', 'oes') . $parentField['label'];

                    /* add parent taxonomies */
                    if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                        get_post_type_object($postTypeData['parent'])->taxonomies)
                        foreach (get_post_type_object($postTypeData['parent'])->taxonomies as $taxonomy)
                            $options['parent_taxonomy__' . $taxonomy] = __('Parent Taxonomy: ', 'oes') .
                                ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);

                    /* add alphabet for archive filter */
                    $optionsArchiveFilter = array_merge(['alphabet' => 'Alphabet'], $options);

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
                                                        'value' => '<strong>' . __('Title for list display', 'oes') .
                                                            '</strong><div>' . __('The field to be displayed as title on archive pages', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][display_titles][title_archive_display]',
                                                            'post_types-' . $postTypeKey . '-oes_args-display_titles-title_archive_display',
                                                            $postTypeData['display_titles']['title_archive_display'] ?? 'wp-title',
                                                            ['options' => $titleOptions])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Sorting title for list display', 'oes') .
                                                            '</strong><div>' . __('The field to be sorted after on archive pages', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][display_titles][title_sorting_display]',
                                                            'post_types-' . $postTypeKey . '-oes_args-display_titles-title_sorting_display',
                                                            $postTypeData['display_titles']['title_sorting_display'] ?? 'wp-title',
                                                            ['options' => $titleOptions])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Has Archive', 'oes') .
                                                            '</strong><div>' . __('Archive is available in frontend', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('checkbox',
                                                            'post_types[' . $postTypeKey . '][register_args][has_archive]',
                                                            'post_types-' . $postTypeKey . '-register_args-has_archive',
                                                            get_post_type_object($postTypeKey)->has_archive ?? false,
                                                            ['hidden' => true])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Display archive as single page list', 'oes') .
                                                            '</strong><div>' .
                                                            __('Display the archive as list, all posts on one page. Post type has no single pages (eg. glossary)', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('checkbox',
                                                            'post_types[' . $postTypeKey . '][oes_args][archive_on_single_page]',
                                                            'post_types-' . $postTypeKey . '-oes_args-archive_on_single_page',
                                                            $postTypeData['archive_on_single_page'] ?? false,
                                                            ['hidden' => true])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Archive Data', 'oes') .
                                                            '</strong><div>' . __('Additional information on archive list view', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][archive]',
                                                            'post_types-' . $postTypeKey . '-oes_args-archive',
                                                            $postTypeData['archive'] ?? [],
                                                            ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true, 'hidden' => true])
                                                    ]
                                                ]
                                            ],
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Archive Filter', 'oes') .
                                                            '</strong><div>' . __('Elements that will be considered for the archive filter', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'post_types[' . $postTypeKey . '][oes_args][archive_filter]',
                                                            'post_types-' . $postTypeKey . '-oes_args-archive_filter',
                                                            $postTypeData['archive_filter'] ?? [],
                                                            ['options' => $optionsArchiveFilter, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true, 'hidden' => true])
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


                    /* add alphabet for archive filter */
                    $options = []; //@oesDevelopment Add more filter options.
                    $optionsArchiveFilter = array_merge(['alphabet' => 'Alphabet'], $options);

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
                                        'rows' => [
                                            [
                                                'cells' => [
                                                    [
                                                        'type' => 'th',
                                                        'value' => '<strong>' . __('Archive Filter', 'oes') .
                                                            '</strong><div>' . __('Elements that will be considered for the archive filter', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'taxonomies[' . $taxonomyKey . '][oes_args][archive_filter]',
                                                            'taxonomies-' . $taxonomyKey . '-oes_args-archive_filter',
                                                            is_array($taxonomyData['archive_filter']) ? $taxonomyData['archive_filter'] : [],
                                                            ['options' => $optionsArchiveFilter, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true, 'hidden' => true])
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


            if(!empty($oes->theme_index_pages)) {
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
                                    'value' => '<strong>' . __('Index', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
                foreach ($oes->theme_index_pages as $indexKey => $indexData) {

                    $optionsArchiveFilter = ['alphabet' => 'Alphabet', 'all' => 'All'];

                    $this->table_data[] = [
                        'rows' => [
                            [
                                'type' => 'trigger',
                                'cells' => [
                                    [
                                        'value' => '<strong>' . (!empty($indexData['label']['language0']) ? $indexData['label']['language0'] : $indexKey) . '</strong>' .
                                            '<code class="oes-object-identifier">' . $indexKey . '</code>'
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
                                                        'value' => '<strong>' . __('Archive Filter', 'oes') .
                                                            '</strong><div>' . __('Elements that will be considered for the archive filter', 'oes') . '</div>'
                                                    ],
                                                    [
                                                        'class' => 'oes-table-transposed',
                                                        'value' => oes_html_get_form_element('select',
                                                            'oes_config[theme_index_pages][' . $indexKey . '][archive_filter]',
                                                            'oes_config-theme_index_pages-' . $indexKey . '-archive_filter',
                                                            $indexData['archive_filter'] ?? [],
                                                            ['options' => $optionsArchiveFilter, 'multiple' => true, 'class' => 'oes-replace-select2',
                                                                'reorder' => true, 'hidden' => true])
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
    register_tool('\OES\Admin\Tools\Theme_Archive_View', 'theme-archive-view');
endif;