<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('LoC_Options')) :

    /**
     * Class LoC_Options
     *
     * Implement the config tool for LoC options configurations.
     */
    class LoC_Options extends LOD_Options
    {

        public string $api_key = 'loc';

        //Overwrite parent
        function empty(): string{
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('There are currently no configuration options for the Library of Congress API.', 'oes') .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LoC_Options', 'loc');

endif;