<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Index_Pages')) :

    /**
     * Class Theme_Index_Pages
     *
     * Implement the config tool for theme configurations: index pages.
     */
    class Theme_Index_Pages extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Index</b> allows you to ' .
                    'define an index page on which post types will be displayed that are linked to the main ' .
                    'post type (usually a type of article). The index post types will include a display of their ' .
                    'connection within the encyclopaedia on the frontend single display.', 'oes') .
                '</p></div>';
        }

        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();
            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage) {

                    $elementOptions = $objectOptions = [];
                    foreach ($oes->post_types as $key => $postType) {
                        $elementOptions[$key] = $postType['label'];
                        $objectOptions[$key] = $postType['label'];
                    }
                    foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                    $innerRows = [
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Page Slug', 'oes') .
                                        '</strong><div>' .
                                        sprintf(__('Slug for the Index Page (relative to %s/).', 'oes'), get_site_url()) . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][slug]',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-slug',
                                        $oes->theme_index_pages[$indexPageKey]['slug'] ?? 'register')
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Considered Object', 'oes') .
                                        '</strong><div>' . __('Considered Object for Index (Nachweis)).', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('select',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][element]',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-element',
                                        $oes->theme_index_pages[$indexPageKey]['element'] ?? '',
                                        ['options' => $elementOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                            'reorder' => true])
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
                                        $oes->theme_index_pages[$indexPageKey]['objects'] ?? [],
                                        ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                            'reorder' => true])
                                ]
                            ]
                        ]
                    ];
                    foreach($oes->languages as $languageKey => $languageData){
                        $innerRows[] = [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Label', 'oes') . ' (' . ($languageData['label'] ?? $languageKey) . ')'.
                                        '</strong><div>' . __('Label of the Index Page', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('text',
                                        'oes_config[theme_index_pages][' . $indexPageKey . '][label][' . $languageKey . ']',
                                        'oes_config-theme-index-pages-' . $indexPageKey .'-label-' . $languageKey,
                                        $oes->theme_index_pages[$indexPageKey]['label'][$languageKey] ?? '')
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
                                        'value' => '<strong>' .
                                            (empty($oes->theme_index_pages[$indexPageKey]['label'][$oes->main_language]) ?
                                                $indexPageKey :
                                                $oes->theme_index_pages[$indexPageKey]['label'][$oes->main_language]) . '</strong>'
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
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Index_Pages', 'theme-index-pages');
endif;