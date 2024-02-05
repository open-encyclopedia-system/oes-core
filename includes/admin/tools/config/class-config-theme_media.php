<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Media')) :

    /**
     * Class Theme_Media
     *
     * Implement the config tool for theme configurations: media.
     */
    class Theme_Media extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Media</b> allows you to define the display behaviour of images in the ' .
                    'frontend. This applies only to media displayed in OES blocks (e.g. "Featured Image").', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();

            /* prepare image fields options */
            $fields = array_merge([
                'title' => ['key' => 'title', 'label' => 'Title'],
                'alt' => ['key' => 'alt', 'label' => 'Alternative Text'],
                'caption' => ['key' => 'caption', 'label' => 'Caption'],
                'description' => ['key' => 'description', 'label' => 'Description'],
                'date' => ['key' => 'date', 'label' => 'Publication Date']
            ], $oes->media_groups['fields'] ?? []);

            $fieldOptions = [];
            foreach ($fields as $key => $field) {
                $fieldKey = $field['key'] ?? $key;
                $fieldOptions[$fieldKey] = $field['label'] ?? $key;
            }


            $this->table_data = [[
                'rows' => [
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Credit Label', 'oes') . '</strong>' .
                                    '<div>' . __('The credit label is displayed beneath the image', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                    'media[credit_text]',
                                    'media-credit_text',
                                    $oes->media_groups['credit_text'] ?? ''
                                )
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Credit field', 'oes') . '</strong>' .
                                    '<div>' . __('The credit field and is displayed after the credit label', 'oes') .
                                    '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'media[credit_label]',
                                    'media-credit_label',
                                    $oes->media_groups['credit_label'] ?? 'none',
                                    ['options' => array_merge(['none' => '-'], $fieldOptions)])
                            ]
                        ]
                    ],
                    [
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Show in Panel', 'oes') .
                                    '</strong><div>' . __('Fields that are shown in panel', 'oes') . '</div>'
                            ],
                            [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('select',
                                    'media[show_in_panel]',
                                    'media-show_in_panel',
                                    $oes->media_groups['show_in_panel'] ?? [],
                                    [
                                        'options' => $fieldOptions,
                                        'multiple' => true,
                                        'class' => 'oes-replace-select2',
                                        'reorder' => true,
                                        'hidden' => true
                                    ])
                            ]
                        ]
                    ]
                ]
            ]];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Media', 'theme-media');
endif;