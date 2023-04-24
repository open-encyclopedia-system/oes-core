<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Geonames_Options')) :

    /**
     * Class Geonames_Options
     *
     * Implement the config tool for Geonames options configurations.
     */
    class Geonames_Options extends LOD_Options
    {

        public string $api_key = 'geonames';
        public bool $credentials_password = false;

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                sprintf('The geonames API requires a username which will be passed with each request. ' .
                    'The username for your application can be registered %shttp://www.geonames.org/login%s. ' .
                    'You will then receive an email with a confirmation link, and after you have confirmed the email ' .
                    'you can enable your account for the webservice on your account page ' .
                    '%shttp://www.geonames.org/manageaccount%s. ' .
                    'You need to fill out the <b>username</b> in the Credentials options below to enable the API.',
            '<a href="http://www.geonames.org/login" target="_blank">',
                '</a>',
                    '<a href="http://www.geonames.org/manageaccoun" target="_blank">',
                    '</a>') .
                '</p><p>' .
                __('The geonames API has a daily limit of 20.000 per application ' .
                    '(identified by your registered username), the hourly limit is 1000 credits. ' .
                    'A credit is a web service request hit for most services (e.g. 1 credit per search). An exception ' .
                    'is thrown when the limit is exceeded.', 'oes') .
                '</p><p>' .
                __('If the <b>Copy to Post</b> option is set for a post type, you can define copy behaviour ' .
                    'for the included LOD databases. E.g. for the included geonames database define the field mapping that ' .
                    'determines which entry data will be imported to which post object field', 'oes') .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Geonames_Options', 'geonames');

endif;