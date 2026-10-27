<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Schema', false)) :

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
         * The schema.prg type associated with the object.
         * Defaults to 'Thing' if not explicitly set.
         *
         * @var string
         */
        public string $schema_type = 'Thing';

        /**
         * The OES type associated with the object.
         * Defaults to 'other' if not explicitly set.
         *
         * @var string
         */
        public string $oes_type = 'other';

        protected function prepare_form_attributes(): void
        {
            $component = $_GET['component'] ?? ($_POST['component'] ?? 'post_types');
            $this->component = $component;
            $this->object = $_GET['object'] ?? ($_POST['object'] ?? '');
        }

        /** @inheritdoc */
        protected function prepare_form(): void
        {
            $this->form_action = admin_url('admin-post.php');

            if ($this->component == 'post_types') {
                $this->schema_type = oes_get_post_type_param($this->object, 'schema', 'other');
                $this->oes_type = oes_get_post_type_param($this->object, 'type', 'other');
            }
            elseif($this->component == 'taxonomies') {
                $this->schema_type = oes_get_taxonomy_param($this->object, 'schema', 'other');
                $this->oes_type = oes_get_taxonomy_param($this->object, 'type', 'other');
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
                'object' => $this->object,
                'component' => $this->component,
                'oes_type' => $this->oes_type,
                'schema_type' => $this->schema_type,
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

        /**
         * Render a select row.
         * @param string $title
         * @param string $key
         * @param mixed $value
         * @param array $options
         * @param bool $multiple
         * @param bool $secondInput
         * @param mixed|null $secondValue
         * @return void
         */
        protected function render_select_row(
            string $title,
            string $key,
            mixed $value,
            array $options,
            bool $multiple = false,
            bool $secondInput = false,
            mixed $secondValue = null): void
        {
            $args = ['options' => $options];
            if ($multiple) {
                $args += [
                    'multiple' => true,
                    'reorder' => true,
                    'hidden' => true,
                ];
            }

            $row = [
                'title' => $title,
                'key' => $key,
                'value' => $value,
                'type' => 'select',
                'args' => $args
            ];

            if($secondInput){

                if(is_array($secondValue)){
                    $secondValue = wp_json_encode($secondValue);
                }

                $newKey = str_ends_with($key, ']')
                    ? substr($key, 0, -1) . '_string]'
                    : $key . '_string';

                $this->add_table_row($row, [], [
                    'key' => $newKey,
                    'value' => is_string($secondValue) ? $secondValue : '',
                ]);
            }
            else{
                $this->add_table_row($row);
            }
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Schema', 'schema');

endif;

