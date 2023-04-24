<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Admin_Inheritance')) :

    /**
     * Class Admin_Inheritance
     *
     * Implement the config tool for inheritance configurations.
     */
    class Admin_Inheritance extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Inheritance</b> allows to define bidirectional relationships between post ' .
                    'objects ' .
                    '(e.g. if you connect an article object with an author the author object will automatically be ' .
                    'connected to this article).', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            foreach (OES()->post_types as $postTypeKey => $postTypeData) {

                /* prepare nested table header */
                $firstRow = [[
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>Field</strong>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => '<strong>inherits to:</strong>'
                        ]
                    ]
                ]];

                /* loop through fields */
                $rows = [];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['inherit_to_options']) && !empty($field['inherit_to_options']))
                            $rows[] = [
                                'cells' => [
                                    [
                                        'type' => 'th',
                                        'value' => '<div><strong>' . ($field['label'] ?? $fieldKey) . '</strong>' .
                                            '<code class="oes-object-identifier">' . $fieldKey . '</code></div>'
                                    ],
                                    [
                                        'class' => 'oes-table-transposed',
                                        'value' => oes_html_get_form_element('select',
                                            'fields[' . $postTypeKey . '][' . $fieldKey . '][inherit_to]',
                                            'fields-' . $postTypeKey . '-' . $fieldKey . '-inherit_to',
                                            $field['inherit_to'] ?? [],
                                            ['options' => $field['inherit_to_options'], 'multiple' => true,
                                                'class' => 'oes-replace-select2', 'reorder' => true, 'hidden' => true])
                                    ]
                                ]
                            ];


                if (!empty($rows))
                    $this->table_data[] = [
                        'rows' => [
                            [
                                'type' => 'trigger',
                                'cells' => [
                                    [
                                        'value' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                                            '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                                    ]
                                ]
                            ],
                            [
                                'type' => 'target',
                                'nested_tables' => [
                                    [
                                        'rows' => array_merge($firstRow, $rows)
                                    ]
                                ]
                            ]
                        ]
                    ];
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Inheritance', 'admin-inheritance');
endif;