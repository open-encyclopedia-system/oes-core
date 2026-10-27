<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Theme_Media', false)) {

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
            $fields = array_merge([
                'title' => ['key' => 'title', 'label' => 'Title'],
                'alt' => ['key' => 'alt', 'label' => 'Alternative Text'],
                'caption' => ['key' => 'caption', 'label' => 'Caption'],
                'description' => ['key' => 'description', 'label' => 'Description'],
                'date' => ['key' => 'date', 'label' => 'Publication Date']
            ], oes_get_media_param('fields'));

            $fieldOptions = [];
            foreach ($fields as $key => $field) {
                $fieldKey = $field['key'] ?? $key;
                $fieldOptions[$fieldKey] = $field['label'] ?? $key;
            }

            $this->add_table_row(
                [
                    'title' => __('Title Field', 'oes'),
                    'key' => 'media[title]',
                    'value' => oes_get_media_param('title', 'title'),
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
                    'value' => oes_get_media_param('credit_text', '')
                ]
            );

            $this->add_table_row(
                [
                    'title' => __('Credit Field', 'oes'),
                    'key' => 'media[credit_label]',
                    'value' => oes_get_media_param('credit_label', 'none'),
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
                    'value' => oes_get_media_param('show_in_panel', []),
                    'type' => 'select',
                    'args' => [
                        'options' => $fieldOptions,
                        'multiple' => true,
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Theme_Media', 'theme-media');

}
