<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Schema', false)) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('LOD_Schema', false)) {

    /**
     * Class LOD_Schema
     *
     * Implement the config tool for LOD options configurations.
     */
    class LOD_Schema extends Schema
    {

        /** @var string The api key. */
        public string $api_key = 'lod';

        /** @inheritdoc */
        protected function additional_parameters(array $args = []): void
        {
            $this->api_key = $args['api_key'] ?? 'lod';
        }

        /** @inheritdoc */
        function empty(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('There are no configurations found for this API for your application. ' .
                    'Enable API options in the <b>General</b> tab.', 'oes') .
                '</p></div>';
        }

        /** @inheritdoc */
        function additional_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('Define the field mapping that determines which entry data will be imported to which post ' .
                    'object field.', 'oes') .
                '</p></div>';
        }

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            if (!isset($oes->apis[$this->api_key])) {
                return;
            }

            if (empty($oes->apis[$this->api_key]->config_options['properties']['options'] ?? [])) {
                return;
            }

            $postTypeData = $oes->post_types[$this->object] ?? [];

            if (!isset($postTypeData['lod'])) {
                return;
            }

            $option = $oes->apis[$this->api_key]->config_options['properties'];

            foreach ($postTypeData['field_options'] as $fieldKey => $field) {

                $type = oes_get_field_object($fieldKey)['type'] ?? 'tab';
                if (in_array($type, ['tab', 'message', 'relationship', 'post', 'image', 'date_picker'])) {
                    continue;
                }

                $this->add_table_row(
                    [
                        'title' => $field['label'] ?? $fieldKey,
                        'key' => 'fields[' . $this->object . '][' . $fieldKey . '][' . $this->api_key . '_properties]',
                        'value' => $field[$this->api_key . '_properties'] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $option['options'],
                            'multiple' => $option['multiple'] ?? true,
                            'hidden' => ($option['type'] === 'select')
                        ]
                    ],
                    [
                        'subtitle' => $fieldKey
                    ]
                );
            }
        }
    }
}