<?php

/**
 * Plugin Name: OES Core
 * Plugin URI: http://www.open-encyclopedia-system.org/
 * Description: Building and maintaining online encyclopedias.
 * Version: 2.2
 * Author: Maren Strobl, Freie Universität Berlin, Center für Digitale Systeme an der Universitätsbibliothek
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

use OES\Admin\Assets;


/** --------------------------------------------------------------------------------------------------------------------
 * The OES instance.
 * -------------------------------------------------------------------------------------------------------------------*/
if (!function_exists('OES')) {

    /**
     * The function returns the OES instance like a global variable everywhere inside the plugin.
     * It initializes the OES plugin if not yet initialized.
     *
     * @param bool|string $projectPath The path to the OES Project plugin.
     * @param array $args Additional arguments.
     *
     * @return OES_Core Returns the OES plugin instance.
     */
    function OES($projectPath = false, array $args = []): OES_Core
    {
        global $oes;

        /* initialize and return the global instance. */
        if (!isset($oes)) {

            $oes = new OES_Core($args);

            /* check if successful */
            if ($oes->initialized) {

                /* initialize the core */
                $oes->initialize_core();

                /* set global project variables */
                if ($projectPath) {
                    $oes->path_project_plugin = $projectPath;
                    $oes->basename_project = basename($projectPath);
                }
            }
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
        public string $version = '2.2';

        /** @var string The OES Database version. */
        public string $db_version = '1.0';

        /** @var bool The OES Core plugin was successfully initialized. False on error. */
        public bool $initialized = true;

        /** @var bool The ACF Pro plugin is active. Default is false. */
        public bool $acf_pro = false;

        /** @var array Store general errors. */
        public array $errors = [];

        /** @var string The basename of the OES Core plugin directory. Default is 'oes-core'. */
        public string $basename = 'oes-core';

        /** @var string The basename of the OES Project plugin directory. Default is empty. */
        public string $basename_project = '';

        /** @var string The path to the OES Core plugin directory. */
        public string $path_core_plugin = __DIR__;

        /** @var string The path to the OES Project plugin directory. */
        public string $path_project_plugin = '';

        /** @var array The path(s) to the OES Project plugin data model. */
        public array $path_data_model = [];

        /* @var array Information about registered OES post types. */
        public array $post_types = [];

        /* @var array Information about general OES fields. */
        public array $other_fields = [
            'field_oes_tab_editorial' => ['type' => 'tab', 'label' => 'Editorial'],
            'field_oes_status' => ['label' => 'Status', 'instructions' => 'Internal field for post status.'],
            'field_oes_comment' => ['label' => 'Comment', 'instructions' => 'Internal comment.']
        ];

        /* @var array Information about registered OES taxonomies. */
        public array $taxonomies = [];

        /* @var array The acf group IDs for media types. */
        public array $media_groups = [];

        /** @var array|Assets The OES Core and OES Project assets. See class 'Assets'. */
        public $assets = [];

        /** @var array The OES Core and OES Project admin pages. See class 'Menu_Page'. */
        public array $admin_pages = [];

        /** @var array The (static) pages for the theme. */
        public array $theme_pages = [];

        /** @var array The OES Core and OES Project blocks. See class 'Blocks'. */
        public array $blocks = [];

        /** @var array Project specific user roles. See class 'User_Role'. */
        public array $user_roles = [];

        /** @var array Project specific parameters. */
        public array $project_params = [];

        /** @var array Languages for multilingual posts. Default is english. */
        public array $languages = ['language0' => ['label' => 'English', 'abb' => 'ENG']];

        /** @var string The website main language (e.g. for navigation elements) */
        public string $main_language = 'language0';

        /** @var array Registered tools. */
        public array $admin_tools = [];

        /** @var array API configurations. */
        public array $apis = [];

        /** @var array Notes configurations. */
        public array $notes = [];

        /* @var string|int|bool The post ID of the OES object holding general configuration information. */
        public $config_post = false;

        /* @var array|bool Information for the theme index pages. */
        public $theme_index_pages = false;

        /* @var array General theme labels. */
        public array $theme_labels = [];

        /* @var array General theme options. */
        public array $theme_options = [];

        /* @var array Search configuration. */
        public array $search = [];

        /**
         * @var array|bool[] The included OES features.
         *
         * oes_dashboard    :   The dashboard feature. Include feature if true.
         * manual           :   The admin manual feature. Include feature if true.
         * tasks            :   The task feature. Include feature if true.
         * admin_filter     :   The admin filter feature. Include feature if true.
         * notes            :   The notes feature. Include feature if true.
         * blocks           :   The blocks feature. Include feature if true.
         * oes_theme        :   The theme classes feature. Include feature if true.
         * lod_apis         :   The Linked Open Data APIs feature. Include feature if true.
         * xml              :   The xml feature. Include feature if true.
         * user rights      :   The user rights feature. Include feature if true.
         */
        public array $included_features = [
            'oes_dashboard' => true,
            'manual' => true,
            'tasks' => true,
            'admin_filter' => true,
            'notes' => true,
            'blocks' => true,
            'oes_theme' => true,
            'lod_apis' => true,
            'xml' => true,
            'user_rights' => true
        ];


        /**
         * OES_Core constructor.
         * Check if ACF plugin is activated and set basename.
         *
         * @param array $args Additional arguments.
         */
        function __construct(array $args = [])
        {
            /* check if acf plugin exists */
            if (!class_exists('ACF')) {
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                        __('The ACF plugin is not active.', 'oes') . '</p></div>';
                });
                $this->initialized = false;
            } else {

                /* check if acf pro plugin exists */
                if (class_exists('acf_pro')) $this->acf_pro = true;

                /* set plugin base name */
                $this->basename = basename($this->path_core_plugin);

                /* check for additional parameters */
                if (!empty($args))
                    foreach ($args as $propertyKey => $value)
                        if ($propertyKey === 'included_features') {
                            if (is_array($value))
                                $this->included_features = array_merge($this->included_features, $value);
                        } elseif (property_exists($this, $propertyKey))
                            $this->$propertyKey = $value;
            }
        }


        /**
         * OES Initializing of the OES Core plugin functionalities and features.
         */
        function initialize_core(): void
        {
            /** Include functionalities for OES Core plugin processing. ------------------------------------------------
             * This will include functions that are used throughout the OES Core plugin and the Project plugin.
             * Especially include the function 'oes_include'.
             */
            require($this->path_core_plugin . '/includes/functions-utility.php');
            oes_include('/includes/functions-text-processing.php');
            oes_include('/includes/functions-post.php');
            oes_include('/includes/functions-html.php');


            /** Create database tables for OES -----------------------------------------------------------------------*/
            oes_include('/includes/admin/db/initialize-db.php');
            oes_include('/includes/admin/db/class-operation.php');
            oes_include('/includes/admin/db/functions-operation.php');


            /** Include tools ----------------------------------------------------------------------------------------*/
            oes_include('/includes/admin/tools/class-tool.php');
            oes_include('/includes/admin/tools/hooks-tool.php');
            oes_include('/includes/admin/tools/functions-tool.php');

            /** Set up dashboard in the editorial layer --------------------------------------------------------------*/
            if ($this->included_features['oes_dashboard']) oes_include('/includes/admin/dashboard/hooks-dashboard.php');

            /** Include messaging to display admin notices in the editorial layer --------------------------------------
             * This will include messaging to display admin notices in the editorial layer.
             */
            oes_include('/includes/admin/notices/functions-notices.php');
            oes_include('/includes/admin/notices/hooks-notices.php');

            /** Include admin pages inside the editorial layer ---------------------------------------------------------
             * This will include admin pages inside the editorial layer for this OES Core plugin and the functionalities
             * on these pages, e.g. settings options inside the editorial layer.
             */
            oes_include('/includes/admin/hooks-admin.php');
            oes_include('/includes/admin/pages/class-page.php');
            oes_include('/includes/admin/pages/class-container.php');
            oes_include('/includes/admin/pages/hooks-pages.php');

            /** Include admin manual feature -------------------------------------------------------------------------*/
            if ($this->included_features['manual']) {
                oes_include('/includes/admin/manual/functions-manual.php');
                oes_include('/includes/admin/manual/hooks-manual.php');
                oes_include('/includes/admin/manual/filters-manual.php');
            }

            /** Include task feature ---------------------------------------------------------------------------------*/
            if ($this->included_features['tasks']) {
                oes_include('/includes/admin/tasks/hooks-tasks.php');
                oes_include('/includes/admin/tasks/functions-tasks.php');
            }

            /** Include assets. Sets $this->assets. --------------------------------------------------------------------
             * This will include all css and js needed inside the editorial layer for this OES Core plugin.
             */
            oes_include('/includes/admin/assets/class-assets.php');

            /** Include modification of columns for post types lists inside the editorial layer. -----------------------
             * This will include modification of columns for post types lists inside the editorial layer.
             */
            if ($this->included_features['admin_filter']) {
                oes_include('/includes/admin/columns/hooks-columns.php');
                oes_include('/includes/admin/columns/functions-columns.php');
            }

            /** Initialize acf dependencies --------------------------------------------------------------------------*/
            oes_include('/includes/acf/functions-acf.php');
            oes_include('/includes/acf/fixes-acf.php');
            oes_include('/includes/acf/hooks-acf.php');
            oes_include('/includes/acf/class-acf_inheritance.php');

            /** Include the data model -------------------------------------------------------------------------------*/
            oes_include('/includes/model/class-model.php');
            oes_include('/includes/model/functions-model.php');

            /** Include multilingualism -------------------------------------------------------------------------------*/
            oes_include('/includes/multilingualism/hooks-multilingualism.php');

            /** Include the versioning feature for OES post types. ---------------------------------------------------*/
            oes_include('/includes/versioning/hooks-versioning.php');
            oes_include('/includes/versioning/functions-versioning.php');

            /** Include note feature. --------------------------------------------------------------------------------*/
            if ($this->included_features['notes']) {
                $this->assets->add_style('oes-notes', '/includes/notes/notes.css');
                $this->assets->add_script('oes-notes', '/includes/notes/notes.js');
                if(is_admin()) $this->assets->add_script('oes-notes-admin', '/includes/notes/notes-admin.js', ['wp-rich-text', 'wp-element', 'wp-editor', 'wp-i18n']);
                oes_include('/includes/notes/shortcodes-notes.php');
                oes_include('/includes/notes/hooks-notes.php');
            }

            /** Include OES Core and OES Project blocks for the Gutenberg editor. ------------------------------------*/
            if ($this->included_features['blocks']) {
                oes_include('/includes/blocks/functions-blocks.php');
                oes_include('/includes/blocks/hooks-blocks.php');
            }

            /** Include theme classes ans functions. -----------------------------------------------------------------*/
            if ($this->included_features['oes_theme']) {


                oes_include('/includes/admin/tools/cache/functions-cache.php');

                oes_include('/includes/theme/functions-theme.php');

                oes_include('/includes/theme/shortcodes-theme.php');

                oes_include('/includes/theme/data/functions-data.php');
                oes_include('/includes/theme/data/hooks-data.php');
                oes_include('/includes/theme/data/class-object.php');
                oes_include('/includes/theme/data/class-post.php');
                oes_include('/includes/theme/data/class-page.php');
                oes_include('/includes/theme/data/class-taxonomy.php');
                oes_include('/includes/theme/data/class-archive.php');

                oes_include('/includes/theme/figures/functions-figures.php');
                $this->assets->add_style('oes-figures', '/includes/theme/figures/figures.css');

                oes_include('/includes/theme/filter/shortcodes-filter.php');
                $this->assets->add_style('oes-filter', '/includes/theme/filter/filter.css');
                $this->assets->add_script('oes-filter', '/includes/theme/filter/filter.js');

                oes_include('/includes/theme/navigation/class-template_redirect.php');
                oes_include('/includes/theme/navigation/functions-multilingualism.php');
                oes_include('/includes/theme/navigation/hooks-navigation.php');
                oes_include('/includes/theme/navigation/class-language_switch.php');

                oes_include('/includes/theme/search/class-search.php');
                oes_include('/includes/theme/search/functions-search.php');
            }

            /** Include LOD APIs. ------------------------------------------------------------------------------------*/
            if ($this->included_features['lod_apis']) {
                oes_include('/includes/api/hooks-rest_api.php');
                oes_include('/includes/api/class-rest_api.php');
                $this->assets->add_style('oes-api', '/includes/api/api.css');
            }

            /** Include xml export -----------------------------------------------------------------------------------*/
            if ($this->included_features['xml']) {
                oes_include('/includes/export/functions-xml.php');
                oes_include('/includes/export/hooks-xml.php');
                oes_include('/includes/export/shortcodes-xml.php');
            }


            /**
             * Fires after OES Plugin has been initialized.
             */
            do_action('oes/initialized');
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
                $generalConfigContent = json_decode(get_post($generalConfigPost[0]->ID)->post_content ?? '{}', true);

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

                /* prepare other fields before data model registration */
                if (isset($generalConfigContent['other_fields']))
                    $this->other_fields = array_merge($this->other_fields, $generalConfigContent['other_fields']);
            }


            /* initialize data model */
            if(empty($this->path_data_model)) {
                $this->path_data_model = [$this->path_project_plugin . '/includes/model.json'];

                /* legacy */
                if(empty($this->path_data_model))
                    $this->path_data_model = [$this->path_project_plugin . '/includes/datamodel.json'];
            }
            if ($this->path_data_model) {
                $model = new OES\Model();
                $model->create_data_model($this->path_data_model, $_POST['reload_json'] ?? false);
            }

            /* include theme pages */
            if (!empty($this->theme_pages))
                foreach ($this->theme_pages as $page) {

                    /* skip if args not set */
                    if (!isset($page['args'])) continue;

                    /* initialize pages */
                    $newPage = oes_initialize_single_page($page['args']);

                    /* make front page if needed */
                    if (isset($page['front_page']) && $page['front_page']) {
                        if ($newPage && get_option('page_on_front') != $newPage->ID) {
                            update_option('page_on_front', $newPage->ID);
                            update_option('show_on_front', 'page');
                        }
                    }
                }

            /* include user roles */
            if ($this->included_features['user_rights']) {
                oes_include('/includes/admin/rights/hooks-rights.php');
                oes_include('/includes/admin/rights/functions-rights.php');
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
    }
endif;