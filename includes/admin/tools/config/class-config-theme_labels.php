<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Labels')) :

    /**
     * Class Theme_Labels
     *
     * Implement the config tool for theme label configurations.
     */
    class Theme_Labels extends Config
    {

        /** @var array Prepare the language row */
        public array $language_row = [];

        /** @var bool Add expand button on top. */
        public bool $expand_button = false;


        //Overwrite parent
        function information_html(): string
        {
            return $this->expand_button ? get_expand_button() : '';
        }


        /**
         * Set language row as table header.
         * @return void
         */
        function set_language_row(): void
        {
            $cells = [[
                'type' => 'th',
                'value' => '<strong>' . __('Label', 'oes') . '</strong>'
            ]];
            foreach (OES()->languages as $language) {
                $cells[] = [
                    'type' => 'th',
                    'class' => 'oes-table-transposed',
                    'value' => '<strong>' . $language['label'] . '</strong>'
                ];
            }
            $this->language_row = [[
                'cells' => $cells
            ]];
        }

    }
endif;