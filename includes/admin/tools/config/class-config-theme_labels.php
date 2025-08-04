<?php

/**
 * @file
 * @reviewed 2.4.0
 */

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

        /** @var bool Add expand button on top. */
        public bool $expand_button = false;

        /** @inheritdoc */
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
            $this->add_table_header('', 'language_label', ['trigger' => false]);
        }

    }
endif;