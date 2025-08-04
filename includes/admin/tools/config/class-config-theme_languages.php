<?php

/**
 * @file
 * @reviewed 2.4.0
 */

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

        /** @inheritdoc */
        function set_table_data_for_display()
        {

            $headers = [
                __('Key', 'oes'),
                __('Name', 'oes'),
                __('Abbreviation', 'oes'),
                __('Date Locale', 'oes')
            ];

            $this->add_table_header('', 'default', $headers);

            global $oes;
            foreach ($oes->languages ?? [] as $languageKey => $language){

                $row = [
                    'cells' => [
                        [
                            'value' => '<code>' . $languageKey .
                                ($languageKey === 'language0' ?
                                    __(' (Primary Language)', 'oes') :
                                    '') .
                                '</code>'
                        ],
                        [
                            'input' => [
                                'name' => 'oes_config[languages][' . $languageKey . '][label]',
                                'value' => $language['label'] ?? ''
                            ]
                        ],
                        [
                            'input' => [
                                'name' => 'oes_config[languages][' . $languageKey . '][abb]',
                                'value' => $language['abb'] ?? ''
                            ]
                        ],
                        [
                            'input' => [
                                'name' => 'oes_config[languages][' . $languageKey . '][locale]',
                                'value' => $language['locale'] ?? ''
                            ]
                        ]
                    ]
                ];

                $this->table->add_row($row);
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Languages', 'theme-languages');
endif;