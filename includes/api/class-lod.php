<?php

/**
 * @file
 * @reviewed 2.4.0
 */

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

        /** @inheritdoc */
        public bool $empty_allowed = true;

        /** @var string The api key. */
        public string $api_key = 'lod';

        /** @var boolean Include credentials password. */
        public bool $credentials_password = true;

        /** @inheritdoc */
        function empty(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('There are no configurations found for this API for your project. ' .
                    'Enable API options in the <b>General</b> tab.', 'oes') .
                '</p></div>';
        }

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            $prefixOption = 'oes_api-' . $this->api_key;

            if (isset($oes->apis[$this->api_key]) && $oes->apis[$this->api_key]->credentials) {

                $this->add_table_row(
                    [
                        'title' => __('Login', 'oes'),
                        'key' => $prefixOption . '_login',
                        'value' => get_option($prefixOption . '_login')
                    ]
                );

                if ($this->credentials_password) {
                    $this->add_table_row(
                        [
                            'title' => __('Password', 'oes'),
                            'key' => $prefixOption . '_password',
                            'value' => get_option($prefixOption . '_password'),
                            'type' => 'password'
                        ]
                    );
                }
            }
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            //@oesDevelopment Store password not in clear text
            $options = [
                'oes_api-' . $this->api_key . '_login',
                'oes_api-' . $this->api_key . '_password'
            ];
            foreach ($options as $option) {
                if (!oes_option_exists($option)) {
                    add_option($option, $_POST[$option]);
                }
                else {
                    update_option($option, $_POST[$option]);
                }
            }
        }
    }
endif;