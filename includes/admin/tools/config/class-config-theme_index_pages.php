<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Index_Pages')) :

    /**
     * Class Theme_Index_Pages
     *
     * Implement the config tool for theme configurations: index pages.
     */
    class Theme_Index_Pages extends Config
    {

        //Set parameters.
        public bool $empty_allowed = true;
        public bool $empty_input = true;


        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Index</b> (Register) allows you to ' .
                    'define an index page on which post types will be displayed that are linked to the main ' .
                    'post type (usually a type of article). The index post types will include a display of their ' .
                    'connection within the encyclopaedia on the frontend single display.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function additional_html(): string
        {
            return '<div class="oes-button-wrapper">'.
                '<input type="submit" name="add_new_index" id="add_new_index" ' .
                'class="button button-secondary button-large" ' .
                'value="' . __('Add New Index', 'oes') . '">' .
                '</div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();
            $modifiedIndexPageKey = 0;
            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage) {

                    /* make sure that index page key is string */
                    if(!is_string($indexPageKey)) {
                        while(isset($oes->theme_index_pages['index' . $modifiedIndexPageKey])) ++$modifiedIndexPageKey;
                        $indexPageKey = 'index' . $modifiedIndexPageKey;
                        ++$modifiedIndexPageKey;
                    }

                    $elementOptions = $objectOptions = [];
                    foreach ($oes->post_types as $key => $postType) {
                        $elementOptions[$key] = $postType['label'];
                        $objectOptions[$key] = $postType['label'];
                    }
                    foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                    //@oesDevelopment More filter options
                    $optionsArchiveFilter = ['alphabet' => 'Alphabet'];
                    $innerRows = [
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Page Slug', 'oes') .
                                        '</strong><div>' .
                                        sprintf(
                                            __('Slug for the Index Page (relative to %s/).', 'oes'), get_site_url()) .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][slug]',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-slug',
                                        $indexPage['slug'] ?? 'register')
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Considered Object', 'oes') .
                                        '</strong><div>' . __('Considered Object for Index (Nachweis)).', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('select',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][element]',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-element',
                                        $indexPage['element'] ?? '',
                                        [
                                            'options' => $elementOptions,
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
                                    'value' => '<strong>' . __('Objects', 'oes') .
                                        '</strong><div>' . __('Objects on the Index Page.', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('select',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][objects]',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-objects',
                                        $indexPage['objects'] ?? [],
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
                                    'value' => '<strong>' . __('Archive Filter', 'oes') . '</strong>' .
                                        '<div>' . __('Elements that will be considered for the archive filter', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('select',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][archive_filter]',
                                        'oes_config-theme_index_pages-' . $indexPageKey . '-archive_filter',
                                        $indexPage['archive_filter'] ?? [],
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
                    ];
                    foreach($oes->languages as $languageKey => $languageData){
                        $innerRows[] = [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Label', 'oes') .
                                        ' (' . ($languageData['label'] ?? $languageKey) . ')'.
                                        '</strong><div>' . __('Label of the Index Page', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][label][' . $languageKey . ']',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-label-' . $languageKey,
                                        $indexPage['label'][$languageKey] ?? '')
                                ]
                            ]
                        ];
                    }

                    /* add delete option */
                    $innerRows[] = [
                        'cells' => [
                            [
                                'type' => 'th',
                                'colspan' => 2,
                                'class' => 'oes-table-transposed',
                                'value' => '<a href="javascript:void(0);" ' .
                                    'onClick="oesConfigTableDeleteRow(this)" ' .
                                    'class="oes-highlighted button">' .
                                    __('Delete This Index', 'oes') .
                                    '</a>'
                            ]
                        ]
                    ];

                    $this->table_data[] = [
                        'rows' => [
                            [
                                'type' => 'trigger',
                                'cells' => [
                                    [
                                        'value' => '<strong>' .
                                            (empty($indexPage['label']['language0']) ?
                                                $indexPageKey  :
                                                $indexPage['label']['language0']) . '</strong>'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'target',
                                'nested_tables' => [
                                    [
                                        'rows' => $innerRows
                                    ]
                                ]
                            ]
                        ]
                    ];

                }
        }


        //Overwrite parent.
        function get_modified_post_data(array $data): array {
            if(isset($data['add_new_index']))
                $data['oes_config']['theme_index_pages'][] = [
                    'slug' => 'hidden',
                    'element' => [],
                    'objects' => [],
                    'archive_filter' => [],
                    'label' => []
                ];
            elseif($data['oes_hidden'] && (empty($data['oes_config']['theme_index_pages'])))
                $data['oes_config']['theme_index_pages'] = [];
            return $data;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Index_Pages', 'theme-index-pages');
endif;