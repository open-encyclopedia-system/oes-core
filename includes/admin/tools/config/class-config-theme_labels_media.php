<?php

/**
 * @file
 * @reviewed 2.4.0
 */

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

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            $languages = array_keys($oes->languages);
            $this->set_language_row();

            foreach ($oes->media_groups['image'] ?? [] as $fieldKey => $field) {

                $label = $field['label'] ?? ([
                            'title' => 'Title',
                            'alt' => 'Alternative Text',
                            'caption' => 'Caption',
                            'description' => 'Description',
                            'date' => 'Publication Date'
                        ][$fieldKey] ?? $fieldKey);

                $this->add_table_row(
                    [
                        'title' => $label,
                        'key' => 'media[image][' . $fieldKey . ']',
                        'value' => $field ?? [],
                        'is_label' => true,
                        'label_key' => $fieldKey
                    ]
                );
            }

            foreach ($oes->media_groups['fields'] ?? [] as $fieldKey => $mediaField) {

                $values = [];
                foreach($languages as $languageKey => $ignore){
                    $values[$languageKey] = $mediaField['label_translation_' . $languageKey] ?? '';
                }

                $this->add_table_row(
                    [
                        'title' => ($mediaField['label'] ?? ''),
                        'key' => 'media[acf_add_local_field_group][fields][' . $fieldKey . ']',
                        'value' => $values,
                        'is_label' => true,
                        'label_key' => $fieldKey
                    ]
                );
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels_Media', 'theme-labels-media');
endif;