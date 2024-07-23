<?php

/**
 * Open Encyclopedia System CORE
 *
 * @wordpress-plugin
 * Plugin Name: OES Core
 * Plugin URI: http://www.open-encyclopedia-system.org/
 * Description: Building and maintaining online encyclopedias.
 * Version: 2.3.3
 * Author: Maren Welterlich-Strobl, Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
 * Author URI: https://www.cedis.fu-berlin.de/cedis/mitarbeiter/beschaeftigte/mstrobl.html
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/** --------------------------------------------------------------------------------------------------------------------
 * The OES instance.
 * -------------------------------------------------------------------------------------------------------------------*/
if (!function_exists('OES')) {

    /**
     * The function returns the OES instance like a global variable everywhere inside the plugin.
     * It initializes the OES plugin if not yet initialized.
     *
     * @param string $projectPath The path to the OES Project plugin.
     * @param array $args Additional arguments.
     *
     * @return OES_Core Returns the OES plugin instance.
     */
    function OES(string $projectPath = '', array $args = []): OES_Core
    {
        global $oes;

        /* initialize the OES Core and return the global instance. */
        if (!isset($oes)) {
            $oes = new OES_Core($args, $projectPath);
            if ($oes->initialized) $oes->initialize_core();
        }
        return $oes;
    }
}


/** --------------------------------------------------------------------------------------------------------------------
 * Add OES hook after plugin is loaded. (This grants full control over the execution order outside the WordPress hook.)
 * -------------------------------------------------------------------------------------------------------------------*/
add_action('plugins_loaded', function () {

    /**
     * Fires after all plugins are loaded.
     */
    do_action('oes/plugins_loaded');
});


/** --------------------------------------------------------------------------------------------------------------------
 * This function will initialize the OES Core plugin.
 * ---------------------------------------------------------------------------------------------------------------------
 * @throws Exception
 */
if (!class_exists('OES_Core')) :

    /**
     * Class OES_Core
     *
     * This function initialize the OES Core plugin.
     */
    class OES_Core
    {

        /** @var string The OES Core plugin version. */
        public string $version = '2.3.3';

        /** @var string The OES Database version. */
        public string $db_version = '2.0';

        /** @var bool Identify if project is legacy (before OES Core 2.3). */
        public bool $legacy = false;

        /** @var bool Identify if project uses a block theme (full site editing theme). */
        public bool $block_theme = true;

        /** @var bool The OES Core plugin was successfully initialized. False on error. */
        public bool $initialized = true;

        /** @var bool The ACF Pro plugin is active. Default is false. */
        public bool $acf_pro = false;

        /** @var array Information about registered OES post types. */
        public array $post_types = [];

        /** @var array Information about registered OES taxonomies. */
        public array $taxonomies = [];

        /** @var array The configurations for media. */
        public array $media_groups = [];

        /** @var array The OES Core and OES Project admin pages. See class 'Menu_Page'. */
        public array $admin_pages = [];

        /** @var array Languages for multilingual posts. Default is english. */
        public array $languages = ['language0' => ['label' => 'English', 'abb' => 'ENG', 'locale' => 'en_BE']];

        /** @var array Registered tools. */
        public array $admin_tools = [];

        /** @var array API configurations. */
        public array $apis = [];

        /** @var string|int|bool The post ID of the OES object holding general configuration information. */
        public $config_post = false;

        /** @var array|bool Information for the theme index pages. */
        public $theme_index_pages = false;

        /** @var array General theme labels. */
        public array $theme_labels = [];

        /** @var array Search configuration. */
        public array $search = [];


        /**
         * OES_Core constructor.
         * Check if ACF plugin is activated and set basename.
         *
         * @param array $args Additional arguments.
         * @param string $projectPath The absolute path of the project plugin.
         */
        function __construct(array $args = [], string $projectPath = '')
        {
            /* check if acf plugin exists */
            if (!class_exists('ACF')) {
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                        __('The ACF plugin is not active.', 'oes') . '</p></div>';
                });
                $this->initialized = false;
            } else {

                /* check acf version */
                if(!function_exists('acf_determine_internal_post_type')){
                    add_action('admin_notices', function () {
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                        __('The ACF plugin might not be up to date, ACF version should be at least 6.2.0.', 'oes') .
                        '</p></div>';
                });}

                /* check if acf pro plugin exists */
                if (class_exists('acf_pro')) $this->acf_pro = true;

                /* check if legacy plugin is active */
                if (function_exists('oes_legacy')) $this->legacy = true;

                /* check if plugin uses block theme */
                $this->block_theme = wp_get_theme()->is_block_theme();

                /* check for additional parameters */
                foreach ($args as $propertyKey => $value)
                    if (property_exists($this, $propertyKey)) $this->$propertyKey = $value;

                $this->define_constants($projectPath);
            }
        }


        /**
         * Define global constants.
         *
         * @param string $projectPath The absolute path of the project plugin.
         * @return void
         */
        function define_constants(string $projectPath = ''): void
        {
            define('OES_CORE_PLUGIN', __DIR__);
            define('OES_BASENAME', basename(__DIR__));
            define('OES_ACF_PRO', class_exists('acf_pro'));

            if (!empty($projectPath)) {
                define('OES_PROJECT_PLUGIN', $projectPath);
                define('OES_BASENAME_PROJECT', basename($projectPath));
            }
        }


        /**
         * OES initializing of the OES Core plugin functionalities and features.
         */
        function initialize_core(): void
        {

            /* get options where feature options are stored */
            $features = get_option('oes_features');
            if ($features) $features = json_decode($features, true);


            /** Include functionalities for OES Core plugin processing. ------------------------------------------------
             * This includes functions that are used throughout the OES Core plugin and the Project plugin.
             * Especially include the function 'oes_include'.
             */
            require(OES_CORE_PLUGIN . '/includes/functions-utility.php');
            oes_include('functions-field.php');
            oes_include('functions-html.php');
            oes_include('functions-post.php');
            oes_include('functions-text-processing.php');


            /** Create database tables for OES -------------------------------------------------------------------------
             * This enables the feature "Operations" where data from a csv-file is transformed to OES operations that
             * can be evaluated before importing as posts, tags, etc..
             */
            oes_include('admin/db/initialize-db.php');
            oes_include('admin/db/class-operation.php');
            oes_include('admin/db/functions-operation.php');


            /** Include tools ------------------------------------------------------------------------------------------
             * This enables the feature "Tools". There are OES tools to configure the data model and data behaviour
             * via the admin panel, the im- and export tool, the tool to administrate the OES features,
             * and the general tool to create the OES data model.
             */
            oes_include('admin/tools/class-tool.php');
            oes_include('admin/tools/functions-tool.php');
            oes_include('admin/functions-shortcode.php');
            add_action('admin_init', '\OES\Admin\Tools\include_tools');


            /** Set up dashboard in the editorial layer ----------------------------------------------------------------
             * This enables the feature "Dashboard". The admin dashboard is modified for OES purposes like displaying
             * an OES welcome message. @oesDevelopment
             */
            if (!$features || ($features['dashboard'] ?? false)) {
                oes_include('admin/functions-dashboard.php');
                add_action('wp_dashboard_setup', '\OES\Dashboard\modify');
            }


            /** Include messaging to display admin notices in the editorial layer --------------------------------------
             * This enables the feature "Admin Messages". It includes messaging to display admin notices in the
             * editorial layer. @oesDevelopment
             */
            oes_include('admin/functions-notices.php');
            add_action('admin_notices', 'OES\Admin\display_oes_notices_after_refresh', 12);


            /** Include admin functionalities --------------------------------------------------------------------------
             * This enables functionalities for the editorial layer. Including the select2-Classes, the modification
             * of the page icon etc.
             */
            oes_include('admin/functions-admin.php');
            oes_include('admin/functions-acf.php');
            add_action('admin_head', '\OES\Admin\set_page_icon');
            add_filter('admin_body_class', '\OES\Admin\set_oes_body_class');


            /** Include admin pages inside the editorial layer ---------------------------------------------------------
             * This enables the feature "Admin Pages". It allows for admin pages inside the editorial layer and the
             * functionalities on these pages, e.g. settings options or container pages.
             */
            oes_include('admin/pages/class-page.php');
            oes_include('admin/pages/class-subpage.php');
            oes_include('admin/pages/class-container.php');
            oes_include('admin/pages/functions-pages.php');
            add_action('admin_enqueue_scripts', 'OES\Admin\add_page_scripts');
            add_action('oes/data_model_registered', 'OES\Admin\initialize_admin_menu_pages');
            add_action('oes/data_model_registered', '\OES\Admin\initialize_container_pages');


            /** Include assets -----------------------------------------------------------------------------------------
             * This enqueues all css and js needed inside the editorial layer for this OES Core plugin.
             * @oesDevelopment Unify enqueue with action calls.
             */
            oes_include('admin/functions-assets.php');
            add_action('wp_enqueue_scripts', 'oes_register_scripts_and_styles');
            add_action('admin_enqueue_scripts', 'oes_load_assets');
            oes_add_style_admin('oes-admin', '/assets/css/admin.css');
            oes_add_style('oes-theme', '/assets/css/theme.css');


            /** Include modification of columns for post types lists inside the editorial layer. -----------------------
             * This enables the feature "Admin Columns". It includes modification of columns for post types lists
             * inside the editorial layer.
             */
            oes_include('admin/functions-columns.php');
            add_action('restrict_manage_posts', '\OES\Admin\columns_filter_dropdown', 10, 2);
            add_action('pre_get_posts', '\OES\Admin\columns_pre_get_posts');


            /** Include the data model ---------------------------------------------------------------------------------
             * This enables the features "Data Model". It allows to modify the WordPress objects 'post type' and
             * 'taxonomy'. It creates custom post types and custom taxonomies according to the project data model file.
             * Additionally forms with fields for structured data are created.
             */
            oes_include('admin/functions-model.php');
            oes_include('admin/functions-schema.php');
            add_filter('init', '\OES\Model\register_model');


            /** Include the factory ------------------------------------------------------------------------------------
             * This enables the features "Factory". It allows to modify the OES data model via the editorial layer.
             */
            if (!$features || ($features['factory'] ?? false)) {
                oes_include('admin/functions-factory.php');
                if (get_option('oes_admin-factory_mode')) {
                    add_action('admin_notices', '\OES\Factory\display_factory_notice');
                    add_filter('acf/post_type/registration_args', '\OES\Factory\set_global_parameters', 10, 2);
                    add_filter('acf/taxonomy/registration_args', '\OES\Factory\set_global_parameters', 10, 2);
                    add_filter('acf/field_group/additional_field_settings_tabs', '\OES\Factory\additional_field_settings_tabs');
                    add_action('acf/field_group/render_field_settings_tab/oes', '\OES\Factory\render_field_settings_tab');
                    add_action('acf/field_group/render_field_settings_tab/oes_language', '\OES\Factory\render_field_settings_tab_language');
                }
            }


            /** Include calculated data values -------------------------------------------------------------------------
             * This enables the features "Formula". It allows to specify a pattern for fields, post title or post name.
             * The field value, post title or post name is computed according to the pattern. E.g. citation field.
             */
            oes_include('functions-formulas.php');
            add_action('acf/save_post', '\OES\Formula\calculate_post_args_from_formula');
            add_filter('acf/update_value', '\OES\Formula\calculate_field_value_from_formula', 10, 3);


            /** Include the versioning feature for OES post types ------------------------------------------------------
             * This enables the feature "Versioning". If part of the data model it creates a version controlling post
             * type "Parent" and a version controlled post type "Version" that include specific fields, e.g. a field for
             * a version number.
             *
             * The feature "Translating" is a special case of the feature "Versioning" where a version controlling post
             * "Origin Post" is linked to a post "Translation" of the same post type.
             *
             */
            oes_include('functions-versioning.php');
            add_filter('get_sample_permalink_html', 'OES\Versioning\get_sample_permalink_html', 10, 2);
            add_action('add_meta_boxes', 'OES\Versioning\add_meta_boxes');
            add_action('save_post', 'OES\Versioning\save_post', 10, 2);
            add_action('admin_action_oes_copy_version', 'OES\Versioning\admin_action_oes_copy_version');
            add_action('admin_action_oes_create_version', 'OES\Versioning\admin_action_oes_create_version');
            add_action('admin_action_oes_create_translation', 'OES\Versioning\admin_action_oes_create_translation');


            /** Include popup ------------------------------------------------------------------------------------------
             * This enables the features "Popup". The OES popup is an editor tool where text can be marked to be
             * displayed as a popup.
             */
            oes_include('popup/class-popups.php');
            oes_add_style('oes-popup', '/includes/popup/assets/popup.css');
            oes_add_script('oes-popup', '/includes/popup/assets/popup.js');
            oes_add_script_admin('oes-admin-popup', '/assets/js/admin-popup.min.js', ['jquery']);
            oes_add_script_admin('oes-popup-admin', '/includes/popup/assets/popup-admin.js',
                ['wp-rich-text', 'wp-element', 'wp-editor']);
            oes_include('popup/functions-popup.php');
            add_filter('the_content', '\OES\Popup\render_for_frontend');


            /** Include notes ------------------------------------------------------------------------------------------
             * This enables the features "Notes". Notes is a specific form of popup. It is an editor tool where text can
             * be marked as a note. The note will be rendered as a popup and linked to a computed number. It allows the
             * notes block to create a list of all existing notes in a text.
             */
            oes_add_style('oes-note', '/includes/popup/assets/note.css');
            oes_add_script('oes-note', '/includes/popup/assets/note.js');
            oes_add_script_admin('oes-note-admin', '/includes/popup/assets/note-admin.js',
                ['wp-rich-text', 'wp-element', 'wp-editor']);
            oes_include('popup/functions-note.php');
            add_filter('oes/popups_render_for_frontend', '\OES\Popup\add_notes_replace_variable');


            /** Include OES Core blocks for the Gutenberg editor. ------------------------------------------------------
             * This enables the feature "Blocks". OES blocks can be used to display the OES modified posts or taxonomies
             * in the frontend. It also includes blocks for e.g. featured posts, a print button or the language switch.
             */
            oes_add_style('oes-blocks', '/includes/blocks/blocks.css', [], null, 'all', true);
            oes_include('blocks/functions-blocks.php');
            add_filter('block_categories_all', '\OES\Block\category');
            add_action('enqueue_block_assets', '\OES\Block\assets', 1);


            /** Include theme classes and functions --------------------------------------------------------------------
             * This enables the feature "Data Processing". It modifies the WordPress display for e.g. pages, posts and
             * taxonomies by adding OES data, e.g. metadata for posts, version information, dropdown data in list
             * displays etc.
             */
            oes_include('theme/functions-theme.php');
            oes_include('theme/data/functions-data.php');
            oes_include('theme/data/functions-parts.php');
            add_filter('init', 'oes_set_language_cookie', 999);
            add_filter('the_content', 'oes_the_content', 12, 1);
            add_filter('render_block_core/heading', 'oes_render_block_core_heading', 10, 2);
            oes_include('theme/data/class-object.php');
            oes_include('theme/data/class-post.php');
            oes_include('theme/data/class-page.php');
            oes_include('theme/data/class-attachment.php');
            oes_include('theme/data/class-taxonomy.php');
            oes_include('theme/data/class-archive.php');
            oes_include('theme/data/class-post_archive.php');
            oes_include('theme/data/class-taxonomy_archive.php');
            oes_include('theme/data/class-index_archive.php');
            add_action('template_redirect', 'oes_prepare_data');
            add_filter('body_class', 'oes_body_class');

            /** Include figures ----------------------------------------------------------------------------------------
             * This enables the feature "Figures". It modifies the display of images and galleries by adding a lightbox
             * and additional caption text.
             */
            if (!$features || ($features['figures'] ?? false)) {
                oes_include('theme/figures/functions-figures.php');
                oes_include('theme/figures/functions-panel.php');
                oes_include('theme/figures/class-panel.php');
                oes_include('theme/figures/class-gallery_panel.php');
                oes_include('theme/figures/class-image_panel.php');
                oes_add_style('oes-panel', '/includes/theme/figures/panel.css');
                oes_add_script('oes-figures', '/includes/theme/figures/figures.min.js', ['jquery']);
            }


            /** Include filter -----------------------------------------------------------------------------------------
             * This enables the feature "Filter". It creates filter that can be used in list displays of posts in the
             * frontend.
             */
            oes_include('theme/filter/functions-filter.php');
            oes_add_script('oes-filter', '/includes/theme/filter/filter.min.js');


            /** Include label ------------------------------------------------------------------------------------------
             * This include a function to retrieve labels.
             */
            oes_include('theme/functions-labels.php');


            /** Include language switch --------------------------------------------------------------------------------
             * This enables the feature "Language Switch". The language switch can be used for projects that include
             * two or more languages. The switch allows to change the website language.
             */
            oes_include('theme/navigation/class-language_switch.php');
            oes_include('theme/navigation/functions-navigation.php');
            add_filter('404_template_hierarchy', '\OES\Navigation\redirect_page');
            add_filter('page_template_hierarchy', '\OES\Navigation\redirect_page');
            add_filter('frontpage_template_hierarchy', '\OES\Navigation\redirect_page');
            add_filter('single_template_hierarchy', '\OES\Navigation\redirect_page');
            add_filter('archive_template_hierarchy', '\OES\Navigation\redirect_page');
            if(!$this->block_theme){
                oes_include('theme/navigation/functions-classic_menu.php');
                add_action('admin_head-customize.php', '\OES\Navigation\admin_head_nav_menus');
                add_action('admin_head-nav-menus.php', '\OES\Navigation\admin_head_nav_menus');
                add_action('init', '\OES\Navigation\init');
                add_filter('nav_menu_item_title', '\OES\Navigation\nav_menu_item_title', 10, 2);
                add_filter('nav_menu_link_attributes', '\OES\Navigation\nav_menu_link_attributes', 10, 2);
                add_filter('nav_menu_css_class', '\OES\Navigation\nav_menu_css_class', 10, 2);
            }


            /** Include search modification ----------------------------------------------------------------------------
             * This enables the feature "Search". It modifies the WordPress search and the display of the results.
             */
            if (!$features || ($features['search'] ?? false)) oes_include('theme/search/functions-search.php');
            oes_include('theme/search/class-search.php');


            /** Include LOD API ----------------------------------------------------------------------------------------
             * This enables the featured "Linked Open Data". It allows to search for linked open data like GND or
             * Geonames. The results can be used as shortcodes or data can be copied to post objects.
             */
            if (!$features || ($features['lod_apis'] ?? false)) {
                oes_include('api/functions-rest_api.php');
                oes_include('api/class-rest_api.php');
                add_action('admin_enqueue_scripts', '\OES\API\admin_scripts');
                add_action('wp_enqueue_scripts', '\OES\API\scripts');
                add_action('enqueue_block_editor_assets', '\OES\API\sidebar_enqueue');
                add_action('oes/initialized', '\OES\API\initialize');
                add_action('wp_ajax_oes_lod_search_query', '\OES\API\lod_search_query');
                add_action('wp_ajax_oes_lod_add_post_meta', '\OES\API\lod_add_post_meta');
                add_action('wp_ajax_oes_lod_box', '\OES\API\lod_box');
                add_action('wp_ajax_nopriv_oes_lod_box', '\OES\API\lod_box');
            }


            /** Include admin manual -----------------------------------------------------------------------------------
             * This enables the featured "Admin Manual". It allows to create internal manual pages.
             */
            if (!$features || ($features['manual'] ?? false)) {
                include_once(__DIR__ . '/includes/admin/functions-manual.php');
                add_action('init', '\OES\Manual\register');
                add_filter('parent_file', '\OES\Manual\modify_parent_file');
            }


            /** Include admin tasks ------------------------------------------------------------------------------------
             * This enables the featured "Tasks". It allows to create and administrate internal tasks.
             */
            if (!$features || ($features['task'] ?? false)) {
                include_once(__DIR__ . '/includes/admin/functions-tasks.php');
                add_action('init', '\OES\Tasks\init');
                add_action('manage_oes_task_posts_custom_column', '\OES\Tasks\posts_custom_column', 10, 2);
                add_filter('get_sample_permalink_html', '\OES\Tasks\hide_permalink', 10, 2);
                add_filter('acf/update_value/key=field_oes_task__date', '\OES\Tasks\update_value');
                add_filter('manage_oes_task_posts_columns', '\OES\Tasks\posts_columns');
                add_action('manage_edit-oes_task_sortable_columns', '\OES\Tasks\sortable_columns');
            }


            /* Include shortcodes */
            oes_include('shortcodes.php');


            /**
             * Fires after OES Plugin has been initialized.
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
            /* get general config post */
            $generalConfigPost = get_posts([
                    'post_type' => 'oes_object',
                    'posts_per_page' => -1,
                    'name' => 'oes_config',
                    'post_status' => 'publish'
                ]
            );

            /* set general configs from found post */
            if (!empty($generalConfigPost)) {

                /* set config post parameter */
                $this->config_post = $generalConfigPost[0]->ID ?? false;

                /* get content */
                $generalConfigContent = json_decode(
                    get_post($generalConfigPost[0]->ID)->post_content ?? '{}',
                    true);

                /* check for languages */
                if (isset($generalConfigContent['languages']))
                    foreach ($generalConfigContent['languages'] as $languageKey => $language)
                        $this->languages[(oes_starts_with($languageKey, 'language') ? '' : 'language') . $languageKey] =
                            array_merge(
                                [
                                    'label' => 'Label missing',
                                    'abb' => $languageKey
                                ],
                                $language
                            );

                /* check for container */
                if (isset($generalConfigContent['container']))
                    foreach ($generalConfigContent['container'] as $containerID => $container)
                        $this->admin_pages['container'][$containerID] = $container;
            }

            /* include user roles */
            oes_include('admin/functions-rights.php');
            add_action('init', '\OES\Rights\user_roles');
        }


        /**
         * Add processing after data model is registered.
         *
         * @return void
         */
        function data_model_registered(): void
        {

            /* register blocks */
            \OES\Block\register_block_styles();
            \OES\Block\register();

            /* prepare filter for column display, sorting and filtering.*/
            foreach ($this->post_types as $postType => $postTypeConfiguration)
                if (isset($postTypeConfiguration['admin_columns'])) {
                    add_filter('manage_' . $postType . '_posts_columns', '\OES\Admin\add_post_column');
                    add_action('manage_' . $postType . '_posts_custom_column', '\OES\Admin\display_post_column_value', 10, 2);
                    add_filter('manage_edit-' . $postType . '_sortable_columns', '\OES\Admin\make_columns_sortable');
                }

            foreach ($this->taxonomies as $taxonomyKey => $taxonomyConfiguration) {
                if (isset($taxonomyConfiguration['admin_columns'])) {
                    add_filter('manage_edit-' . $taxonomyKey . '_columns', '\OES\Admin\add_post_column');
                    add_filter('manage_' . $taxonomyKey . '_custom_column', '\OES\Admin\display_taxonomy_column_value', 10, 3);
                    //@oesDevelopment Add feature for taxonomy, add_filter('manage_edit-' . $taxonomyKey . '_sortable_columns', 'OES\Admin\make_columns_sortable');
                }

                if(sizeof($this->languages) > 1 && ($taxonomyConfiguration['language_dependent'] ?? false)){
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
            if ($postID) $this->set_config_post($postID);
            foreach ([
                         'theme_index_pages',
                         'notes',
                         'theme_labels',
                         'search'
                     ] as $configKey)
                if (isset($config[$configKey])) $this->$configKey = $config[$configKey];
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
            if(!empty($postID)) $this->media_groups['post_ID'] = $postID;
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
            foreach (\OES\Model\get_taxonomy_oes_args_defaults() as $configKey => $default)
                $this->taxonomies[$taxonomyKey][$configKey] = $oesArgs[$configKey] ?? $default;

            /* validate label */
            if(empty($this->taxonomies[$taxonomyKey]['label']))
                $this->taxonomies[$taxonomyKey]['label'] = $taxonomyKey;
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
            /* parse defaults */
            foreach (\OES\Model\get_post_type_oes_args_defaults() as $configKey => $default)
                $this->post_types[$postTypeKey][$configKey] = $oesArgs[$configKey] ?? $default;

            /* validate label */
            if(empty($this->post_types[$postTypeKey]['label']))
                $this->post_types[$postTypeKey]['label'] = $registerArgs['label'] ?? $postTypeKey;

            /* additional parameters */
            foreach (['parent', 'version'] as $parameter)
                if (!empty($oesArgs[$parameter])) $this->post_types[$postTypeKey][$parameter] = $oesArgs[$parameter];

            /* content post type */
            $options = [
                'language' => 'none',
                'external' => []
            ]; //@oesDevelopment add more options.
            if ($this->post_types[$postTypeKey]['type'] == 'single-article')
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
                    'lod' => []
                ];
            elseif ($this->post_types[$postTypeKey]['type'] == 'single-contributor')
                $options = [
                    'vita' => 'none',
                    'publications' => 'none',
                    'language' => 'none',
                    'external' => []
                ];

            foreach ($options as $parameter => $default)
                $this->post_types[$postTypeKey][$parameter] = ((isset($oesArgs[$parameter]) && $oesArgs[$parameter]) ?
                    $oesArgs[$parameter] :
                    $default);


            /* add theme labels */
            $themeLabels = array_merge(
                [],
                $oesArgs['theme_labels'] ?? []
            );
            if (!empty($themeLabels)) $this->post_types[$postTypeKey]['theme_labels'] = $themeLabels;
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
            /* validate component */
            if ($component == 'taxonomy') $component = 'taxonomies';
            elseif ($component == 'post_type') $component = 'post_types';

            /* store additional parameter for fields in cache */
            $this->$component[$key]['acf_ids'][$postID] = $postID;

            foreach ($fields as $field) {

                /* add field parameters */
                $this->$component[$key]['field_options'][$field['key']]['label'] = $field['label'];
                $this->$component[$key]['field_options'][$field['key']]['type'] = $field['type'];

                /* add translations */
                if (!empty($this->languages))
                    foreach ($this->languages as $languageKey => $language) {
                        $this->$component[$key]['field_options'][$field['key']]['label_translation_' . $languageKey] =
                            $field['label_translation_' . $languageKey] ?? $field['label'];
                    }

                /* check if field option */
                foreach ([
                             'pattern' => [],
                             'language_dependent' => false,
                             'display_option' => 'none',
                             'display_prefix' => ''] as $optionKey => $option)
                    $this->$component[$key]['field_options'][$field['key']][$optionKey] = $field[$optionKey] ?? $option;

                /* add api information */
                if (!empty($this->apis))
                    foreach ($this->apis as $apiKey => $ignore)
                        if (isset($field[$apiKey . '_properties']))
                            $this->$component[$key]['field_options'][$field['key']][$apiKey . '_properties'] =
                                $field[$apiKey . '_properties'];
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

                /* add field parameters */
                $this->media_groups['fields'][$field['key']]['label'] = $field['label'];
                $this->media_groups['fields'][$field['key']]['type'] = $field['type'];

                /* add translations */
                if (!empty($this->languages))
                    foreach ($this->languages as $languageKey => $language) {
                        $this->media_groups['fields'][$field['key']]['label_translation_' . $languageKey] =
                            $field['label_translation_' . $languageKey] ?? $field['label'];
                    }

                /* check if field option */
                foreach ([
                             'pattern' => [],
                             'language_dependent' => false,
                             'display_option' => 'none',
                             'display_prefix' => ''] as $optionKey => $option)
                    $this->media_groups['fields'][$field['key']][$optionKey] = $field[$optionKey] ?? $option;
            }
        }
    }
endif;