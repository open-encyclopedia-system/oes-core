<?php

use function OES\Admin\get_features;
use OES\Features as Features;
use OES\DB as DB;
use OES\Service as Service;
use OES\Core as Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 *  Class OES_Core
 *
 *  Main class for the OES Core plugin initialization and configuration.
 * Lifecycle facade. Owns boot order and wires the services together; holds no configuration data itself.
 *
 * @property-read array $post_types        (compat, see OES_Core_Compat)
 * @property-read array $taxonomies
 * @property-read array $media_groups
 * @property-read array $languages
 * @property-read array $admin_pages
 */
class OES_Core
{
    use OES_Core_Compat;

    /** @var string The version of the OES Core plugin. */
    public string $version = OES_CORE_VERSION;

    /** @var bool Indicates whether the application is legacy (before OES Core 2.3). */
    public bool $legacy = false;

    /** @var bool Indicates whether the application uses a block theme (Full Site Editing theme). */
    public bool $block_theme = true;

    /** @var bool Whether the OES Core plugin was successfully initialized. */
    public bool $initialized = true;

    /** @var bool Whether an OES application plugin was successfully initialized. */
    public bool $application_initialized = false;

    /** @var bool Whether the OES data model was successfully initialized. */
    public bool $data_model_initialized = false;

    /** @var bool Whether the ACF Pro plugin is active. */
    public bool $acf_pro = false;

    // Private on purpose: external reads of ->languages, ->post_types etc. go through OES_Core_Compat::__get.
    private Service\Model $model;
    private Service\Languages $languages;
    private Service\Admin $admin;
    private Service\Theme $theme;
    private Service\Registry $apis;
    private Service\Registry $tools;
    private Service\Registry $modules;

    private Core\Cache $cache;

    /** @var array<string,string> Old name => new name (renamed properties). */
    private const RENAMED = [
        'project_initialized' => 'application_initialized',
    ];

    public function __construct(array $args = [])
    {
        if (!class_exists('ACF', false)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>' .
                    esc_html__('The ACF plugin is not active.', 'oes') . '</p></div>';
            });
            $this->initialized = false;
            return;
        }

        if (!function_exists('acf_determine_internal_post_type')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>' .
                    esc_html__('The ACF plugin might not be up to date. Required version is at least 6.2.0.', 'oes') .
                    '</p></div>';
            });
            return;
        }

        $this->model = new Service\Model();
        $this->languages = new Service\Languages();
        $this->admin = new Service\Admin();
        $this->theme = new Service\Theme();
        $this->apis = new Service\Registry();
        $this->tools = new Service\Registry();
        $this->modules = new Service\Registry();

        $this->cache = new Core\Cache($this->version);

        $this->acf_pro = class_exists('acf_pro', false);
        $this->legacy = function_exists('oes_legacy');
        $this->block_theme = wp_get_theme()->is_block_theme();
    }

    /**
     * Initializes the OES Core plugin functionalities and features.
     *
     * @return void
     */
    public function initialize_core(): void
    {
        /**
         * Include core plugin functionalities.
         * This includes functions that are used throughout both the OES Core plugin and the application plugin.
         * Specifically, it includes the function 'oes_include'.
         */
        require(OES_CORE_PLUGIN . '/includes/functions-utility.php');
        require(OES_CORE_PLUGIN . '/includes/functions-features.php');

        // Initialize OES Features
        Features\utility_functions();
        Features\database();
        Features\tools();
        Features\admin_messages();
        Features\admin_functions();

        // Retrieve feature options to check which features are enabled
        $features = get_features();
        $is_enabled = fn($key) => !$features || ($features[$key] ?? false);

        Features\dashboard();
        Features\site_health();
        Features\admin_pages();
        Features\assets();
        Features\columns();
        Features\user_profile_settings();
        Features\user_rights();

        Features\remarks($is_enabled('remarks'));
        Features\guidelines($is_enabled('manual'));
        Features\tasks($is_enabled('task'));

        Features\data_model();
        Features\data_model_factory();
        Features\formula_functions();
        Features\versioning();
        Features\cache($is_enabled('cache'));
        Features\lod_apis($is_enabled('lod_apis'));

        Features\icons();
        Features\popup();
        Features\notes();
        Features\blocks();
        Features\theme_classes();
        Features\figures();
        Features\filter();
        Features\labels();
        Features\navigation(!$this->block_theme);
        Features\search();
        Features\export();
        Features\shortcodes();

        /**
         * Fires after the OES Plugin has been fully initialized.
         */
        do_action('oes/initialized');

        add_action('oes/data_model_registered', [$this, 'data_model_registered']);
    }

    /**
     * Initialize the OES application plugin, including data model and application processing.
     *
     * @param string $applicationPath The absolute path to the application plugin, if any.
     *
     * @return void
     */
    public function initialize_application(string $applicationPath = ''): void
    {
        if ($applicationPath !== '') {
            defined('OES_APPLICATION_PLUGIN') || define('OES_APPLICATION_PLUGIN', $applicationPath);
            defined('OES_BASENAME_APPLICATION') || define('OES_BASENAME_APPLICATION', basename($applicationPath));

            /**
             * @deprecated 3.0.0 Use OES_APPLICATION_PLUGIN instead.
             */
            define('OES_PROJECT_PLUGIN', $applicationPath);

            /**
             * @deprecated 3.0.0 Use OES_BASENAME_APPLICATION instead.
             */
            defined('OES_BASENAME_PROJECT') || define('OES_BASENAME_PROJECT', basename($applicationPath));
        }

        $this->application_initialized = true;

        /**
         * Fires after the OES Application Plugin has been fully initialized.
         */
        do_action('oes/application_initialized');
    }

    /**
     * @deprecated 3.0.0 Use OES::initialize_application() instead.
     */
    public function initialize_project(string $applicationPath = ''): void
    {
        _deprecated_function(__METHOD__, '3.0.0', 'OES_Core::initialize_application()');
        $this->initialize_application($applicationPath);
    }

    /* -----------------------------------------------------------------------------------------------------------------
     * Data model loading (called once on 'init' priority 5)
     * -------------------------------------------------------------------------------------------------------------- */
    public function load_data_model(): void
    {
        if ($this->data_model_initialized || !$this->initialized || !$this->application_initialized) {
            return;
        }

        $this->data_model_initialized = true;

        $this->cache->register_invalidation_hooks();

        try {
            $loader = new Core\Loader($this->cache, [
                'model' => $this->model,
                'languages' => $this->languages,
                'admin' => $this->admin,
                'theme' => $this->theme
            ]);
            $loader->load(fn() => $this->build_from_config());

        } catch (Throwable $e) {
            if (is_admin()) {
                add_action('admin_notices', function () use ($e) {
                    echo '<div class="notice notice-warning is-dismissible"><p>' .
                        esc_html__('The OES Core Plugin could not be initialized for this application.', 'oes') .
                        '</p><p>' . esc_html($e->getMessage()) . '</p></div>';
                });
                oes_write_log('The OES Core Plugin could not be initialized for this application.');
            }
        }

        do_action('oes/data_model_loaded', $this);
    }

    /** Runs ONLY on a cache miss. */
    private function build_from_config(): void
    {
        $result = Core\Reader::read();

        if ($result !== null) {
            $config = $result['config'];
            $this->theme->set_config_post($result['post_id']);
            $this->theme->load($config);
            $this->languages->load($config['languages'] ?? null);
            $this->admin->load_containers($config['container'] ?? null);
            $this->admin->load_separators($config['separators'] ?? null);
            $this->admin->load_include_menu($config['include_menu'] ?? null);
        }

        /**
         * Everything that currently calls set_post_type_parameters(), set_taxonomy_parameters(),
         * set_field_options() etc. must hook in here, so it only runs when the cache is rebuilt.
         */
        do_action('oes/build_data_model', $this);
    }

    /* -----------------------------------------------------------------------------------------------------------------
     * Service access
     * -------------------------------------------------------------------------------------------------------------- */

    public function model(): Service\Model
    {
        return $this->model;
    }

    public function post_types(): array
    {
        return $this->model->post_types();
    }

    public function taxonomies(): array
    {
        return $this->model->taxonomies();
    }

    public function media_groups(): array
    {
        return $this->model->media_groups();
    }

    public function languages(): Service\Languages
    {
        return $this->languages;
    }

    public function admin(): Service\Admin
    {
        return $this->admin;
    }

    public function tools(): Service\Registry
    {
        return $this->tools;
    }

    public function apis(): Service\Registry
    {
        return $this->apis;
    }

    public function theme(): Service\Theme
    {
        return $this->theme;
    }

    public function modules(): Service\Registry
    {
        return $this->modules;
    }

    public function cache(): Core\Cache
    {
        return $this->cache;
    }

    /**
     * Returns the plugin version or null if it doesn't exist.
     *
     * @return string Returns the version.
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /* -----------------------------------------------------------------------------------------------------------------
     * Thin delegates for existing callers (keep signatures, forward to services) TODO
     * -------------------------------------------------------------------------------------------------------------- */

    public function set_post_type_parameters(string $key, array $oesArgs, array $registerArgs = []): void
    {
        $this->model->set_post_type($key, $oesArgs, $registerArgs);
    }

    public function set_taxonomy_parameters(string $key, array $oesArgs): void
    {
        $this->model->set_taxonomy($key, $oesArgs);
    }

    public function set_field_options(string $component, string $key, array $fields, $postID): void
    {
        $this->model->set_field_options(
            $component, $key, $fields, $postID, $this->languages->keys(), $this->apis()->keys()
        );
    }

    public function set_media_parameters(array $oesArgs, $postID = null): void
    {
        $this->model->set_media_parameters($oesArgs, $postID);
    }

    public function set_media_field_options(array $fields, $postID): void
    {
        $this->model->set_media_field_options($fields, $postID, $this->languages->keys());
    }

    public function set_general_parameters(array $config, $postID = null): void
    {
        if ($postID) {
            $this->theme->set_config_post($postID);
        }
        $this->theme->load($config);
    }

    /**
     * Set the config post ID linking the OES object post storing the general configuration to the OES instance.
     *
     * @param mixed $postID The OES object post ID storing the general configuration.
     *
     * @return void
     */
    public function set_config_post($postID): void
    {
        $this->theme->set_config_post($postID);
    }

    /**
     * Add processing after the data model is registered.
     *
     * @return void
     */
    public function data_model_registered(): void
    {
        \OES\Block\register_block_styles();
        \OES\Block\register();

        foreach ($this->model->post_types() as $postType => $postTypeConfiguration) {
            if (isset($postTypeConfiguration['admin_columns'])) {
                add_filter('manage_' . $postType . '_posts_columns', '\OES\Admin\add_post_column');
                add_action('manage_' . $postType . '_posts_custom_column', '\OES\Admin\display_post_column_value', 10, 2);
                add_filter('manage_edit-' . $postType . '_sortable_columns', '\OES\Admin\make_columns_sortable');
            }
        }

        foreach ($this->model->taxonomies() as $taxonomyKey => $taxonomyConfiguration) {
            if (isset($taxonomyConfiguration['admin_columns'])) {
                add_filter('manage_edit-' . $taxonomyKey . '_columns', '\OES\Admin\add_post_column');
                add_filter('manage_' . $taxonomyKey . '_custom_column', '\OES\Admin\display_taxonomy_column_value', 10, 3);
            }

            if ($this->languages->has_multiple() && ($taxonomyConfiguration['language_dependent'] ?? false)) {
                add_action($taxonomyKey . '_edit_form_fields', '\OES\Model\term_add_fields_for_multilingualism', 10, 2);
                add_action('edited_' . $taxonomyKey, '\OES\Model\term_save_fields_for_multilingualism', 10, 2);
            }
        }
    }

    /**
     * Check plugin version and run upgrade routines if needed.
     *
     * @return void
     */
    public function maybe_upgrade(): void
    {
        $installed = get_option('oes_db_version');

        if (!$installed || version_compare($installed, OES_DB_VERSION, '<')) {
            $this->install_db();
        }
    }

    /**
     * Perform database installation.
     *
     * @return void
     */
    public function install_db(): void
    {
        DB\install_operation_table();
        DB\install_cache_table();

        update_option('oes_db_version', OES_DB_VERSION);
    }
}
