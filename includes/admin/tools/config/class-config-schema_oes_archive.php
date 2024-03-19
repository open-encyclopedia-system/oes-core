<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('Schema_OES_Archive')) :

    /**
     * Class Schema_OES_Archive
     *
     * Implement the config tool for admin configurations.
     */
    class Schema_OES_Archive extends Schema
    {

        //Overwrite parent
        function set_table_data_for_display(): void
        {
            if ($this->component == 'post_types') $this->set_post_type();
            elseif ($this->component == 'taxonomies') $this->set_taxonomies();
        }


        /**
         * Set post type options.
         * 
         * @return void
         */
        function set_post_type(): void
        {
            $postTypeData = OES()->post_types[$this->object] ?? [];

            /* get options */
            $selects = oes_get_object_select_options($this->object);
            $titleOptions = $selects['title'] ?? [];
            $options = $selects['all'] ?? [];

            /* add list and sorting title option, display option, data option and filter options */
            $this->table_data[] = [
                'rows' => [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Title for list display', 'oes') . '</strong>' .
                                    '<div>' . __('The field to be displayed as title on archive pages', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'post_types[' . $this->object . '][oes_args][display_titles][title_archive_display]',
                                    'post_types-' . $this->object . '-oes_args-display_titles-title_archive_display',
                                    $postTypeData['display_titles']['title_archive_display'] ?? 'wp-title',
                                    ['options' => $titleOptions])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Sorting title for list display', 'oes') . '</strong>' .
                                    '<div>' . __('The field to be sorted after on archive pages', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'post_types[' . $this->object . '][oes_args][display_titles][title_sorting_display]',
                                    'post_types-' . $this->object . '-oes_args-display_titles-title_sorting_display',
                                    $postTypeData['display_titles']['title_sorting_display'] ?? 'wp-title',
                                    ['options' => $titleOptions])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Display archive as single page list', 'oes') . '</strong>' .
                                    '<div>' .
                                    __('Display the archive as list, all posts on one page. ' .
                                        'Post type has no single pages (eg. glossary)', 'oes') .
                                    '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('checkbox',
                                    'post_types[' . $this->object . '][oes_args][archive_on_single_page]',
                                    'post_types-' . $this->object . '-oes_args-archive_on_single_page',
                                    $postTypeData['archive_on_single_page'] ?? false,
                                    ['hidden' => true])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Archive Data', 'oes') . '</strong>' .
                                    '<div>' . __('Additional information on archive list view', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'post_types[' . $this->object . '][oes_args][archive]',
                                    'post_types-' . $this->object . '-oes_args-archive',
                                    $postTypeData['archive'] ?? [],
                                    [
                                        'options' => $options,
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
                                'value' => '<strong>' . __('Archive Filter', 'oes') . '</strong>' .
                                    '<div>' .
                                    __('Elements that will be considered for the archive filter', 'oes') .
                                    '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'post_types[' . $this->object . '][oes_args][archive_filter]',
                                    'post_types-' . $this->object . '-oes_args-archive_filter',
                                    $postTypeData['archive_filter'] ?? [],
                                    [
                                        'options' => array_merge(['alphabet' => 'Alphabet'], $options),
                                        'multiple' => true,
                                        'class' => 'oes-replace-select2',
                                        'reorder' => true,
                                        'hidden' => true
                                    ])
                            ]
                        ]
                    ]
                ]
            ];
        }


        /**
         * Set taxonomy options.
         * 
         * @return void
         */
        function set_taxonomies(): void
        {
            $oes = OES();
            $taxonomyData = $oes->taxonomies[$this->object];

            /* get options */
            $selects = oes_get_object_select_options($this->object, false, ['title' => true]);
            $titleOptions = $selects['title'] ?? [];

            /* prepare filter option */
            $options = []; //@oesDevelopment Add more filter options.
            $optionsArchiveFilter = array_merge(['alphabet' => 'Alphabet'], $options);
            $this->table_data[] = [
                'rows' => [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Title for list display', 'oes') . '</strong>' .
                                    '<div>' . __('The field to be displayed as title on archive pages', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'taxonomies[' . $this->object . '][oes_args][display_titles][title_archive_display]',
                                    'taxonomies-' . $this->object . '-oes_args-display_titles-title_archive_display',
                                    $taxonomyData['display_titles']['title_archive_display'] ?? 'wp-title',
                                    ['options' => $titleOptions])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Sorting title for list display', 'oes') . '</strong>' .
                                    '<div>' . __('The field to be sorted after on archive pages', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'taxonomies[' . $this->object . '][oes_args][display_titles][title_sorting_display]',
                                    'taxonomies-' . $this->object . '-oes_args-display_titles-title_sorting_display',
                                    $taxonomyData['display_titles']['title_sorting_display'] ?? 'wp-title',
                                    ['options' => $titleOptions])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Archive Filter', 'oes') . '</strong>' .
                                    '<div>' .
                                    __('Elements that will be considered for the archive filter', 'oes') .
                                    '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'taxonomies[' . $this->object . '][oes_args][archive_filter]',
                                    'taxonomies-' . $this->object . '-oes_args-archive_filter',
                                    is_array($taxonomyData['archive_filter']) ?
                                        $taxonomyData['archive_filter'] :
                                        [],
                                    [
                                        'options' => $optionsArchiveFilter,
                                        'multiple' => true,
                                        'class' => 'oes-replace-select2',
                                        'reorder' => true,
                                        'hidden' => true
                                    ])
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema_OES_Archive', 'schema-oes_archive');

endif;