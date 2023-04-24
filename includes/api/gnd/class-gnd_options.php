<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('GND_Options')) :

    /**
     * Class GND_Options
     *
     * Implement the config tool for GND options configurations.
     */
    class GND_Options extends LOD_Options
    {

        public string $api_key = 'gnd';

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('If the <b>Copy to Post</b> option is set for a post type, you can define copy behaviour ' .
                    'for the included LOD databases. E.g. for the included GND database define the field mapping that ' .
                    'determines which entry data will be imported to which post object field', 'oes') .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\GND_Options', 'gnd');

endif;