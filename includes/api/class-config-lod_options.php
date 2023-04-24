<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field_object;

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('LOD_Options')) :

    /**
     * Class LOD_Options
     *
     * Implement the config tool for LOD options configurations.
     */
    class LOD_Options extends Config
    {

        /** @var string The api key. */
        public string $api_key = 'lod';

        /** @var boolean Include credentials password. */
        public bool $credentials_password = true;


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
        function empty(): string{
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
                    $this->set_property_options();
                }
            if (isset($oes->apis[$this->api_key]) && $oes->apis[$this->api_key]->credentials)
                $this->set_credential_options();
        }


        /**
         * Set property options
         */
        function set_property_options()
        {

            /* get theme label configurations */
            $oes = OES();

            $nestedTable = [];
            foreach ($oes->post_types as $postTypeKey => $postType) {

                $rows = [];
                if (isset($postType['lod_box']) &&
                    in_array('post', $postType['lod_box'])) {

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
                                        'fields[' . $postTypeKey . '][' . $fieldKey . '][' . $copyOption . ']',
                                        'fields-' . $postTypeKey . '-' . $fieldKey . '_' . $copyOption,
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

                    $nestedTable[] = [
                        'type' => 'trigger',
                        'cells' => [
                            [
                                'value' => '<strong>' . ($postType['label'] ?? $postTypeKey) . '</strong>' .
                                    '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                            ]
                        ]
                    ];
                    $nestedTable[] = [
                        'type' => 'target',
                        'nested_tables' => [
                            [
                                'rows' => $rows
                            ]
                        ]
                    ];
                }
            }

            if (!empty($nestedTable))
                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . __('Properties for "Copy to Post"', 'oes') . '</strong>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => $nestedTable
                                ]
                            ]
                        ]
                    ]
                ];
        }


        /**
         * Set property options
         */
        function set_credential_options()
        {
            $rows = [[
                'cells' => [
                    [
                        'type' => 'th',
                        'value' => '<strong>' . __('Login', 'oes') .
                            '</strong><div>' . __('The login name or username', 'oes') . '</div>'
                    ],
                    [
                        'class' => 'oes-table-transposed',
                        'value' => oes_html_get_form_element('text',
                            'oes_api-geonames_login',
                            'oes_api-geonames_login',
                            get_option('oes_api-geonames_login'))
                    ]
                ]
            ]];
            if ($this->credentials_password)
                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . __('Password', 'oes') .
                                '</strong><div>' . __('The login password', 'oes') . '</div>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('password',
                                'oes_api-geonames_password',
                                'oes_api-geonames_password',
                                get_option('oes_api-geonames_password'))
                        ]
                    ]
                ];

            $this->table_data[] = [
                'rows' => [
                    [
                        'type' => 'trigger',
                        'cells' => [
                            [
                                'value' => '<strong>' . __('Credentials', 'oes') . '</strong>'
                            ]
                        ]
                    ],
                    [
                        'type' => 'target',
                        'nested_tables' => [
                            [
                                'rows' => $rows
                            ]
                        ]
                    ]
                ]
            ];
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            /* add or delete option */
            //@oesDevelopment Store password not in clear text
            $options = ['oes_api-' . $this->api_key . '_login', 'oes_api-' . $this->api_key . '_password'];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, $_POST[$option]);
                else update_option($option, $_POST[$option]);

                parent::admin_post_tool_action();
        }
    }
endif;