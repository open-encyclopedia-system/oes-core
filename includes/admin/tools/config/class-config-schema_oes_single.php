<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('Schema_OES_Single')) :

    /**
     * Class Schema_OES_Single
     *
     * Implement the config tool for admin configurations.
     */
    class Schema_OES_Single extends Schema
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
            $fieldOptions = array_merge($selects['fields'] ?? [], $selects['parent'] ?? []);

            /* title options */
            $rows[] = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Title for single display', 'oes') . '</strong>' .
                            '<div>' . __('The field to be displayed as title on the single page', 'oes') . '</div>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('select',
                            'post_types[' . $this->object . '][oes_args][display_titles][title_display]',
                            'post_types-' . $this->object . '-oes_args-display_titles-title_display',
                            $postTypeData['display_titles']['title_display'] ?? 'wp-title',
                            ['options' => $titleOptions])
                    ]
                ]
            ];
            
            /* metadata options */
            $rows[] = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Metadata', 'oes') . '</strong>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('select',
                            'post_types[' . $this->object . '][oes_args][metadata]',
                            'post_types-' . $this->object . '-oes_args-metadata',
                            $postTypeData['metadata'] ?? [],
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

            /* prepare and loop through schema options */
            if ($this->oes_type == 'single-article')
                $schemaOptions = [
                    'authors' => [
                        'label' => __('Authors', 'oes'),
                        'multiple' => true
                    ],
                    'creators' => [
                        'label' => __('Creators', 'oes'),
                        'multiple' => true
                    ],
                    'subtitle' => [
                        'label' => __('Subtitle', 'oes'),
                        'pattern' => true
                    ],
                    'citation' => [
                        'label' => __('Citation', 'oes'),
                        'pattern' => true
                    ],
                    'excerpt' => [
                        'label' => __('Excerpt', 'oes')
                    ],
                    'featured_image' => [
                        'label' => __('Featured Image', 'oes')
                    ],
                    'licence' => [
                        'label' => __('Licence', 'oes'),
                        'options' => $options
                    ],
                    'pub_date' => [
                        'label' => __('Publication Date', 'oes')
                    ],
                    'edit_date' => [
                        'label' => __('Edit Date', 'oes')
                    ],
                    'language' => [
                        'label' => __('Language', 'oes')
                    ],
                    'version_field' => [
                        'label' => __('Version', 'oes')
                    ],
                    'literature' => [
                        'label' => __('Literature', 'oes'),
                        'multiple' => true
                    ],
                    'terms' => [
                        'label' => __('Terms', 'oes'),
                        'multiple' => true,
                        'options' => array_merge($selects['taxonomies'] ?? [],
                            $selects['parent-taxonomies'] ?? [])
                    ],
                    'external' => [
                        'label' => __('Fields with external links', 'oes'),
                        'multiple' => true
                    ],
                    'lod' => [
                        'label' => __('LoD Fields', 'oes'),
                        'multiple' => true
                    ]
                ];
            elseif ($this->oes_type == 'single-contributor')
                $schemaOptions = [
                    'vita' => [
                        'label' => __('Vita', 'oes')
                    ],
                    'publications' => [
                        'label' => __('Publications', 'oes'),
                        'multiple' => true
                    ],
                    'language' => [
                        'label' => __('Language', 'oes')
                    ],
                    'external' => [
                        'label' => __('Fields with external links', 'oes'),
                        'multiple' => true
                    ]
                ];
            else $schemaOptions = [
                'language' => [
                    'label' => __('Language', 'oes')
                ],
                'external' => [
                    'label' => __('Fields with external links', 'oes'),
                    'multiple' => true
                ]
            ];


            /**
             * Filters the schema options.
             *
             * @param array $schemaOptions The OES schema options.
             */
            $schemaOptions = apply_filters('oes/schema_options_single',
                $schemaOptions,
                $this->oes_type,
                $this->object,
                $this->component);


            foreach ($schemaOptions as $paramKey => $param) {

                /* reset args */
                $args = [];

                /* prepare value and select configuration for multiple select options */
                if ($param['multiple'] ?? false) {
                    $args = [
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ];
                    $value = is_array($postTypeData[$paramKey]) ? $postTypeData[$paramKey] : [];
                } else $value = is_bool($postTypeData[$paramKey]) ?
                    '' :
                    ($postTypeData[$paramKey] ?? '');

                /* prepare select options */
                $args['options'] = $param['options'] ??
                    ((isset($param['multiple']) && $param['multiple']) ?
                        $fieldOptions :
                        array_merge(['none' => '-'], $fieldOptions));

                /* option name */
                if(isset($param['option_name'])){
                    $optionName = 'oes_option[' . $param['option_name'] . ']';
                    $value = get_option($param['option_name']);
                }else {
                    $optionName = 'post_types[' . $this->object . '][oes_args][' . $paramKey . ']';
                }
                $optionID = str_replace(['[', ']'], ['', '-'], $optionName);

                /* prepare row with or without pattern */
                if (isset($param['pattern'])) {

                    /* decode value */
                    $jsonValue = is_array($value) ? $value : [];

                    $triggerText = '';
                    if (is_array($jsonValue['pattern'] ?? false))
                        foreach ($jsonValue['pattern'] as $part) {
                            $triggerText .= $part['prefix'] . $part['string_value'];
                            if ($part['field_key'] != 'none')
                                $triggerText .= '[' . ($options[$part['field_key']] ?? $part['field_key']) . ']';
                            $triggerText .= $part['suffix'];
                        }
                    if (empty($triggerText)) $triggerText = __('Use Pattern instead', 'oes');

                    $patternID = '_post_types-' . $this->object . '-oes_args-' . $paramKey;
                    $patternButton = '<div class="oes-config-subline">' .
                        '<a href="javascript:void(0);" id="' . $patternID .
                        '" onclick="oesPattern.InitPanel(\'' . $patternID . '\')">' . $triggerText . '</a>' .
                        '</div>';

                    $rows[] = [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong id="post_types-' . $this->object .
                                    '-oes_args-' . $paramKey . '_label">' . $param['label'] . '</strong>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                        'post_types[' . $this->object . '][oes_args][' . $paramKey . '][field]',
                                        'post_types-' . $this->object . '-oes_args-' . $paramKey . '-field',
                                        $jsonValue['field'] ?? 'none',
                                        $args) .
                                    oes_html_get_form_element('text',
                                        'post_types[' . $this->object . '][oes_args][' . $paramKey . '][pattern]',
                                        'post_types-' . $this->object . '-oes_args-' . $paramKey . '-pattern',
                                        (isset($jsonValue['pattern']) ?
                                            (str_replace('"', '&quot;', json_encode($jsonValue['pattern']))) :
                                            ''),
                                        array_merge(['class' => 'oes-display-none'], $args)) .
                                    $patternButton
                            ]
                        ]
                    ];
                } else
                    $rows[] = [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . $param['label'] . '</strong>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    $optionName,
                                    $optionID,
                                    $value,
                                    $args)
                            ]
                        ]
                    ];
            }

            /* add data */
            $this->table_data[] = ['rows' => $rows];
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

            /* prepare redirect option */
            $redirectOptions = ['none' => '-'];
            foreach ($oes->post_types as $postTypeKey => $postTypeData)
                $redirectOptions[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
            $rows[] = [
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Redirect', 'oes') . '</strong>' .
                            '<div>' . __('Redirect the term to an archive page', 'oes') . '</div>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('select',
                            'taxonomies[' . $this->object . '][oes_args][redirect]',
                            'taxonomies-' . $this->object . '-oes_args-redirect',
                            (isset($taxonomyData['redirect']) && is_string($taxonomyData['redirect']) ?
                                $taxonomyData['redirect'] :
                                'none'),
                            ['options' => $redirectOptions])
                    ]
                ]
            ];

            /* prepare title option */
            if (!empty($titleOptions))
                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Title for single display', 'oes') . '</strong>' .
                                '<div>' . __('The field to be displayed as title on the single page', 'oes') . '</div>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'taxonomies[' . $this->object . '][oes_args][display_titles][title_display]',
                                'taxonomies-' . $this->object . '-oes_args-display_titles-title_display',
                                $taxonomyData['display_titles']['title_display'] ?? 'wp-title',
                                ['options' => $titleOptions])
                        ]
                    ]
                ];


            /* add data */
            $this->table_data[] = [
                'rows' => $rows
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema_OES_Single', 'schema-oes_single');

endif;