<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOD')) oes_include('api/class-lod.php');

if (!class_exists('LoC')) :

    /**
     * Class LoC
     *
     * Implement the config tool for LoC options configurations.
     */
    class LoC extends LOD
    {

        /** @inheritdoc */
        public string $api_key = 'loc';

        /** @inheritdoc */
        function information_html(): string{
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The Library of Congress (LoC) is the largest library in the world, with millions of books, films and '.
                    'video, audio recordings, photographs, newspapers, maps and manuscripts in its collections. ' .
                    'The Library is the main research arm of the U.S. Congress and the home of the U.S. ' .
                    'Copyright Office. The LoC API provides structured data about LoC subjects.', 'oes') .
                ' ' .
                sprintf(__('Visit the %swebsite%s for more information.', 'oes'),
                    '<a href="https://www.loc.gov/" target="_blank">',
                    '</a>'
                ) .
                '</p><p>' .
                __('There are currently no configuration options for the Library of Congress API.', 'oes') .
                '</p></div>';
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LoC', 'loc');

endif;