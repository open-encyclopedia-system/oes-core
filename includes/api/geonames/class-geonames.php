<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD')) oes_include('api/class-lod.php');

if (!class_exists('Geonames')) :

    /**
     * Class Geonames
     *
     * Implement the config tool for Geonames options configurations.
     */
    class Geonames extends LOD
    {
        /** @inheritdoc */
        public string $api_key = 'geonames';

        /** @inheritdoc */
        public bool $credentials_password = false;

        /** @inheritdoc */
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper">' .
                '<p>' .
                __('The GeoNames geographical database covers all countries and contains over eleven million ' .
                    'placenames that are available for download free of charge.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://www.geonames.org/" target="_blank">',
                    '</a>'
                ) .
                '</p><p>' .
                sprintf('The GeoNames API requires a username which will be passed with each request. ' .
                    'The username for your application can be registered %shttp://www.geonames.org/login%s. ' .
                    'You will then receive an email with a confirmation link, and after you have confirmed the email ' .
                    'you can enable your account for the webservice on your account page ' .
                    '%shttp://www.geonames.org/manageaccount%s. ' .
                    'You need to fill out the <b>username</b> in the Credentials options below to enable the API.',
                    '<a href="http://www.geonames.org/login" target="_blank">',
                    '</a>',
                    '<a href="http://www.geonames.org/manageaccount" target="_blank">',
                    '</a>') .
                '</p><p>' .
                __('The GeoNames API has a daily limit of 20.000 per application ' .
                    '(identified by your registered username), the hourly limit is 1000 credits. ' .
                    'A credit is a web service request hit for most services (e.g. 1 credit per search). An exception ' .
                    'is thrown when the limit is exceeded.', 'oes') .
                '</p><p>' .
                sprintf(__('You can define the copy behaviour in the %sOES schema settings%s.', 'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_settings_schema') . '">',
                    '</a>'
                ) .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Geonames', 'geonames');

endif;