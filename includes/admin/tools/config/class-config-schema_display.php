<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');
if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (class_exists('Schema_Display')) exit;

/**
 * Class Schema_Display
 *
 * Implement the config tool for admin configurations.
 */
class Schema_Display extends Schema
{

    /** @inheritdoc */
    function set_table_data_for_display(): void
    {
        if ($this->component == 'post_types') {
            $this->set_post_type();
        } elseif ($this->component == 'taxonomies') {
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

        $this->add_table_header(__('Single Options', 'oes'));

        $this->render_select_row(
            __('Metadata', 'oes'),
            $keyPrefix . '[metadata]',
            $postTypeData['metadata'] ?? [],
            $options,
            true);

        $this->add_table_header(__('Archive Options', 'oes'));

        $this->render_select_row(
            __('Title for list display', 'oes'),
            $keyPrefix . '[display_titles][title_archive_display]',
            $postTypeData['display_titles']['title_archive_display'] ?? 'wp-title',
            $titleOptions
        );

        $this->render_select_row(
            __('Sorting title for list display', 'oes'),
            $keyPrefix . '[display_titles][title_sorting_display]',
            $postTypeData['display_titles']['title_sorting_display'] ?? 'wp-title',
            $titleOptions
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

        $this->render_select_row(
            __('Archive Data', 'oes'),
            $keyPrefix . '[archive]',
            $postTypeData['archive'] ?? [],
            $options,
            true
        );

        $this->render_select_row(
            __('Archive Filter', 'oes'),
            $keyPrefix . '[archive_filter]',
            $postTypeData['archive_filter'] ?? [],
            array_merge(['alphabet' => 'Alphabet'], $options),
            true
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

        $this->render_select_row(
            __('Title for list display', 'oes'),
            $keyPrefix . '[display_titles][title_archive_display]',
            $taxonomyData['display_titles']['title_archive_display'] ?? 'wp-title',
            $titleOptions
        );

        $this->render_select_row(
            __('Sorting title for list display', 'oes'),
            $keyPrefix . '[display_titles][title_sorting_display]',
            $taxonomyData['display_titles']['title_sorting_display'] ?? 'wp-title',
            $titleOptions
        );

        //@oesDevelopment Add more filter options.
        $this->render_select_row(
            __('Archive Filter', 'oes'),
            $keyPrefix . '[archive_filter]',
            $taxonomyData['archive_filter'] ?? [],
            ['alphabet' => 'Alphabet'],
            true
        );
    }
}

// initialize
register_tool('\OES\Admin\Tools\Schema_Display', 'schema-display');
