<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Theme_Index_Pages', false)) {

    /**
     * Class Theme_Index_Pages
     *
     * Implement the config tool for theme configurations: index pages.
     */
    class Theme_Index_Pages extends Config
    {

        /** @inheritdoc */
        public bool $empty_allowed = true;

        /** @inheritdoc */
        public bool $empty_input = true;

        /** @inheritdoc */
        function additional_html(): string
        {
            return '<div>' .
                '<input type="submit" name="add_new_index" id="add_new_index" ' .
                'class="button button-secondary button-large" ' .
                'value="' . __('Add New Index', 'oes') . '">' .
                '</div>';
        }

        /** @inheritdoc */
        function set_table_data_for_display(): void
        {
            $indexPages = oes_get_index_pages();
            if (empty($indexPages)) {
                return;
            }

            $modifiedIndexPageKey = 0;
            $tableID = 1;
            foreach ($indexPages as $indexPageKey => $indexPage) {

                // make sure that index page key is string
                if (!is_string($indexPageKey)) {
                    while (isset($indexPages['index' . $modifiedIndexPageKey])) ++$modifiedIndexPageKey;
                    $indexPageKey = 'index' . $modifiedIndexPageKey;
                    ++$modifiedIndexPageKey;
                }

                $elementOptions = $objectOptions = [];
                foreach (oes_get_post_types() as $key => $postType) {
                    $elementOptions[$key] = $postType['label'];
                    $objectOptions[$key] = $postType['label'];
                }
                foreach (oes_get_taxonomies() as $key => $taxonomy) {
                    $objectOptions[$key] = $taxonomy['label'];
                }

                $indexLabel = $indexPage['label']['language0'] ?? $indexPageKey;
                if(empty($indexLabel)){
                    $indexLabel = __('[Index without label]', 'oes');
                }

                $deleteLink = '<a href="javascript:void(0);" ' .
                    'onClick="oesRemoveElementById(\'oes-form-table-' . $tableID++ . '\')" ' .
                    'class="oes-highlighted button">' .
                    __('Delete This Index', 'oes') .
                    '</a>';

                $this->add_table_header($indexLabel, 'tag', ['p' => $deleteLink]);

                $optionPrefix = 'oes_config[theme_index_pages][' . $indexPageKey . ']';

                $this->add_table_row(
                    [
                        'title' => __('Page Slug', 'oes'),
                        'key' => $optionPrefix . '[slug]',
                        'value' => $indexPage['slug'] ?? 'register'
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Considered Object', 'oes'),
                        'key' => $optionPrefix . '[element]',
                        'value' => $indexPage['element'] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $elementOptions,
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Objects', 'oes'),
                        'key' => $optionPrefix . '[objects]',
                        'value' => $indexPage['objects'] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $objectOptions,
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Archive Filter', 'oes'),
                        'key' => $optionPrefix . '[archive_filter]',
                        'value' => $indexPage['archive_filter'] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => ['alphabet' => 'Alphabet'],
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );

                foreach (oes_get_languages() as $languageKey => $languageData) {
                    $this->add_table_row(
                        [
                            'title' => __('Label', 'oes') . ' (' . ($languageData['label'] ?? $languageKey) . ')',
                            'key' => $optionPrefix . '[label][' . $languageKey . ']',
                            'value' => $indexPage['label'][$languageKey] ?? ''
                        ]
                    );
                }
            }
        }

        /** @inheritdoc */
        function get_modified_post_data(array $data): array
        {
            if (isset($data['add_new_index'])) {
                $data['oes_config']['theme_index_pages'][] = [
                    'slug' => 'hidden',
                    'element' => [],
                    'objects' => [],
                    'archive_filter' => [],
                    'label' => []
                ];
            } elseif ($data['oes_hidden'] && (empty($data['oes_config']['theme_index_pages']))) {
                $data['oes_config']['theme_index_pages'] = [];
            }
            return $data;
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Theme_Index_Pages', 'theme-index-pages');
}


