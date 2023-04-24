<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Notes')) :

    /**
     * Class Theme_Notes
     *
     * Implement the config tool for theme configurations: notes.
     */
    class Theme_Notes extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Notes</b> (or endnotes) enables the display of the notes header in the table of contents ' .
                    'inside the single view of a post object.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            /* get global OES instance */
            $oes = OES();
            if (!empty($oes->notes))
                $this->table_data[] = [
                    'class' => 'oes-toggle-checkbox',
                    'rows' => [
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Exclude from Table of Contents', 'oes') .
                                        '</strong><div>' . __('Do not include the heading in the table of contents.', 'oes') . '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_config[notes][exclude_from_toc]',
                                        'oes_config-notes-exclude_from_toc',
                                        (isset($oes->notes['exclude_from_toc']) && $oes->notes['exclude_from_toc'] == 'on'),
                                        ['hidden' => true])
                                ]
                            ]
                        ],
                        [
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'value' => '<strong>' . __('Add Number to Heading', 'oes') .
                                        '</strong><div>' . __('Add number in front of heading (automatically incremented).', 'oes') .
                                        '</div>'
                                ],
                                [
                                    'class' => 'oes-table-transposed',
                                    'value' => oes_html_get_form_element('checkbox',
                                        'oes_config[notes][add_number]',
                                        'oes_config-notes-add_number',
                                        (isset($oes->notes['add_number']) && $oes->notes['add_number'] == 'on'),
                                        ['hidden' => true])
                                ]
                            ]
                        ]
                    ]
                ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Notes', 'theme-notes');
endif;