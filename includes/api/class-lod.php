<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('LOD')) :

    /**
     * Class LOD
     *
     * Implement the config tool for LOD configurations.
     */
    class LOD extends Config
    {

        //Set parent parameter
        public bool $empty_allowed = true;

        /** @var string The api key. */
        public string $api_key = 'lod';

        /** @var boolean Include credentials password. */
        public bool $credentials_password = true;


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
            if (isset($oes->apis[$this->api_key]) && $oes->apis[$this->api_key]->credentials) {
                $rows = [
                    [
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
                    ]
                ];
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

                $this->table_data[] = ['rows' => $rows];
            }
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            /* add or delete option */
            //@oesDevelopment Store password not in clear text
            $options = [
                'oes_api-' . $this->api_key . '_login',
                'oes_api-' . $this->api_key . '_password'
            ];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, $_POST[$option]);
                else update_option($option, $_POST[$option]);
        }
    }
endif;