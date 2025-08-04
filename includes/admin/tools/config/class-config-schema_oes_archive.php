<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('Schema_OES_Archive')) :

    /**
     * Class Schema_OES_Archive
     *
     * Implement the config tool for admin configurations.
     */
    class Schema_OES_Archive extends Schema
    {

        /** @inheritdoc */
        function set_table_data_for_display(): void
        {
            if ($this->component == 'post_types') {
                $this->set_post_type();
            }
            elseif ($this->component == 'taxonomies') {
                $this->set_taxonomies();
            }
        }

        /**
         * Set post type options.
         * 
         * @return void
         */
        function set_post_type(): void
        {
            global $oes;
            $postTypeData = $oes->post_types[$this->object] ?? [];

            // get options
            $selects = oes_get_object_select_options($this->object);
            $titleOptions = $selects['title'] ?? [];
            $options = $selects['all'] ?? [];

            $keyPrefix = 'post_types[' . $this->object . '][oes_args]';

            $this->add_table_row(
                [
                    'title' => __('Title for list display', 'oes'),
                    'key' => $keyPrefix . '[display_titles][title_archive_display]',
                    'value' => $postTypeData['display_titles']['title_archive_display'] ?? 'wp-title',
                    'type' => 'select',
                    'args' => [
                        'options' => $titleOptions
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Sorting title for list display', 'oes'),
                    'key' => $keyPrefix . '[display_titles][title_sorting_display]',
                    'value' => $postTypeData['display_titles']['title_sorting_display'] ?? 'wp-title',
                    'type' => 'select',
                    'args' => [
                        'options' => $titleOptions
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Display archive as single page list', 'oes'),
                    'key' => $keyPrefix . '[archive_on_single_page]',
                    'value' => $postTypeData['archive_on_single_page'] ?? false,
                    'type' => 'checkbox',
                    'args' => [
                        'hidden' => true
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Archive Data', 'oes'),
                    'key' => $keyPrefix . '[archive]',
                    'value' => $postTypeData['archive'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => $options,
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Archive Filter', 'oes'),
                    'key' => $keyPrefix . '[archive_filter]',
                    'value' => $postTypeData['archive_filter'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => array_merge(['alphabet' => 'Alphabet'], $options),
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );
        }

        /**
         * Set taxonomy options.
         * 
         * @return void
         */
        function set_taxonomies(): void
        {
            global $oes;
            $taxonomyData = $oes->taxonomies[$this->object] ?? [];

            $selects = oes_get_object_select_options($this->object, false, ['title' => true]);
            $titleOptions = $selects['title'] ?? [];

            $keyPrefix = 'taxonomies[' . $this->object . '][oes_args]';

            $this->add_table_row(
                [
                    'title' => __('Title for list display', 'oes'),
                    'key' => $keyPrefix . '[display_titles][title_archive_display]',
                    'value' => $taxonomyData['display_titles']['title_archive_display'] ?? 'wp-title',
                    'type' => 'select',
                    'args' => [
                        'options' => $titleOptions
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Sorting title for list display', 'oes'),
                    'key' => $keyPrefix . '[display_titles][title_sorting_display]',
                    'value' => $taxonomyData['display_titles']['title_sorting_display'] ?? 'wp-title',
                    'type' => 'select',
                    'args' => [
                        'options' => $titleOptions
                    ]
                ]
            );

            //@oesDevelopment Add more filter options.
            $this->add_table_row(
                [
                    'title' => __('Archive Filter', 'oes'),
                    'key' => $keyPrefix . '[archive_filter]',
                    'value' => $taxonomyData['archive_filter'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => ['alphabet' => 'Alphabet'],
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema_OES_Archive', 'schema-oes_archive');

endif;