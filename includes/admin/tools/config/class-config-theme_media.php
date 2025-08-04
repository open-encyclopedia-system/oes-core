<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Media')) :

    /**
     * Class Theme_Media
     *
     * Implement the config tool for theme configurations: media.
     */
    class Theme_Media extends Config
    {
        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            $fields = array_merge([
                'title' => ['key' => 'title', 'label' => 'Title'],
                'alt' => ['key' => 'alt', 'label' => 'Alternative Text'],
                'caption' => ['key' => 'caption', 'label' => 'Caption'],
                'description' => ['key' => 'description', 'label' => 'Description'],
                'date' => ['key' => 'date', 'label' => 'Publication Date']
            ], $oes->media_groups['fields'] ?? []);

            $fieldOptions = [];
            foreach ($fields as $key => $field) {
                $fieldKey = $field['key'] ?? $key;
                $fieldOptions[$fieldKey] = $field['label'] ?? $key;
            }

            $this->add_table_row(
                [
                    'title' => __('Title Field', 'oes'),
                    'key' => 'media[title]',
                    'value' => $oes->media_groups['title'] ?? 'title',
                    'type' => 'select',
                    'args' => [
                        'options' => $fieldOptions
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Credit Label', 'oes'),
                    'key' => 'media[credit_text]',
                    'value' => $oes->media_groups['credit_text'] ?? ''
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Credit Field', 'oes'),
                    'key' => 'media[credit_label]',
                    'value' => $oes->media_groups['credit_label'] ?? 'none',
                    'type' => 'select',
                    'args' => [
                        'options' => array_merge(['none' => '-'], $fieldOptions)
                    ]
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Show in Panel', 'oes'),
                    'key' => 'media[show_in_panel]',
                    'value' => $oes->media_groups['show_in_panel'] ?? [],
                    'type' => 'select',
                    'args' => [
                        'options' => $fieldOptions,
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
    register_tool('\OES\Admin\Tools\Theme_Media', 'theme-media');
endif;