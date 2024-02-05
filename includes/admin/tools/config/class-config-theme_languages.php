<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

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
                    '(e.g. language switch for articles). ' .
                    'The <strong>abbreviation</strong> is used for e.g. the language switch. ' .
                    'Choose a <strong>date locale</strong> for the date display. Default is "en_BE" for british ' .
                    'date format. Most used locale codes are: "de_DE" (German), "en_BE" (British), ' .
                    '"en_US" (US) and "fr_FR" (French). You can look up further locale codes online, but ' .
                    'unfortunately, there is no list for all php language locale codes provided by an ' .
                    'official source.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            /* prepare header */
            $tableBody[] = [
                'cells' => [
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
                    ],
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Date Locale', 'oes') . '</strong>'
                    ]
                ]
            ];

            /* loop through languages */
            foreach (OES()->languages ?? [] as $languageKey => $language)
                $tableBody[] = [
                    'cells' => [
                        ['value' => '<code>' . $languageKey .
                            ($languageKey === 'language0' ?
                                __(' (Primary Language)', 'oes') :
                                '') .
                            '</code>'],
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
                            )],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                'oes_config[languages][' . $languageKey . '][locale]',
                                'oes_config-languages-' . $languageKey . '-locale',
                                $language['locale'] ?? ''
                            )]
                    ]
                ];

            /* add to table */
            $this->table_data[] = [
                'rows' => $tableBody
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Languages', 'theme-languages');
endif;