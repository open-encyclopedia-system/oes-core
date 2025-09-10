<?php

/**
 * Open Encyclopedia System CORE
 *
 * @wordpress-plugin
 * Plugin Name:       OES Core
 * Plugin URI:        https://www.open-encyclopedia-system.org/
 * Description:       Tools for building and maintaining online encyclopedias.
 * Version:           2.4.0
 * Author:            Maren Welterlich-Strobl, Freie Universität Berlin, FUB-IT
 * Author URI:        https://www.it.fu-berlin.de/die-fub-it/mitarbeitende/mstrobl.html
 * Requires at least: 6.5
 * Tested up to:      6.8.2
 * Requires PHP:      8.1
 * Tags:              encyclopedia, open-access, digital-humanities, academic, wiki, lexicon, education
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** --------------------------------------------------------------------------------------------------------------------
 * Returns the singleton instance of the OES Core class.
 * ------------------------------------------------------------------------------------------------------------------- */
if (!function_exists('OES')) {

    /**
     * Returns the OES Core instance, initializing it if necessary.
     *
     * @param string $projectPath Optional path to the OES Project plugin.
     * @param array $args Optional additional arguments for initialization.
     *
     * @return OES_Core The OES Core instance.
     */
    function OES(string $projectPath = '', array $args = []): OES_Core
    {
        global $oes;

        if (!isset($oes)) {
            $oes = new OES_Core($args, $projectPath);
            if ($oes->initialized) $oes->initialize_core();
        }

        return $oes;
    }
}

/** --------------------------------------------------------------------------------------------------------------------
 * Add OES hook after all plugins are loaded. (This grants full control over the execution order outside the WordPress
 * hook.)
 * -------------------------------------------------------------------------------------------------------------------*/
add_action('plugins_loaded', function () {
    /**
     * Fires after all plugins are loaded.
     */
    do_action('oes/plugins_loaded');
});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * Initializes the OES Core plugin.
 * ---------------------------------------------------------------------------------------------------------------------
 * @throws Exception
 */
if (!class_exists('OES_Core')) :

    /**
     * Class OES_Core
     *
     * Main class for the OES Core plugin initialization and configuration.
     */
    class OES_Core
    {

        /** @var string The version of the OES Core plugin. */
        public string $version = '2.4.0';

        /** @var string The version of the OES database schema. */
        public string $db_version = '2.0';

        /** @var bool Indicates whether the project is legacy (before OES Core 2.3). */
        public bool $legacy = false;

        /** @var bool Indicates whether the project uses a block theme (Full Site Editing theme). */
        public bool $block_theme = true;

        /** @var bool Whether the OES Core plugin was successfully initialized. */
        public bool $initialized = true;

        /** @var bool Whether the ACF Pro plugin is active. */
        public bool $acf_pro = false;

        /** @var array Registered OES post types. */
        public array $post_types = [];

        /** @var array Registered OES taxonomies. */
        public array $taxonomies = [];

        /** @var array Media group configurations. */
        public array $media_groups = [];

        /** @var array Admin pages for OES Core and project plugins. See class 'Menu_Page'. */
        public array $admin_pages = [];

        /** @var array Supported languages for multilingual posts. Default is English. */
        public array $languages = [
            'language0' => [
                'label' => 'English',
                'abb' => 'ENG',
                'locale' => 'en_BE'
            ]
        ];

        /** @var array Registered admin tools. */
        public array $admin_tools = [];

        /** @var array API configurations. */
        public array $apis = [];

        /** @var string|int|bool The post ID of the OES object holding general configuration information. */
        public $config_post = false;

        /** @var array|bool Information for theme index pages. */
        public $theme_index_pages = false;

        /** @var array General theme labels. */
        public array $theme_labels = [];

        /** @var array Search configuration settings. */
        public array $search = [];

        /**
         * OES_Core constructor.
         *
         * Checks for ACF plugin activation, plugin compatibility, and sets configuration values.
         *
         * @param array $args Additional initialization parameters.
         * @param string $projectPath Absolute path to the project plugin.
         */
        public function __construct(array $args = [], string $projectPath = '')
        {
            if (!class_exists('ACF')) {
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                        __('The ACF plugin is not active.', 'oes') . '</p></div>';
                });
                $this->initialized = false;
            } else {

                if (!function_exists('acf_determine_internal_post_type')) {
                    add_action('admin_notices', function () {
                        echo '<div class="notice notice-warning is-dismissible"><p>' .
                            __('The ACF plugin might not be up to date. Required version is at least 6.2.0.', 'oes') .
                            '</p></div>';
                    });
                }

                $this->acf_pro = class_exists('acf_pro');
                $this->legacy = function_exists('oes_legacy');
                $this->block_theme = wp_get_theme()->is_block_theme();

                foreach ($args as $propertyKey => $value) {
                    if (property_exists($this, $propertyKey)) $this->$propertyKey = $value;
                }

                $this->define_constants($projectPath);
            }
        }

        /**
         * Defines global constants used by the OES Core plugin.
         *
         * @param string $projectPath The absolute path to the project plugin, if any.
         * @return void
         */
        public function define_constants(string $projectPath = ''): void
        {
            define('OES_CORE_PLUGIN', __DIR__);
            define('OES_BASENAME', basename(__DIR__));
            define('OES_ACF_PRO', class_exists('acf_pro'));
            define('OES_LIVEMODE', true);

            if (!empty($projectPath)) {
                define('OES_PROJECT_PLUGIN', $projectPath);
                define('OES_BASENAME_PROJECT', basename($projectPath));
            }
        }

        /**
         * Initializes the OES Core plugin functionalities and features.
         */
        function initialize_core(): void
        {
            /**
             * Include core plugin functionalities.
             * This includes functions that are used throughout both the OES Core plugin and the Project plugin.
             * Specifically, it includes the function 'oes_include'.
             */
            require(OES_CORE_PLUGIN . '/includes/functions-utility.php');
            require(OES_CORE_PLUGIN . '/includes/functions-features.php');


            // Initialize OES Features
            \OES\Features\utility_functions();
            \OES\Features\database();
            \OES\Features\tools();
            \OES\Features\admin_messages();
            \OES\Features\admin_functions();

            // Retrieve feature options to check which features are enabled
            $features = \OES\Admin\get_features();
            $is_enabled = fn($key) => !$features || ($features[$key] ?? false);

            \OES\Features\dashboard($is_enabled('dashboard'));
            \OES\Features\admin_pages();
            \OES\Features\assets();
            \OES\Features\columns();
            \OES\Features\user_profile_settings();

            \OES\Features\remarks($is_enabled('remarks'));
            \OES\Features\manual($is_enabled('manual'));
            \OES\Features\tasks($is_enabled('task'));

            \OES\Features\data_model();
            \OES\Features\data_model_factory();
            \OES\Features\formula_functions();
            \OES\Features\versioning();
            \OES\Features\cache($is_enabled('cache'));
            \OES\Features\lod_api($is_enabled('lod_apis'));

            \OES\Features\icons();
            \OES\Features\popup();
            \OES\Features\notes();
            \OES\Features\blocks();
            \OES\Features\theme_classes();
            \OES\Features\figures($is_enabled('figures'));
            \OES\Features\filter();
            \OES\Features\labels();
            \OES\Features\language_switch(!$this->block_theme);
            \OES\Features\search();
            \OES\Features\shortcodes();


            /**
             * Fires after the OES Plugin has been fully initialized.
             */
            do_action('oes/initialized');

            add_action('oes/data_model_registered', [$this, 'data_model_registered']);
        }

        /**
         * Initialize the OES Project plugin, including data model and project processing.
         *
         * @throws Exception
         */
        function initialize_project(): void
        {
            $generalConfigPost = get_posts([
                    'post_type' => 'oes_object',
                    'posts_per_page' => -1,
                    'name' => 'oes_config',
                    'post_status' => 'publish'
                ]
            );

            if (!empty($generalConfigPost)) {

                $this->config_post = $generalConfigPost[0]->ID ?? false;

                $generalConfigContent = json_decode(
                    get_post($generalConfigPost[0]->ID)->post_content ?? '{}',
                    true
                );

                if (isset($generalConfigContent['languages'])) {
                    foreach ($generalConfigContent['languages'] as $languageKey => $language) {
                        $this->languages[(str_starts_with($languageKey, 'language') ? '' : 'language') . $languageKey] =
                            array_merge(
                                [
                                    'label' => 'Label missing',
                                    'abb' => $languageKey
                                ],
                                $language
                            );
                    }
                }

                if (isset($generalConfigContent['container'])) {
                    foreach ($generalConfigContent['container'] as $containerID => $container) {
                        $this->admin_pages['container'][$containerID] = $container;
                    }
                }
            }

            oes_include('admin/functions-rights.php');
            add_action('init', '\OES\Rights\user_roles');
        }

        /**
         * Add processing after the data model is registered.
         *
         * @return void
         */
        function data_model_registered(): void
        {
            \OES\Block\register_block_styles();
            \OES\Block\register();

            foreach ($this->post_types as $postType => $postTypeConfiguration) {
                if (isset($postTypeConfiguration['admin_columns'])) {
                    add_filter('manage_' . $postType . '_posts_columns', '\OES\Admin\add_post_column');
                    add_action('manage_' . $postType . '_posts_custom_column', '\OES\Admin\display_post_column_value', 10, 2);
                    add_filter('manage_edit-' . $postType . '_sortable_columns', '\OES\Admin\make_columns_sortable');
                }
            }

            foreach ($this->taxonomies as $taxonomyKey => $taxonomyConfiguration) {
                if (isset($taxonomyConfiguration['admin_columns'])) {
                    add_filter('manage_edit-' . $taxonomyKey . '_columns', '\OES\Admin\add_post_column');
                    add_filter('manage_' . $taxonomyKey . '_custom_column', '\OES\Admin\display_taxonomy_column_value', 10, 3);
                    // @oesDevelopment: Add feature for taxonomy sortable columns (e.g., `add_filter('manage_edit-' . $taxonomyKey . '_sortable_columns', 'OES\Admin\make_columns_sortable');`)
                }

                if (sizeof($this->languages) > 1 && ($taxonomyConfiguration['language_dependent'] ?? false)) {
                    add_action($taxonomyKey . '_edit_form_fields', '\OES\Model\term_add_fields_for_multilingualism', 10, 2);
                    add_action('edited_' . $taxonomyKey, '\OES\Model\term_save_fields_for_multilingualism', 10, 2);
                }
            }
        }

        /**
         * Returns the plugin version or null if it doesn't exist.
         *
         * @return string|null Returns the version or null.
         */
        function get_version(): ?string
        {
            return $this->version ?? null;
        }

        /**
         * Set the config post ID linking the OES object post storing the general configuration to the OES instance.
         *
         * @param mixed $postID The OES object post ID storing the general configuration.
         * @return void
         */
        function set_config_post($postID): void
        {
            $this->config_post = $postID;
        }

        /**
         * Set general config parameters.
         *
         * @param array $config The config parameters.
         * @param mixed $postID The OES object post ID storing the general configuration.
         * @return void
         */
        function set_general_parameters(array $config, $postID = null): void
        {
            if ($postID) {
                $this->set_config_post($postID);
            }

            foreach ([
                         'theme_index_pages',
                         'theme_labels',
                         'search'
                     ] as $configKey) {
                if (isset($config[$configKey])) {
                    $this->$configKey = $config[$configKey];
                }
            }
        }

        /**
         * Set media parameters.
         *
         * @param array $oesArgs The media OES arguments.
         * @param mixed $postID The OES object post ID storing the media configuration.
         * @return void
         */
        function set_media_parameters(array $oesArgs, $postID = null): void
        {
            $this->media_groups = $oesArgs;

            if (!empty($postID)) {
                $this->media_groups['post_ID'] = $postID;
            }
        }

        /**
         * Set taxonomy parameters.
         *
         * @param string $taxonomyKey The taxonomy key.
         * @param array $oesArgs The taxonomy OES arguments.
         * @return void
         */
        function set_taxonomy_parameters(string $taxonomyKey, array $oesArgs): void
        {
            foreach (\OES\Model\get_taxonomy_oes_args_defaults() as $configKey => $default) {
                $this->taxonomies[$taxonomyKey][$configKey] = $oesArgs[$configKey] ?? $default;
            }

            if (empty($this->taxonomies[$taxonomyKey]['label'])) {
                $this->taxonomies[$taxonomyKey]['label'] = $taxonomyKey;
            }
        }

        /**
         * Set the post type parameters.
         *
         * @param string $postTypeKey The post type key.
         * @param array $oesArgs The post type OES arguments.
         * @param array $registerArgs The post type register arguments.
         * @return void
         */
        function set_post_type_parameters(string $postTypeKey, array $oesArgs, array $registerArgs = []): void
        {
            foreach (\OES\Model\get_post_type_oes_args_defaults() as $configKey => $default) {
                $this->post_types[$postTypeKey][$configKey] = $oesArgs[$configKey] ?? $default;
            }

            if (empty($this->post_types[$postTypeKey]['label'])) {
                $this->post_types[$postTypeKey]['label'] = $registerArgs['label'] ?? $postTypeKey;
            }

            foreach (['parent', 'version'] as $parameter) {
                if (!empty($oesArgs[$parameter]) && ($oesArgs[$parameter] !== 'none')) {
                    $this->post_types[$postTypeKey][$parameter] = $oesArgs[$parameter];
                }
            }

            $options = [
                'language' => 'none',
                'external' => []
            ];

            if ($this->post_types[$postTypeKey]['type'] == 'single-article') {
                $options = [
                    'authors' => [],
                    'creators' => [],
                    'subtitle' => 'none',
                    'citation' => 'none',
                    'excerpt' => 'none',
                    'featured_image' => 'none',
                    'licence' => 'none',
                    'pub_date' => 'none',
                    'edit_date' => 'none',
                    'language' => 'none',
                    'version_field' => 'none',
                    'literature' => [],
                    'terms' => [],
                    'external' => [],
                    'lod' => false
                ];
            }
            elseif ($this->post_types[$postTypeKey]['type'] == 'single-contributor') {
                $options = [
                    'vita' => 'none',
                    'publications' => 'none',
                    'language' => 'none',
                    'external' => [],
                    'lod' => false
                ];
            }

            foreach ($options as $parameter => $default) {
                $this->post_types[$postTypeKey][$parameter] = $oesArgs[$parameter] ?? $default;
            }

            $themeLabels = array_merge([], $oesArgs['theme_labels'] ?? []);
            if (!empty($themeLabels)) {
                $this->post_types[$postTypeKey]['theme_labels'] = $themeLabels;
            }
        }

        /**
         * Set field options.
         *
         * @param string $component The component.
         * @param string $key The object key.
         * @param array $fields The fields.
         * @param string|int $postID The post ID of the OES object post.
         * @return void
         */
        function set_field_options(string $component, string $key, array $fields, $postID): void
        {
            if ($component == 'taxonomy') $component = 'taxonomies';
            elseif ($component == 'post_type') $component = 'post_types';
            $target = &$this->$component;

            $target[$key]['acf_ids'][$postID] = $postID;

            foreach ($fields as $field) {
                $target[$key]['field_options'][$field['key']]['label'] = $field['label'];
                $target[$key]['field_options'][$field['key']]['type'] = $field['type'];

                if(!empty($field['taxonomy'] ?? '')){
                    $target[$key]['field_options'][$field['key']]['taxonomy'] = $field['taxonomy'] ?? '';
                }

                if (!empty($this->languages)) {
                    foreach ($this->languages as $languageKey => $language) {
                        $target[$key]['field_options'][$field['key']]['label_translation_' . $languageKey] =
                            $field['label_translation_' . $languageKey] ?? $field['label'];
                    }
                }

                foreach ([
                             'pattern' => [],
                             'language_dependent' => false,
                             'display_option' => 'none',
                             'display_prefix' => ''
                         ] as $optionKey => $defaultOption) {
                    $target[$key]['field_options'][$field['key']][$optionKey] = $field[$optionKey] ?? $defaultOption;
                }

                if (!empty($this->apis)) {
                    foreach ($this->apis as $apiKey => $ignore) {
                        if (isset($field[$apiKey . '_properties'])) {
                            $target[$key]['field_options'][$field['key']][$apiKey . '_properties'] =
                                $field[$apiKey . '_properties'];
                        }
                    }
                }
            }
        }

        /**
         * Set field options for media field groups.
         *
         * @param array $fields The fields.
         * @return void
         */
        function set_media_field_options(array $fields): void
        {
            foreach ($fields as $field) {
                $this->media_groups['fields'][$field['key']]['label'] = $field['label'];
                $this->media_groups['fields'][$field['key']]['type'] = $field['type'];

                if (!empty($this->languages)) {
                    foreach ($this->languages as $languageKey => $language) {
                        $this->media_groups['fields'][$field['key']]['label_translation_' . $languageKey] =
                            $field['label_translation_' . $languageKey] ?? $field['label'];
                    }
                }

                foreach ([
                             'pattern' => [],
                             'language_dependent' => false,
                             'display_option' => 'none',
                             'display_prefix' => ''
                         ] as $optionKey => $defaultOption) {
                    $this->media_groups['fields'][$field['key']][$optionKey] = $field[$optionKey] ?? $defaultOption;
                }
            }
        }
    }
endif;