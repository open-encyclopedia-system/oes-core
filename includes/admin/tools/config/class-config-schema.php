<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Admin\Tools\Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Schema')) :

    /**
     * Class Schema
     *
     * Implement the config tool for admin configurations.
     */
    class Schema extends Config
    {

        /** @var string The component. Valid parameters are 'post_types', 'taxonomies'. */
        public string $component = '';

        /** @var string The post type or taxonomy. */
        public string $object = '';

        /** @var string The OES type of the object. */
        public string $oes_type = 'other';


        //Overwrite parent
        function initialize_parameters($args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
            $component = $_GET['component'] ?? ($_POST['component'] ?? 'post_types');
            $this->component = $component;
            $this->object = $_GET['object'] ?? ($_POST['object'] ?? '');
            if (!empty($component)) $this->oes_type = OES()->$component[$this->object]['type'] ?? 'other';
        }


        //Overwrite parent
        function information_html(): string
        {
            return '<h2 style="text-align:center">' . (OES()->{$this->component}[$this->object]['label'] ?? '') .
                '<code class="oes-object-identifier">' . $this->object . '</code>' .
                '</h2>' .
                $this->get_tabs() .
                $this->get_schema_additional_html();
        }


        /**
         * Get additional html, displayed after the tabs.
         *
         * @return string Returns the additional html.
         */
        function get_schema_additional_html(): string
        {
            return '';
        }


        /**
         * Get the tabs (differentiate between "General", "Single" and "Archive" settings).
         *
         * @return string Return the html representation of tabs.
         */
        function get_tabs(): string
        {

            $tabs = [
                'oes' => __('General', 'oes'),
                'oes_single' => __('Single', 'oes'),
                'oes_archive' => __('Archive', 'oes'),
            ];

            /* add LoD tabs */
            global $oes;
            if ($oes->post_types[$this->object]['lod'] ?? false) {
                foreach ($oes->apis as $apiKey => $api) {
                    if (!empty($api->config_options['properties']['options'])) $tabs[$apiKey] = $api->label;
                }
            }


            /**
             * Filters the tabs for the OES schema.
             *
             * @param array $tabs The tabs for the OES schema.
             */
            $tabs = apply_filters('oes/schema_tabs', $tabs, $this->object, $this->component, $this->oes_type);


            $links = '';
            foreach ($tabs as $type => $label)
                $links .= oes_get_html_anchor($label,
                    admin_url('admin.php?page=oes_settings_schema&tab=schema' .
                        '&type=' . $type .
                        '&component=' . $this->component .
                        '&object=' . $this->object),
                    false,
                    'oes-tab' . ((isset($_GET['type']) && $_GET['type'] == $type) ? ' active' : '')
                );

            return '<div class="oes-inner-tabs-wrapper">' .
                '<nav class="oes-tabs-wrapper hide-if-no-js tab-count-8">' .
                $links .
                '</nav>' .
                '</div>';
        }


        /**
         * Set hidden form inputs.
         *
         * @return void
         */
        function set_hidden_inputs(): void
        {
            $this->hidden_inputs = [
                'object' => $this->object,
                'component' => $this->component,
                'oes_type' => $this->oes_type
            ];
        }


        /**
         * Get option array for component.
         *
         * @param string $component The component. Valid parameters are 'post_types' and 'taxonomies'.
         * @return array Return the option or empty array.
         */
        function get_option_array(string $component = ''): array
        {
            $optionDB = get_option(($this->options['prefix'] ?? '') . $component) ?? false;
            if (!$optionDB) return [];

            $optionArray = json_decode($optionDB, true);
            if (!is_array($optionArray)) return [];

            return $optionArray;
        }


        /**
         * Get option name for this config tool.
         *
         * @return string Return option name.
         */
        function get_option_name(): string
        {
            $option = $this->options['name'] ?? false;
            if ($this->options['object_dependent'] ?? true) $option .= '-' . $this->object;
            if (!empty($this->options['suffix'] ?? '')) $option .= '-' . $this->options['suffix'] ?? '';
            return $option;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Schema', 'schema');

endif;