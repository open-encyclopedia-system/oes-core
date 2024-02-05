<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Schema')) oes_include('admin/tools/config/class-config-schema.php');

if (!class_exists('Schema_LOD')) :

    /**
     * Class Schema_LOD
     *
     * Implement the config tool for LOD options configurations.
     */
    class Schema_LOD extends Schema
    {

        /** @var string The api key. */
        public string $api_key = 'lod';


        //Overwrite parent
        function empty(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('There are no configurations found for this API for your project. ' .
                    'Enable API options in the <b>General</b> tab.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();

            if (isset($oes->apis[$this->api_key]))
                if (!empty($oes->apis[$this->api_key]->config_options['properties']['options'])) {

                    $postType = $oes->post_types[$this->object] ?? [];
                    $rows = [];
                    if ($postType['lod'] ?? false) {

                        /* prepare data */
                        $option = $oes->apis[$this->api_key]->config_options['properties'];

                        /* prepare table body */
                        foreach ($postType['field_options'] as $fieldKey => $field) {

                            /* skip field types */
                            $type = (oes_get_field_object($fieldKey) &&
                                isset(oes_get_field_object($fieldKey)['type'])) ?
                                oes_get_field_object($fieldKey)['type'] :
                                'tab';
                            if (in_array($type, ['tab', 'message', 'relationship', 'post', 'image', 'date_picker']))
                                continue;

                            $copyOption = $this->api_key . '_properties';
                            $rows[] = [
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'value' => '<strong>' . ($field['label'] ?? 'Label missing') .
                                            '</strong><div><code>' . $fieldKey . '</code>' . '</div>'
                                    ],
                                    [
                                        'class' => 'oes-table-transposed',
                                        'value' => oes_html_get_form_element($option['type'],
                                            'fields[' . $this->object . '][' . $fieldKey . '][' . $copyOption . ']',
                                            'fields-' . $this->object . '-' . $fieldKey . '_' . $copyOption,
                                            $field[$copyOption] ?? [],
                                            [
                                                'options' => $option['options'],
                                                'multiple' => $option['multiple'] ?? true,
                                                'class' => 'oes-replace-select2',
                                                'hidden' => ($option['type'] === 'select')
                                            ]
                                        )
                                    ]
                                ]
                            ];
                        }
                    }

                    if (!empty($rows)) $this->table_data[] = ['rows' => $rows];
                }
        }
    }
endif;