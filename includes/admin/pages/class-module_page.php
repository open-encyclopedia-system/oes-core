<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Module_Page')) :

    /**
     * Class Module_Page
     *
     * Registers a module admin subpage and integrates it with schema-related filters.
     */
    class Module_Page
    {
        /**
         * @var string $key
         * Unique identifier for the module, typically a lowercase slug used in settings keys.
         */
        public string $key = '';

        /**
         * @var string $name
         * Human-readable name of the module, shown in the admin UI.
         */
        public string $name = '';

        /**
         * @var string $title
         * Human-readable title of the module, shown in the admin page.
         */
        public string $title = '';

        /**
         * @var string $setting
         * Option key used for storing/retrieving this module's settings.
         */
        public string $setting = '';

        /**
         * @var string $parent_slug
         * Parent slug in admin menu.
         */
        public string $parent_slug = '';

        /**
         * @var int $position
         * Position of the menu item in the WordPress admin menu. Used for ordering.
         */
        public int $position = 0;

        /**
         * @var bool $schema_enabled
         * Whether this module should hook into schema-related filters (e.g., for structured data settings).
         */
        public bool $schema_enabled = true;

        /**
         * @var string $file
         * Optional file path to include as a view or handler for the module’s settings page.
         */
        public string $file = '';

        /**
         * @var array $components
         * List of supported schema components (e.g., 'post_types', 'taxonomies') that this module integrates with.
         */
        public array $components = [];

        /**
         * @var array $types
         * List of object types (e.g., 'single-article') that this module targets.
         */
        public array $types = [];

        /**
         * Module_Page constructor.
         *
         * @param array $args Configuration options for the module page.
         */
        public function __construct(array $args = [])
        {
            $this->set_parameters($args);

            add_action('admin_head', [$this, 'help_tab']);
            add_filter('oes/admin_menu_pages', [$this, 'admin_menu_pages']);

            if ($this->schema_enabled) {
                add_filter('oes/schema_general', [$this, 'schema_enable'], 10, 4);
                add_filter('oes/schema_tabs', [$this, 'schema_tabs'], 10, 2);
                add_filter('oes/schema_options_single', [$this, 'schema_options_single'], 10, 4);
            }
        }

        /**
         * Sets parameters passed to the module.
         *
         * @param array $args
         * @return void
         */
        public function set_parameters(array $args): void
        {
            $this->name             = $this->param($args, 'name', $this->name ?: 'Name missing');
            $this->title            = $this->param($args, 'title', $this->title ?: ($this->name ?: 'Title missing'));
            $this->key              = $this->param($args, 'key', $this->key ?: strtolower($this->name));
            $this->setting          = $this->param($args, 'setting', $this->setting ?: ('oes_' . $this->key));
            $this->position         = $this->param($args, 'position', $this->position ?: 50);
            $this->schema_enabled   = $this->param($args, 'schema_enabled', $this->schema_enabled ?: true);
            $this->file             = $this->param($args, 'file', $this->file ?: '');
            $this->components       = $this->param($args, 'components', $this->components ?: ['post_types']);
            $this->types            = $this->param($args, 'types', $this->types ?: ['single-article']);
            $this->parent_slug      = $this->param($args, 'parent_slug', $this->parent_slug ?: 'oes_modules');
        }

        /**
         * Set single parameter
         *
         * @param array $args
         * @param string $key
         * @param $default
         * @param bool $allowEmpty
         * @return mixed
         */
        private function param(array $args, string $key, $default, bool $allowEmpty = false) {
            if (array_key_exists($key, $args)) {
                return ($allowEmpty || !empty($args[$key])) ? $args[$key] : $default;
            }
            return $default;
        }

        /**
         * Adds this module to the admin menu pages filter.
         *
         * @param array $adminMenuPages
         * @return array
         */
        public function admin_menu_pages(array $adminMenuPages): array
        {
            $args = [
                'subpage' => true,
                'page_parameters' => [
                    'page_title' => $this->title,
                    'menu_title' => $this->title,
                    'menu_slug' => $this->setting,
                    'position' => $this->position,
                    'parent_slug' => $this->parent_slug
                ],
                'tool' => $this->key
            ];

            if (!empty($this->file)) {
                $args['view_file_name_full_path'] = $this->file;
            }

            $pageKey = sprintf("%03d_%s", $this->position, $this->key);
            $adminMenuPages[$pageKey] = $args;

            return $adminMenuPages;
        }

        /**
         * Registers contextual help tab if applicable.
         *
         * @return void
         */
        public function help_tab(): void
        {
            $screen = get_current_screen();
            $modulePageId = 'oes_page_' . $this->setting;

            if ($screen->id === $modulePageId ||
                ($screen->id === 'oes_page_oes_settings_schema' && ($_GET['type'] ?? '') === $this->key)) {
                $this->set_help_tabs($screen);
            }
        }

        /**
         * Add help tabs to the screen (to be implemented by developer).
         *
         * @param \WP_Screen $screen
         * @return void
         */
        public function set_help_tabs($screen): void
        {
        }

        /**
         * Adds schema config toggle for individual post types.
         *
         * @param array $configs
         * @param string $type
         * @param string $objectKey
         * @param string $component
         * @return array
         */
        public function schema_options_single(array $configs, string $type = '', string $objectKey = '', string $component = ''): array
        {
            if (in_array($type, $this->types) && in_array($component, $this->components)) {
                $configs[$this->key] = [
                    'label' => $this->name,
                    'option_name' => 'oes_' . $this->key . '-buttons-' . $objectKey
                ];
            }
            return $configs;
        }

        /**
         * Adds toggle to enable/disable module schema config for post types.
         *
         * @param array $configs
         * @param string $objectKey
         * @param string $type
         * @param string $component
         * @return array
         */
        public function schema_enable(array $configs, string $objectKey, string $type = '', string $component = ''): array
        {
            if (in_array($component, $this->components)) {
                $optionKey = $this->get_option_key($objectKey);

                if (empty($this->types) || in_array($type, $this->types)) {
                    $info = sprintf(__('Enable the %s configuration for this post type.', 'oes'), $this->name);
                } else {
                    $info = sprintf(__('Only recommended for objects of types "%s".', 'oes'), implode('", "', $this->types));
                }

                $configs[$this->key] = [
                    'label' => __('Enable ', 'oes') . $this->name,
                    'info' => $info,
                    'type' => 'checkbox',
                    'value' => get_option($optionKey) ?? false,
                    'options' => ['hidden' => true],
                    'option_key' => $optionKey
                ];
            }

            return $configs;
        }

        /**
         * Registers module-specific schema tabs (if enabled).
         *
         * @param array $tabs
         * @param string $objectKey
         * @return array
         */
        public function schema_tabs(array $tabs, string $objectKey = ''): array
        {
            $enabled = true;
            if (!empty($objectKey)) {
                $optionKey = $this->get_option_key($objectKey);
                $enabled = get_option($optionKey) ?? false;
            }

            if ($enabled) {
                $tabs[$this->key] = $this->name;
            }

            return $tabs;
        }

        /**
         * Generates the option key used for enabling/disabling a module for a post type.
         *
         * @param string $objectKey
         * @return string
         */
        public function get_option_key(string $objectKey): string
        {
            return 'oes_' . $this->key . '-enabled-' . $objectKey;
        }
    }

endif;
