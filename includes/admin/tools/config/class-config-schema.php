<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Admin\Tools\Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Schema')) :

    /**
     * Class Schema
     *
     * A configuration tool used for managing OES schema-related settings
     * for post types and taxonomies in the WordPress admin.
     *
     * Extends the base Config tool.
     *
     * @package OES\Admin\Tools
     */
    class Schema extends Config
    {
        /**
         * The component type.
         * Valid values: 'post_types' or 'taxonomies'.
         *
         * @var string
         */
        public string $component = '';

        /**
         * The post type or taxonomy key.
         *
         * @var string
         */
        public string $object = '';

        /**
         * The OES type associated with the object.
         * Defaults to 'other' if not explicitly set.
         *
         * @var string
         */
        public string $oes_type = 'other';

        /** @inheritdoc */
        public function initialize_parameters($args = []): void
        {
            $this->form_action = admin_url('admin-post.php');

            $component = $_GET['component'] ?? ($_POST['component'] ?? 'post_types');
            $this->component = $component;
            $this->object = $_GET['object'] ?? ($_POST['object'] ?? '');

            if (!empty($component)) {
                $this->oes_type = OES()->$component[$this->object]['type'] ?? 'other';
            }
        }

        /** @inheritdoc */
        public function information_html(): string
        {
            return $this->get_schema_additional_html();
        }

        /**
         * Provide schema-specific additional HTML content.
         *
         * Can be overridden in subclasses.
         *
         * @return string Returns the additional HTML (default empty).
         */
        public function get_schema_additional_html(): string
        {
            return '';
        }

        /** @inheritdoc */
        public function set_hidden_inputs(): void
        {
            $this->hidden_inputs = [
                'object'    => $this->object,
                'component' => $this->component,
                'oes_type'  => $this->oes_type,
            ];
        }

        /**
         * Retrieve the option array for a given component.
         *
         * @param string $component The component key (e.g., 'post_types').
         * @return array Returns the decoded option array, or an empty array on failure.
         */
        public function get_option_array(string $component = ''): array
        {
            $optionDB = get_option(($this->options['prefix'] ?? '') . $component) ?? false;

            if (!$optionDB) {
                return [];
            }

            $optionArray = json_decode($optionDB, true);

            return is_array($optionArray) ? $optionArray : [];
        }

        /**
         * Construct the option name for this schema tool.
         *
         * Takes into account object dependency and suffix options.
         *
         * @return string The composed option name.
         */
        public function get_option_name(): string
        {
            $option = $this->options['name'] ?? '';

            if ($this->options['object_dependent'] ?? true) {
                $option .= '-' . $this->object;
            }

            $suffix = $this->options['suffix'] ?? '';
            if (!empty($suffix)) {
                $option .= '-' . $suffix;
            }

            return $option;
        }
    }

    // Register the schema tool
    register_tool('\OES\Admin\Tools\Schema', 'schema');

endif;
