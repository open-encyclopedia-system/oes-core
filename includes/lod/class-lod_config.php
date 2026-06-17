<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists('LOD', false)) {

    /**
     * Class LOD
     *
     * Implement the config tool for LOD configurations.
     */
    class LOD extends Config
    {
        public bool $empty_allowed = true;
        public string $api_key = 'lod';
        public bool $credentials_password = true;

        /** @inheritdoc */
        function empty(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('There are no configurations found for this API for your application. ' .
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

            $popup = get_option($prefixOption . '_popup');
            $this->add_table_row(
                [
                    'title' => __('Render Shortcode as Popup', 'oes'),
                    'key' => $prefixOption . '_popup',
                    'value' => get_option($prefixOption . '_popup'),
                    'type' => 'checkbox'
                ]
            );

            //TODO: make this a general API option? Instead of multiple one
            if($popup){

                $apiInterface = '\\OES\\API\\' . $this->api_key . '_Interface';
                $properties = (class_exists($apiInterface) ? $apiInterface::PROPERTIES : []);

                $options = [];
                foreach($properties as $propertyKey => $propertyData){
                    $options[$propertyKey] = implode(' / ', ($propertyData['label'] ?? [])) . ' (' . $propertyKey . ')';
                }

                $this->add_table_row(
                    [
                        'title' => __('Include in Popup', 'oes'),
                        'key' => $prefixOption . '_popup_include',
                        'value' => get_option($prefixOption . '_popup_include'),
                        'type' => 'select',
                        'args' => [
                            'options' => $options,
                            'multiple' => true,
                            'reorder' => true,
                            'hidden' => true,
                        ],
                    ]
                );
            }
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            $prefixOption = 'oes_api-' . $this->api_key;

            $options = array_merge([$prefixOption . '_popup' => false], $_POST);

            foreach($options as $option => $value){
                //@oesDevelopment Store password not in clear text
                if(str_starts_with($option, $prefixOption)){
                    if (!oes_option_exists($option)) {
                        add_option($option, $value);
                    } else {
                        update_option($option, $value);
                    }
                }
            }
        }
    }
}
