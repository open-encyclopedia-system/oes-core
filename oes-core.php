<?php

/**
 * Plugin Name: OES Core
 * Plugin URI: http://www.open-encyclopedia-system.org/
 * Description: Building and maintaining online encyclopedias.
 * Version: 2.0
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
     *
     * @return OES_Core Returns the OES plugin instance.
     */
    function OES($projectPath = false): OES_Core
    {
        global $oes;

        /* initialize and return the global instance. */
        if (!isset($oes)) {

            $oes = new OES_Core();

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
        public string $version = '2.0';

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

        /* @var array Information about registered OES taxonomies. */
        public array $taxonomies = [];

        /* @var array The acf group IDs for media types. */
        public array $media_groups = [];

        /** @var bool Indicating if admin filter included. */
        public bool $admin_filter = true;

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
        public array $languages = ['language0' => ['label' => 'English']];

        /** @var string The website main language (e.g. for navigation elements) */
        public string $main_language = 'language0';

        /** @var array Registered tools. */
        public array $admin_tools = [];

        /** @var array API configurations */
        public array $apis = [];

        /** @var array Notes configurations */
        public array $notes = [];

        /** @var bool The admin manual feature. Include feature if true. */
        public bool $manual = true;

        /** @var bool The task feature. Include feature if true. */
        public bool $tasks = true;

        /* @var string|int|bool The post ID of the OES object holding general configuration information. */
        public $config_post = false;

        /* @var array|bool Information for the theme page 'Index'. */
        public $theme_index = false;

        /* @var array General theme labels. */
        public array $theme_labels = [];

        /* @var array Search configuration. */
        public array $search = [];

        /** @var bool The dashboard feature. Include oes feature if true */
        public bool $oes_dashboard = true;


        /**
         * OES_Core constructor.
         * Check if ACF plugin is activated and set basename.
         */
        function __construct()
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
            }
        }


        /**
         * OES Initializing of the OES Core plugin functionalities and features.
         */
        function initialize_core()
        {
            /** Include functionalities for OES Core plugin processing. ------------------------------------------------
             * This will include functions that are used throughout the OES Core plugin and the Project plugin.
             * Especially include the function 'oes_include'.
             */
            require($this->path_core_plugin . '/includes/functions-utility.php');
            oes_include('/includes/functions-text-processing.php');
            oes_include('/includes/functions-post.php');
            oes_include('/includes/functions-html.php');

            /** Set up dashboard in the editorial layer --------------------------------------------------------------*/
            if($this->oes_dashboard) oes_include('/includes/admin/dashboard.php');

            /** Include messaging to display admin notices in the editorial layer --------------------------------------
             * This will include messaging to display admin notices in the editorial layer.
             */
            oes_include('/includes/admin/notices.php');

            /** Include admin manual feature -------------------------------------------------------------------------*/
            if($this->manual) oes_include('/includes/admin/manual.php');

            /** Include task feature ---------------------------------------------------------------------------------*/
            if($this->tasks) oes_include('/includes/admin/tasks.php');

            /** Include assets. Sets $this->assets. --------------------------------------------------------------------
             * This will include all css and js needed inside the editorial layer for this OES Core plugin.
             */
            oes_include('/includes/admin/assets.class.php');

            /** Include modification of columns for post types lists inside the editorial layer. -----------------------
             * This will include modification of columns for post types lists inside the editorial layer.
             */
            if($this->admin_filter) oes_include('/includes/admin/columns.php');

            /** Include admin pages inside the editorial layer ---------------------------------------------------------
             * This will include admin pages inside the editorial layer for this OES Core plugin and the functionalities
             * on these pages, e.g. settings options inside the editorial layer.
             */
            oes_include('/includes/admin/pages/page.class.php');

            /* Include menu container pages. */
            oes_include('/includes/admin/pages/container.class.php');

            /** Initialize acf dependencies --------------------------------------------------------------------------*/
            oes_include('/includes/acf/acf.php');
            oes_include('/includes/acf/inheritance.class.php');

            /** Include the feature 'OES Posts' which generates custom post types according to the OES Project data
             * model. ------------------------------------------------------------------------------------------------*/
            oes_include('/includes/datamodel/datamodel.class.php');

            /** Include the versioning feature for OES post types. ---------------------------------------------------*/
            oes_include('/includes/versioning/versioning.php');

            /** Include note feature. --------------------------------------------------------------------------------*/
            oes_include('/includes/notes/notes.php');

            /** Include OES Core and OES Project blocks for the Gutenberg editor. ------------------------------------*/
            oes_include('/includes/blocks/blocks.php');

            /** Include theme classes ans functions. -----------------------------------------------------------------*/
            oes_include('/includes/theme/object.class.php');
            oes_include('/includes/theme/post.class.php');
            oes_include('/includes/theme/taxonomy.class.php');
            oes_include('/includes/theme/archive.class.php');
            oes_include('/includes/theme/search.class.php');
            oes_include('/includes/theme/functions-theme.php');
            oes_include('/includes/theme/navigation.php');
            oes_include('/includes/theme/figures.php');

            /** Include LOD APIs. ------------------------------------------------------------------------------------*/
            oes_include('/includes/api/rest-api.class.php');


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
        function initialize_project()
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
            if(!empty($generalConfigPost)){

                /* set config post parameter */
                $this->config_post = $generalConfigPost[0]->ID ?? false;

                /* get content */
                $generalConfigContent = json_decode(get_post($generalConfigPost[0]->ID)->post_content ?? '{}', true);

                /* check for languages */
                if (isset($generalConfigContent['languages']))
                    foreach ($generalConfigContent['languages'] as $languageKey => $language)
                        $this->languages[
                            (oes_starts_with($languageKey, 'language') ? '' : 'language') . $languageKey] =
                            $language;

                /* check for container */
                if (isset($generalConfigContent['container']))
                    foreach ($generalConfigContent['container'] as $containerID => $container)
                        $this->admin_pages['container'][$containerID] = $container;
            }


            /* initialize data model */
            if (isset($this->path_data_model)) {
                $dataModel = new OES\Datamodel();
                $dataModel->create_datamodel($this->path_data_model, $_POST['reload_json'] ?? false);
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
            oes_include('/includes/admin/rights.php');
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


/**
 * Add favicon to WordPress admin pages.
 */
add_action('admin_head', function () {
    echo '<link rel="icon" type="image/x-icon" href="' . plugin_dir_url(__FILE__) . 'assets/images/favicon.ico" />';
});