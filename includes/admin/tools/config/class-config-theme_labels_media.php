<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Theme_Labels', false)) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists(__NAMESPACE__ . '\Theme_Labels_Media', false)) {

    /**
     * Class Theme_Labels_Media
     *
     * Implement the config tool for media theme configurations.
     */
    class Theme_Labels_Media extends Theme_Labels
    {

        /** @inheritdoc */
        protected function set_table_data_for_display(): void
        {
            $languages = array_keys(oes_get_languages());
            $this->set_language_row();

            foreach (oes_get_media_param('image', []) as $fieldKey => $field) {

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

            foreach (oes_get_media_param('fields', []) as $fieldKey => $mediaField) {
                $this->add_table_row(
                    [
                        'title' => ($mediaField['label'] ?? ''),
                        'key' => 'media_fields[' . $fieldKey . ']',
                        'value' => $mediaField,
                        'is_label' => true,
                        'label_key' => $fieldKey,
                        'option_prefix' => 'label_translation_'
                    ]
                );
            }
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Theme_Labels_Media', 'theme-labels-media');
}
