<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin_Columns')) :

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
            global $oes;

            // Post types
            $this->add_table_header(__('Post Types', 'oes'));
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {
                $options = $this->build_column_options($postTypeData['field_options'] ?? [], [
                    'cb'            => 'Checkbox',
                    'title'         => 'Title',
                    'date'          => 'Date',
                    'date_modified' => 'Modified Date',
                    'parent'        => 'Parent',
                ]);

                foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomyKey) {
                    if (isset($oes->taxonomies[$taxonomyKey]['label'])) {
                        $options['taxonomy-' . $taxonomyKey] = $oes->taxonomies[$taxonomyKey]['label'];
                    }
                }

                $this->add_admin_column_row('post_types', $postTypeKey, $postTypeData['label'] ?? $postTypeKey, $postTypeData['admin_columns'] ?? [], $options);
            }

            // Taxonomies
            $this->add_table_header(__('Taxonomies', 'oes'));
            foreach ($oes->taxonomies ?? [] as $taxonomyKey => $taxonomyData) {
                $options = $this->build_column_options($taxonomyData['field_options'] ?? [], [
                    'cb'          => 'Checkbox',
                    'name'        => 'Name',
                    'slug'        => 'Slug',
                    'description' => 'Description',
                    'posts'       => 'Count',
                    'id'          => 'ID',
                ]);

                $this->add_admin_column_row('taxonomies', $taxonomyKey, $taxonomyData['label'] ?? $taxonomyKey, $taxonomyData['admin_columns'] ?? [], $options);
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
                    'title' => $label,
                    'key'   => "{$type}[{$key}][oes_args][admin_columns]",
                    'value' => $value,
                    'type'  => 'select',
                    'args'  => [
                        'options'  => $options,
                        'multiple' => true,
                        'class'    => 'oes-replace-select2',
                        'reorder'  => true,
                        'hidden'   => true,
                    ],
                ],
                [
                    'subtitle' => '<code>' . $key . '</code>',
                ]
            );
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Columns', 'admin-columns');

endif;