<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Navigation')) :

    /**
     * Class Theme_Navigation
     *
     * Implement the config tool for theme configurations: navigation.
     */
    class Theme_Navigation extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The WordPress feature <b>Menus</b> allows you to define menus for specific location on your ' .
                    'frontend layer. If you are using the OES theme or a derivation of it you can add ' .
                    'special menu items to the menu location <b>OES Top</b>.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();
            $this->table_data = [
                [
                    'class' => 'oes-toggle-checkbox',
                    'rows' => [
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Enable Search Button', 'oes') .
                                        '</strong><div>' . __('include a search button in the top menu. ' .
                                            'The switch works depending on your data and theme.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_config[theme_options][search_button]',
                                        'oes_config-theme_options-search_button',
                                        (isset($oes->theme_options['search_button']) && $oes->theme_options['search_button'] === 'on'),
                                        ['hidden' => true]
                                    )
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Enable Language Switch', 'oes') .
                                        '</strong><div>' . __('include a language switch in the top menu. ' .
                                            'The switch works depending on your data and theme.', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_config[theme_options][language_switch]',
                                        'oes_config-theme_options-language_switch',
                                        (isset($oes->theme_options['language_switch']) && $oes->theme_options['language_switch'] === 'on'),
                                        ['hidden' => true]
                                    )
                                ]
                            ]
                        ]
                    ]
                ]];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Navigation', 'theme-navigation');
endif;