<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Search')) :

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
            global $oes;
            $optionPrefix = 'oes_config[search]';

            $this->add_table_header(__('General', 'oes'));

            $this->add_table_row(
                [
                    'title' => __('Maximum paragraphs in search result', 'oes'),
                    'key' => $optionPrefix . '[max_preview_paragraphs]',
                    'value' => $oes->search['max_preview_paragraphs'] ?? 1,
                    'type' => 'number',
                    'args' => [
                        'min' => 0,
                        'max' => 100
                    ]
                ]
            );

            $orderSelects = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {
                $orderSelects[$postTypeKey] = $postTypeData['label'] ?? $postTypeKey;
            }
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData) {
                $orderSelects[$taxonomyKey] = $taxonomyData['label'] ?? $taxonomyKey;
            }

            $this->add_table_row(
                [
                    'title' => __('Order of searched objects', 'oes'),
                    'key' => $optionPrefix . '[order]',
                    'value' => $oes->search['order'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => $orderSelects,
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            // @oesDevelopment shouldn't that be a label...?
            foreach($oes->languages as $languageKey => $languageData){
                $this->add_table_row(
                    [
                        'title' => __('Type Filter Label', 'oes') . ' (' . ($languageData['label'] ?? $languageKey) . ')',
                        'key' => $optionPrefix . '[type_label][' . $languageKey . ']',
                        'value' => $oes->search['order'][$languageKey] ?? ''
                    ]
                );
            }

            $this->add_table_header(__('Search In', 'oes'));

            $this->add_table_header(__('Post Types', 'oes'), 'inner');

            $this->add_table_row(
                [
                    'title' => __('Page', 'oes'),
                    'key' => $optionPrefix . '[postmeta_fields][page]',
                    'value' => $oes->search['postmeta_fields']['page'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'],
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                $options = ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'];
                $optionsFields = oes_get_object_select_options($postTypeKey);
                $optionsAll = array_merge($options, $optionsFields['fields'] ?? []);

                $this->add_table_row(
                    [
                        'title' => ($postTypeData['label'] ?? $postTypeKey) .
                            '<code class="oes-object-identifier">' . $postTypeKey . '</code>',
                        'key' => $optionPrefix . '[postmeta_fields][' . $postTypeKey . ']',
                        'value' => $oes->search['postmeta_fields'][$postTypeKey] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $optionsAll,
                            'multiple' => true,
                            'class' => 'oes-replace-select2',
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );
            }

            $this->add_table_header(__('Taxonomies', 'oes'), 'inner');

            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData) {

                //@oesDevelopment Add one field option for alternative names.
                $options = ['name' => 'Title (Name)', 'slug' => 'Slug'];

                $this->add_table_row(
                    [
                        'title' => ($taxonomyData['label'] ?? $taxonomyKey) .
                            '<code class="oes-object-identifier">' . $taxonomyKey . '</code>',
                        'key' => $optionPrefix . '[postmeta_fields][' . $taxonomyKey . ']',
                        'value' => $oes->search['postmeta_fields'][$taxonomyKey] ?? [],
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
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Search', 'theme-search');
endif;