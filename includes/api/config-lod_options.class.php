<?php

namespace OES\Admin\Tools;

use function OES\ACF\oes_get_field_object;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('LOD_Options')) :

    /**
     * Class LOD_Options
     *
     * Implement the config tool for LOD options configurations.
     */
    class LOD_Options extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('If the <b>Copy to Post</b> option is set for a post type, you can define copy behaviour ' .
                    'for the included LOD databases. E.g. for the included GND database define the field mapping that ' .
                    'determines which entry data will be imported to which post object field', 'oes') .
                '</p></div>';
        }

        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get theme label configurations */
            $oes = OES();
            if (!empty($oes->apis))
                foreach ($oes->apis as $apiKey => $apiOptions)
                    if (!empty($apiOptions->config_options['properties']['options'])) {

                        $tableData = [];

                        foreach ($oes->post_types as $postTypeKey => $postType)
                            if (isset($postType['lod_box']) &&
                                in_array('post', $postType['lod_box'])) {

                                /* prepare data */
                                $tableDataHead = [];
                                $tableDataBody = [];

                                $option = $apiOptions->config_options['properties'];

                                /* prepare table body */
                                foreach ($postType['field_options'] as $fieldKey => $field) {

                                    /* skip field types */
                                    $type = (oes_get_field_object($fieldKey) &&
                                        isset(oes_get_field_object($fieldKey)['type'])) ?
                                        oes_get_field_object($fieldKey)['type'] :
                                        'tab';
                                    if (in_array($type, ['tab', 'message', 'relationship', 'post', 'image', 'date_picker']))
                                        continue;

                                    $tableDataHead[] = '<strong>' . ($field['label'] ?? 'Label missing') .
                                        '</strong><div><code>' . $fieldKey . '</code>' . '</div>';

                                    $copyOption = $apiKey . '_properties';
                                    $tableDataBody[] = oes_html_get_form_element($option['type'],
                                        'fields[' . $postTypeKey . '][' . $fieldKey . '][' . $copyOption . ']',
                                        'fields-' . $postTypeKey . '-' . $fieldKey . '_' . $copyOption,
                                        $field[$copyOption] ?? [],
                                        [
                                            'options' => $option['options'],
                                            'multiple' => $option['multiple'] ?? true,
                                            'class' => 'oes-replace-select2',
                                            'hidden' => ($option['type'] === 'select')
                                        ]
                                    );
                                }

                                /* add to return value */
                                $tableData[] = [
                                    'header' => $postType['label'],
                                    'transpose' => true,
                                    'thead' => $tableDataHead,
                                    'tbody' => [$tableDataBody]
                                ];
                            }

                        $this->table_data[] = [
                            'type' => 'accordion',
                            'title' => $apiOptions->label ?? $apiKey,
                            'table' => $tableData
                        ];
                    }

            $this->table_title = __('Copy To Post', 'oes');
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LOD_Options', 'lod-options');

endif;