<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Theme_Labels')) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists('Theme_Labels_Objects')) :

    /**
     * Class Theme_Labels_Objects
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Labels_Objects extends Theme_Labels
    {

        public bool $expand_button = true;


        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            foreach ($oes->post_types ?? [] as $postTypeKey => $postType) {
                $this->add_object_rows('post_types', $postTypeKey, $postType);
            }

            foreach ($oes->taxonomies ?? [] as $taxonomyKey => $taxonomy) {
                $this->add_object_rows('taxonomies', $taxonomyKey, $taxonomy);
            }
        }

        /**
         * Add rows for object.
         *
         * @param string $identifier Post types or taxonomies.
         * @param string $objectKey The post type key or the taxonomy key.
         * @param array $object The post type object or the taxonomy object.
         */
        function add_object_rows(string $identifier, string $objectKey, array $object)
        {
            $objectLabel = $object['label'] ?? $objectKey;
            $this->add_table_header($objectLabel, 'standalone');

            // general labels
            $this->add_table_header('General', 'trigger');

            $optionPrefixGeneral = $identifier . '[' . $objectKey . '][oes_args]';

            $this->add_table_row(
                [
                    'title' => __('Label (Singular)', 'oes'),
                    'key' => $optionPrefixGeneral . '[label_translations]',
                    'value' => $object['label_translations'] ?? [],
                    'is_label' => true
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Label (Plural)', 'oes'),
                    'key' => $optionPrefixGeneral . '[label_translations_plural]',
                    'value' => $object['label_translations_plural'] ?? [],
                    'is_label' => true
                ]
            );

            // field labels
            $this->add_table_header('Fields', 'trigger');

            foreach ($object['field_options'] ?? [] as $fieldKey => $field) {

                if (in_array($field['type'] ?? false, ['tab', 'message'])) {
                    continue;
                }

                $this->add_table_row(
                    [
                        'title' => ($field['label'] ?? $fieldKey),
                        'key' => 'fields[' . $objectKey . '][' . $fieldKey . ']',
                        'value' => $field,
                        'is_label' => true,
                        'label_key' => $fieldKey,
                        'option_prefix' => 'label_translation_'
                    ]
                );
            }

            // theme labels
            $this->add_table_header('Theme Labels', 'trigger');

            $themeLabels = $object['theme_labels'] ?? [];
            ksort($themeLabels);
            foreach ($themeLabels as $key => $label) {
                $this->add_table_row(
                    [
                        'title' => $label['name'] ?? $key,
                        'key' => $identifier . '[' . $objectKey . '][oes_args][theme_labels][' . $key . ']',
                        'value' => $label,
                        'is_label' => true,
                        'label_key' => $key,
                        'location' => $label['location'] ?? ''
                    ]
                );
            }

            $this->end_nested_table();
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels_Objects', 'theme-labels-objects');
endif;