<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Theme_Labels')) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists('Theme_Labels_Media')) :

    /**
     * Class Theme_Labels_Media
     *
     * Implement the config tool for media theme configurations.
     */
    class Theme_Labels_Media extends Theme_Labels
    {


        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();
            $languages = array_keys($oes->languages);
            $this->set_language_row();

            /* get acf group image fields */
            $mediaRows = [];
            foreach ($oes->media_groups['image'] ?? [] as $fieldKey => $field) {

                $label = $field['label'] ?? ([
                            'title' => 'Title',
                            'alt' => 'Alternative Text',
                            'caption' => 'Caption',
                            'description' => 'Description',
                            'date' => 'Publication Date'
                        ][$fieldKey] ?? $fieldKey);
                $cells = [[
                    'type' => 'th',
                    'value' => '<strong>' . $label . '</strong>' .
                        '<code class="oes-object-identifier">' . $fieldKey . '</code>'
                ]];

                foreach ($languages as $language)
                    $cells[] = [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('text',
                            'media[image][' . $fieldKey . '][' . $language . ']',
                            'media-image-' . $fieldKey . '-' . $language,
                            $field[$language] ?? ''
                        )
                    ];

                $mediaRows[] = [
                    'cells' => $cells
                ];
            }

            /* add acf fields */
            foreach ($oes->media_groups['fields'] ?? [] as $fieldKey => $mediaField) {

                $cells = [[
                    'type' => 'th',
                    'value' => '<strong>' . ($mediaField['label'] ?? '') . '</strong>' .
                        '<code class="oes-object-identifier">' . $fieldKey . '</code>'
                ]];

                foreach ($languages as $language)
                    $cells[] = [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('text',
                            'media[acf_add_local_field_group][fields][' . $fieldKey . '][' . $language . ']',
                            'media-acf_add_local_field_group-fields-' . $fieldKey . '-' . $language,
                            $mediaField['label_translation_' . $language] ?? ''
                        )
                    ];

                $mediaRows[] = [
                    'cells' => $cells
                ];
            }

            $this->table_data[]['rows'] = array_merge($this->language_row, $mediaRows);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels_Media', 'theme-labels-media');
endif;