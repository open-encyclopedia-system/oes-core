<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Admin_Columns', false)) :

    /**
     * Class Admin Columns
     *
     * Implement the config tool for admin columns configurations.
     */
    class Admin_Columns extends Config
    {

        /** @inheritdoc */
        public function set_table_data_for_display(): void
        {
            foreach (oes_get_post_types() as $postTypeKey => $postTypeData) {
                $options = $this->build_column_options($postTypeData['field_options'] ?? [], [
                    'cb' => 'Checkbox',
                    'title' => 'Title',
                    'date' => 'Date',
                    'date_modified' => 'Modified Date',
                    'parent' => 'Parent',
                ]);

                foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomyKey) {
                    $options['taxonomy-' . $taxonomyKey] = oes_get_taxonomy_param($taxonomyKey, 'label');
                }

                $this->add_admin_column_row(
                    'post_types',
                    $postTypeKey,
                    $postTypeData['label'] ?? $postTypeKey,
                    $postTypeData['admin_columns'] ?? [],
                    $options);
            }

            foreach (oes_get_taxonomies() as $taxonomyKey => $taxonomyData) {
                $options = $this->build_column_options($taxonomyData['field_options'] ?? [], [
                    'cb' => 'Checkbox',
                    'name' => 'Name',
                    'slug' => 'Slug',
                    'description' => 'Description',
                    'posts' => 'Count',
                    'id' => 'ID',
                ]);

                $this->add_admin_column_row(
                    'taxonomies',
                    $taxonomyKey,
                    $taxonomyData['label'] ?? $taxonomyKey,
                    $taxonomyData['admin_columns'] ?? [],
                    $options);
            }
        }

        /**
         * Build a list of admin column options.
         *
         * @param array $fieldOptions ACF-style field options.
         * @param array $defaults Default core columns.
         * @return array Merged options.
         */
        protected function build_column_options(array $fieldOptions, array $defaults): array
        {
            $options = $defaults;

            foreach ($fieldOptions as $fieldKey => $field) {
                if (!in_array($field['type'] ?? '', ['tab', 'message'], true)) {
                    $options[$fieldKey] = __('Field: ', 'oes') . ($field['label'] ?? $fieldKey);
                }
            }

            return $options;
        }

        /**
         * Add a table row for admin column configuration.
         *
         * @param string $type Either 'post_types' or 'taxonomies'.
         * @param string $key The post type or taxonomy key.
         * @param string $label The row label.
         * @param array $value Current saved column configuration.
         * @param array $options Available column options.
         * @return void
         */
        protected function add_admin_column_row(string $type, string $key, string $label, array $value, array $options): void
        {
            $this->add_table_row(
                [
                    'title' => $label . '<p class="description"><code>' . $key . '</code></p>',
                    'key' => "{$type}[{$key}][oes_args][admin_columns]",
                    'value' => $value,
                    'type' => 'select',
                    'args' => [
                        'options' => $options,
                        'multiple' => true,
                        'reorder' => true,
                        'hidden' => true,
                    ],
                ]
            );
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Admin_Columns', 'admin-columns');

endif;
