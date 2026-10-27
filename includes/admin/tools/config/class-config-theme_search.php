<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Theme_Search', false)) {

    /**
     * Class Theme_Search
     *
     * Implement the config tool for theme configurations: search.
     */
    class Theme_Search extends Config
    {
        /** @inheritdoc */
        function set_table_data_for_display()
        {
            $optionPrefix = 'oes_config[search]';

            $this->add_table_row(
                [
                    'title' => __('Maximum paragraphs in search result', 'oes'),
                    'key' => $optionPrefix . '[max_preview_paragraphs]',
                    'value' => oes_get_search_param('max_preview_paragraphs', 1),
                    'type' => 'number',
                    'args' => [
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            );

            $orderSelects = [];
            $postTypes = oes_get_post_types();
            $taxonomies = oes_get_taxonomies();

            foreach ($postTypes as $postTypeKey => $postTypeData) {
                $orderSelects[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
            }
            foreach ($taxonomies as $taxonomyKey => $taxonomyData) {
                $orderSelects[$taxonomyKey] = $taxonomyData['label'] ?? $taxonomyKey;
            }

            $this->add_table_row(
                [
                    'title' => __('Order of searched objects', 'oes'),
                    'key' => $optionPrefix . '[order]',
                    'value' => oes_get_search_param('order', []),
                    'type' => 'select',
                    'args' => [
                        'options' => $orderSelects,
                        'multiple' => true,
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            // @oesDevelopment shouldn't that be a label...?
            foreach (oes_get_languages() as $languageKey => $languageData) {
                $this->add_table_row(
                    [
                        'title' => __('Type Filter Label', 'oes') . ' (' . ($languageData['label'] ?? $languageKey) . ')',
                        'key' => $optionPrefix . '[type_label][' . $languageKey . ']',
                        'value' => oes_get_search_param('order')[$languageKey] ?? ''
                    ]
                );
            }

            $this->add_table_header(__('Search In', 'oes'), 'tag');

            $this->add_table_row(
                [
                    'title' => __('Page', 'oes'),
                    'key' => $optionPrefix . '[postmeta_fields][page]',
                    'value' => oes_get_search_param('postmeta_fields')['page'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'],
                        'multiple' => true,
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            foreach ($postTypes as $postTypeKey => $postTypeData) {

                $options = ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'];
                $optionsFields = oes_get_object_select_options($postTypeKey);
                $optionsAll = array_merge($options, $optionsFields['fields'] ?? []);

                $this->add_table_row(
                    [
                        'title' => ($postTypeData['label'] ?? $postTypeKey) .
                            '<br><code class="oes-object-identifier">' . $postTypeKey . '</code>',
                        'key' => $optionPrefix . '[postmeta_fields][' . $postTypeKey . ']',
                        'value' => oes_get_search_param('postmeta_fields')[$postTypeKey] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $optionsAll,
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );
            }

            foreach ($taxonomies as $taxonomyKey => $taxonomyData) {

                //@oesDevelopment Add one field option for alternative names.
                $options = ['name' => 'Title (Name)', 'slug' => 'Slug'];

                $this->add_table_row(
                    [
                        'title' => ($taxonomyData['label'] ?? $taxonomyKey) .
                            '<code class="oes-object-identifier">' . $taxonomyKey . '</code>',
                        'key' => $optionPrefix . '[postmeta_fields][' . $taxonomyKey . ']',
                        'value' => oes_get_search_param('postmeta_fields')[$taxonomyKey] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $options,
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );
            }
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Theme_Search', 'theme-search');

}
