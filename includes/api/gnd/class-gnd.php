<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD')) oes_include('api/class-lod.php');

if (!class_exists('GND')) :

    /**
     * Class GND
     *
     * Implement the config tool for GND options configurations.
     */
    class GND extends LOD
    {
        /** @inheritdoc */
        public string $api_key = 'gnd';

        /** @inheritdoc */
        public bool $credentials_password = false;

        /** @inheritdoc */
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper">' .
                '<p>' .
                __('The Gemeinsame Normdatei (GND ) is a service for the use and management of authority files. ' .
                    'This authority data represents and describes entities, i.e. persons, corporate bodies, ' .
                    'conferences, geographies, subject headings and works that are related to cultural and ' .
                    'scientific collections.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://www.dnb.de/DE/Professionell/Standardisierung/GND/gnd_node.html" target="_blank">',
                    '</a>'
                ) .
                '</p><p>' .
                sprintf(__('You can define the copy behaviour in the %sOES schema settings%s.', 'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_settings_schema') . '">',
                    '</a>'
                ) .
                '</p><p>' .
                sprintf(__('The API is %spowered by %s%s.', 'oes'),
                    '<a href="https://lobid.org/" target="_blank" title="powered by lobid data">',
                    '<img src="https://lobid.org/images/lobid.png" alt="lobid-Logo" width="110" height="32"/>',
                    '</a>') .
                '</p>' .
                '</div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\GND', 'gnd');

endif;