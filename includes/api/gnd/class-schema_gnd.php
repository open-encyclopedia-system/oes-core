<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Schema_LOD')) oes_include('api/class-schema_lod.php');

if (!class_exists('Schema_GND')) :

    /**
     * Class Schema_GND
     *
     * Implement the config tool for GND options configurations.
     */
    class Schema_GND extends Schema_LOD
    {

        public string $api_key = 'gnd';

        /** @inheritdoc */
        function additional_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('Define the field mapping that determines which entry data will be imported to which post ' .
                    'object field.', 'oes') .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema_GND', 'schema-gnd');

endif;