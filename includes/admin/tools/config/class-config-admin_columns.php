<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin_Columns')) :

    /**
     * Class Admin Columns
     *
     * Implement the config tool for admin columns configurations.
     */
    class Admin_Columns extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can add or remove columns for the list views of post objects in the admin area with the OES ' .
                    'feature <b>Admin Columns</b> and display information that helps you to administrate and ' .
                    'organizing your post objects.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();

            $postTypeRows = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* prepare options */
                $options = ['cb' => 'Checkbox', 'title' => 'Title', 'date' => 'Date',
                    'date_modified' => 'Modified Date', 'parent' => 'Parent'];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message']))
                            $options[$fieldKey] = __('Field: ', 'oes') . $field['label'];

                /* check for taxonomies */
                foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomyKey)
                    $options['taxonomy-' . $taxonomyKey] = $oes->taxonomies[$taxonomyKey]['label'];

                $postTypeRows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                        ],
                        [
                            'colspan' => '2',
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'post_types[' . $postTypeKey . '][oes_args][admin_columns]',
                                'post_types-' . $postTypeKey . '-oes_args-admin_columns',
                                $postTypeData['admin_columns'] ?? [],
                                [
                                    'options' => $options,
                                    'multiple' => true,
                                    'class' => 'oes-replace-select2',
                                    'reorder' => true,
                                    'hidden' => true
                                ])
                        ]
                    ]
                ];
            }

            if(!empty($postTypeRows)){
                $this->table_data[] = [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'colspan' => 3,
                                    'value' => '<strong>' . __('Post Types', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
                $this->table_data[] = [
                    'rows' => $postTypeRows
                ];
            }


            $taxonomyRows = [];
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomyData) {

                /* prepare options */
                $options = ['cb' => 'Checkbox', 'name' => 'Name', 'slug' => 'Slug',
                    'description' => 'Description', 'posts' => 'Count', 'id' => 'ID'];
                if (isset($taxonomyData['field_options']) && !empty($taxonomyData['field_options']))
                    foreach ($taxonomyData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message']))
                            $options[$fieldKey] = __('Field: ', 'oes') . $field['label'];

                $taxonomyRows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . ($taxonomyData['label'] ?? $taxonomyKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $taxonomyKey . '</code>'
                        ],
                        [
                            'colspan' => '2',
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'taxonomies[' . $taxonomyKey . '][oes_args][admin_columns]',
                                'taxonomies-' . $taxonomyKey . '-oes_args-admin_columns',
                                $taxonomyData['admin_columns'] ?? [],
                                [
                                    'options' => $options,
                                    'multiple' => true,
                                    'class' => 'oes-replace-select2',
                                    'reorder' => true,
                                    'hidden' => true
                                ])
                        ]
                    ]
                ];
            }

            if(!empty($taxonomyRows)) {
                $this->table_data[] = [
                    'type' => 'thead',
                    'rows' => [
                        [
                            'class' => 'oes-config-table-separator',
                            'cells' => [
                                [
                                    'type' => 'th',
                                    'colspan' => 3,
                                    'value' => '<strong>' . __('Taxonomies', 'oes') . '</strong>'
                                ]
                            ]
                        ]
                    ]
                ];
                $this->table_data[] = [
                    'rows' => $taxonomyRows
                ];
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Columns', 'admin-columns');

endif;