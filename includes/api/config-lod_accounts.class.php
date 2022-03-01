<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('LOD_Accounts')) :

    /**
     * Class LOD_Accounts
     *
     * Implement the config tool for LOD options configurations.
     */
    class LOD_Accounts extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('Some apis require login information.', 'oes') .
                '</p></div>';
        }

        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get theme label configurations */
            $oes = OES();
            if (!empty($oes->apis))
                foreach ($oes->apis as $apiKey => $apiOptions)
                    if ($apiOptions->credentials){

                        $table[] = [
                            'header' => '<strong>' . __('General', 'oes') . '</strong>',
                            'transpose' => true,
                            'thead' => [
                                '<strong>' . __('Login', 'oes') .
                                '</strong><div>' . __('The login name', 'oes') . '</div>',
                                '<strong>' . __('Password', 'oes') .
                                '</strong><div>' . __('The login password', 'oes') . '</div>'
                            ],
                            'tbody' => [[
                                oes_html_get_form_element('text',
                                    'oes_api-geonames_login',
                                    'oes_api-geonames_login',
                                    get_option('oes_api-geonames_login')),
                                oes_html_get_form_element('password',
                                    'oes_api-geonames_password',
                                    'oes_api-geonames_password',
                                    get_option('oes_api-geonames_password')),
                            ]]
                        ];

                        $this->table_data[] = [
                            'title' => $apiOptions->label ?? $apiKey,
                            'table' => $table
                        ];
                    }


            $this->table_title = __('Accounts', 'oes');
        }

        //Implement parent
        function admin_post_tool_action()
        {
            /* add or delete option TODO : store password not in clear text? */
            $options = ['oes_api-geonames_login', 'oes_api-geonames_password'];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, $_POST[$option]);
                else update_option($option, $_POST[$option]);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LOD_Accounts', 'lod-accounts');

endif;