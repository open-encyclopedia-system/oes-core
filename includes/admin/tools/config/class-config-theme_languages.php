<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Languages')) :

    /**
     * Class Theme_Languages
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Languages extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('If you are using an OES theme you can define labels for the templates that will be rendered ' .
                    'on certain part of the pages or for specific languages if you are using the OES feature ' .
                    '<b>Bilingualism</b>. Most of the labels are defined by your OES project plugin.', 'oes') .
                '</p><p>' .
                __('OES can be displayed in different languages. ' .
                    'Navigation elements will be displayed in the chosen language. Other elements might be ' .
                    'displayed in a different language depending on implemented language switches ' .
                    '(e.g. language switch for articles). Note that the main language ' .
                    '(the default language inside the editorial layer) can be different from the ' .
                    'primary language (the default language inside the frontend layer). ' .
                    'The abbreviation is used for e.g. the language switch.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            /* get global OES instance */
            $oes = OES();

            /* prepare languages */
            $languageOptions = [];
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language)
                    $languageOptions[$languageKey] = $language['label'];


            /* Language label option ---------------------------------------------------------------------------------*/
            $tableBody = [];
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language)
                    $tableBody[] = [
                        'cells' => [
                            ['value' => '<code>' . $languageKey . ($languageKey === 'language0' ? __(' (Primary Language)', 'oes') : '') . '</code>'],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                'oes_config[languages][' . $languageKey . '][label]',
                                'oes_config-languages-' . $languageKey . '-label',
                                $language['label']
                            )],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                'oes_config[languages][' . $languageKey . '][abb]',
                                'oes_config-languages-' . $languageKey . '-abb',
                                $language['abb'] ?? ''
                            )]
                        ]
                    ];

                /* add main language option (optional) */
            if(sizeof($oes->languages) > 1)
                $this->table_data = [
                    [
                        'type' => 'thead',
                        'rows' => [
                            [
                                'class' => 'oes-config-table-separator',
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'colspan' => '3',
                                        'value' => '<strong>' . __('General', 'oes') . '</strong>'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'rows' => [
                            [
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'value' => '<strong>' . __('Main Language', 'oes') . '</strong>'
                                    ],
                                    [
                                        'colspan' => '2',
                                        'class' => 'oes-table-transposed',
                                        'value' => oes_html_get_form_element('select',
                                            'oes_config[main_language]',
                                            'oes_config-main_language',
                                            $oes->main_language ?? 'language0',
                                            ['options' => $languageOptions])
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];


            /* add to return value */
            $this->table_data[] = [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'colspan' => '3',
                                    'value' => '<strong>' . __('Labels', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
            $this->table_data[] = [
                    'rows' => [
                        ['cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Key', 'oes') . '</strong>'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Name', 'oes') . '</strong>'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Abbreviation', 'oes') . '</strong>'
                            ]
                        ]
                        ]
                    ]
                ];
            $this->table_data[] = [
                    'rows' => $tableBody
                ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Languages', 'theme-languages');
endif;