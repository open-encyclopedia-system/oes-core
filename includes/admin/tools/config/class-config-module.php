<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Module')) :

    /**
     * Class Module
     *
     * Handles configuration tools for modules.
     * Extends the base Config class to provide post type–aware and JSON-capable options.
     *
     * @package OES\Admin\Tools
     */
    class Module extends Config
    {
        /**
         * Prefix for the options stored in the database.
         *
         * @var string
         */
        public string $option_prefix = '';

        /**
         * Whether the option values are JSON encoded.
         *
         * @var bool
         */
        public bool $encoded = false;

        /**
         * Whether the configuration is dependent on post types.
         *
         * @var bool
         */
        public bool $post_type_dependent = true;

        /**
         * Handles saving module settings when the tool action is triggered via POST.
         *
         * This function loops through post types (if applicable) and updates the corresponding option(s).
         *
         * @return void
         */
        public function admin_post_tool_action(): void
        {
            if ($this->post_type_dependent) {
                global $oes;

                foreach ($oes->post_types as $postTypeKey => $postType) {
                    $this->update_option($postTypeKey);
                }
            } else {
                $this->update_option();
            }
        }

        /**
         * Updates a single module option in the database.
         *
         * @param string $postTypeKey Optional post type key.
         * @return void
         */
        protected function update_option(string $postTypeKey = ''): void
        {
            $option = $this->get_single_option_name($postTypeKey);
            $value = $this->get_value($option, $postTypeKey);

            if (!oes_option_exists($option)) {
                add_option($option, $value);
            } else {
                update_option($option, $value);
            }
        }

        /**
         * Constructs the option name based on prefix, post type, and the specific option name.
         *
         * @param string $postTypeKey Optional post type key.
         * @return string The fully-qualified option key.
         */
        protected function get_single_option_name(string $postTypeKey = ''): string
        {
            $option = $this->option_prefix;

            if (!empty($postTypeKey)) {
                $option .= '-' . $postTypeKey;
            }

            if (!empty($this->option)) {
                $option .= '-' . $this->option;
            }

            return $option;
        }

        /**
         * Retrieves the value from $_POST to be saved in the database.
         *
         * If encoding is enabled, this will encode the relevant input as JSON.
         *
         * @param string $option The option name.
         * @param string $postTypeKey Optional post type key.
         * @return mixed The sanitized or modified value.
         */
        protected function get_value(string $option, string $postTypeKey = ''): mixed
        {
            $value = '';

            if ($this->encoded) {
                if (!empty($postTypeKey) && isset($_POST[$this->option_prefix][$postTypeKey])) {
                    $value = json_encode($_POST[$this->option_prefix][$postTypeKey]);
                } elseif (isset($_POST[$this->option_prefix])) {
                    $value = json_encode($_POST[$this->option_prefix]);
                }
            } else {
                $value = $_POST[$option] ?? '';
            }

            return $this->modify_value($value);
        }

        /**
         * Allows subclasses to modify the saved value before writing it to the database.
         *
         * @param mixed $value The value to modify.
         * @return mixed The modified value.
         */
        protected function modify_value(mixed $value): mixed
        {
            return $value;
        }

        /**
         * Retrieves a decoded JSON array from a saved option, based on component key.
         *
         * @param string $component The component identifier (e.g. 'post_types').
         * @return array Returns the decoded option array or an empty array.
         */
        public function get_option_array(string $component = ''): array
        {
            $optionKey = $this->option_prefix . $component;
            $optionDB = get_option($optionKey);

            if (!$optionDB) {
                return [];
            }

            $optionArray = json_decode($optionDB, true);
            return is_array($optionArray) ? $optionArray : [];
        }
    }

endif;
