<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Create container pages.
 * @return void
 */
function initialize_container_pages(): void
{
    global $oes;

    /* add container page for versioning posts */
    foreach ($oes->post_types ?? [] as $postTypeKey => $postType) {
        if (!empty($postType['parent'] ?? '') &&
            (($postType['parent'] ?? '') != 'none') &&
            !isset($oes->admin_pages['container'][$postTypeKey])) {
            $oes->admin_pages['container'][$postTypeKey] = prepare_container_page_args($postTypeKey);
        }
    }

    foreach ($oes->admin_pages['container'] ?? [] as $key => $page) {
        if (is_array($page) && !($page['hide'] ?? false)) {
            create_container($key, $page);
        }
    }
}


/**
 * Create a container admin page.
 *
 * @param string $key The page key.
 * @param array $args The container arguments.
 * @return void
 */
function create_container(string $key, array $args = []): void
{
    $args['key'] = $key;
    $class = $key . '_Container';
    class_exists($class) ? new $class($args) : new Container($args);
}

/**
 * Prepare the container arguments for a versioning post type.
 *
 * @param string $postTypeKey The post type key.
 * @return array
 */
function prepare_container_page_args(string $postTypeKey = ''): array
{
    $postType = OES()->post_types[$postTypeKey] ?? false;
    if (!$postType) return [];

    $elements = [
        $postType['parent'],
        $postTypeKey
    ];
    $subPages = $elements;
    foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomy) $subPages[] = $taxonomy;
    foreach (get_post_type_object($postType['parent'])->taxonomies ?? [] as $taxonomy) $subPages[] = $taxonomy;

    return [
        'page_parameters' => [
            'menu_title' => '[' . $postType['label'] . ']',
            'position' => '20',
            'subpages' => $subPages
        ],
        'info_page' => [
            'elements' => $elements,
            'label' => __('Recently worked on', 'oes')
        ],
        'generated' => true,
        'hide' => false
    ];
}

/**
 * Enqueue script for an admin page.
 * @param string $hook The page hook
 * @return void
 */
function add_page_scripts(string $hook): void
{

    $file = '/includes/admin/pages/js/';
    wp_register_script(
        'oes-admin_page',
        plugins_url(OES_BASENAME . $file . 'page.min.js'),
        ['jquery'],
        false,
        true);
    wp_localize_script(
        'oes-admin_page',
        'oesLanguages',
        OES()->languages ?? []
    );
    wp_enqueue_script('oes-admin_page');

    $file .= str_replace('-', '_', $hook) . '.min.js';
    if (file_exists(oes_get_path($file, OES_CORE_PLUGIN))) {
        wp_register_script(
            'oes-admin_' . $hook,
            plugins_url(OES_BASENAME . $file),
            ['jquery'],
            false,
            true);
        wp_enqueue_script('oes-admin_' . $hook);
    }

    if (str_starts_with($hook, 'oes-settings')) \OES\ACF\enqueue_select2();
}


/**
 * Initialize the OES Settings pages
 * @return void
 */
function initialize_admin_menu_pages(): void
{
    global $oes;

    $adminMenuPages['010_settings'] = [
        'page_parameters' => [
            'page_title' => 'OES Settings',
            'menu_title' => 'OES Settings',
            'position' => 55,
            'menu_slug' => 'oes_settings',
        ],
        'separator' => 'before',
        'is_core_page' => true
    ];

    $adminMenuPages['020_information'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Information',
            'menu_title' => 'Information',
            'menu_slug' => 'oes_settings',
            'parent_slug' => 'oes_settings'
        ],
        'view_file_name' => 'view-settings-information',
        'is_core_page' => true
    ];

    $adminMenuPages['040_writing'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Writing',
            'menu_title' => 'Writing',
            'menu_slug' => 'oes_settings_writing',
            'position' => 20,
            'parent_slug' => 'oes_settings'
        ],
        'tabs' => [
            'admin-columns' => 'Columns',
            'admin-container' => 'Container'
        ],
        'is_core_page' => true
    ];

    //prepare reading tabs
    $readingTabs = [
        'theme-languages' => __('Languages', 'oes'),
        'theme-index-pages' => __('Index', 'oes'),
        'theme-search' => __('Search', 'oes'),
        'theme-date' => __('Date Format', 'oes'),
        'theme-media' => __('Media', 'oes')
    ];

    if(!$oes->block_theme){
        $readingTabs['theme-colors'] = __('Colors', 'oes');
        $readingTabs['theme-logos'] = __('Logos', 'oes');
    }

    $adminMenuPages['050_reading'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Reading',
            'menu_title' => 'Reading',
            'menu_slug' => 'oes_settings_reading',
            'position' => 30,
            'parent_slug' => 'oes_settings'
        ],
        'tabs' => $readingTabs,
        'is_core_page' => true
    ];

    $adminMenuPages['051_schema'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Schema',
            'menu_title' => 'Schema',
            'menu_slug' => 'oes_settings_schema',
            'position' => 31,
            'parent_slug' => 'oes_settings'
        ],
        'view_file_name' => 'view-settings-schema',
        'is_core_page' => true
    ];

    $adminMenuPages['052_labels'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Labels',
            'menu_title' => 'Labels',
            'menu_slug' => 'oes_settings_labels',
            'position' => 32,
            'parent_slug' => 'oes_settings'
        ],
        'tabs' => [
            'theme-labels-general' => 'General',
            'theme-labels-media' => 'Media',
            'theme-labels-objects' => 'Objects'
        ],
        'is_core_page' => true
    ];

    // prepare lod tabs
    $lodTabs = [];
    foreach($oes->apis ?? [] as $apiKey => $apiData) {
        $lodTabs[$apiKey] = $apiData->label;
    }

    $adminMenuPages['070_lod'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Linked Open Data',
            'menu_title' => 'Linked Open Data',
            'menu_slug' => 'oes_settings_lod',
            'position' => 60,
            'parent_slug' => 'oes_settings'
        ],
        'tabs' => $lodTabs,
        'is_core_page' => true
    ];

    $adminMenuPages['090_project'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Project',
            'menu_title' => 'Project',
            'menu_slug' => 'oes_settings_project',
            'position' => 90,
            'parent_slug' => 'oes_settings'
        ],
        'tool' => 'project',
        'is_core_page' => true
    ];

    $adminMenuPages['210_tools'] = [
        'page_parameters' => [
            'page_title' => 'OES Tools',
            'menu_title' => 'OES Tools',
            'menu_slug' => 'oes_tools',
            'position' => 56
        ],
        'is_core_page' => true
    ];

    $adminMenuPages['220_tools_information'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Information',
            'menu_title' => 'Information',
            'menu_slug' => 'oes_tools',
            'parent_slug' => 'oes_tools'
        ],
        'view_file_name' => 'view-tools',
        'is_core_page' => true
    ];

    $adminMenuPages['225_tools_data_model'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Data Model',
            'menu_title' => 'Data Model',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_model',
            'position' => 20
        ],
        'view_file_name' => 'view-tools-model',
        'is_core_page' => true
    ];

    $adminMenuPages['227_tools_cache'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Cache',
            'menu_title' => 'Cache',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_cache',
            'position' => 25
        ],
        'view_file_name' => 'view-tools-cache',
        'is_core_page' => true
    ];

    $adminMenuPages['230_tools_import'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Import',
            'menu_title' => 'Import',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_import',
            'position' => 30
        ],
        'tool' => 'import',
        'is_core_page' => true
    ];

    $adminMenuPages['240_tools_export'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Export',
            'menu_title' => 'Export',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_export',
            'position' => 50
        ],
        'tool' => 'export',
        'is_core_page' => true
    ];

    $adminMenuPages['250_tools_operations'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Operations',
            'menu_title' => 'Operations',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_operations',
            'position' => 70
        ],
        'tool' => 'operations',
        'is_core_page' => true
    ];

    $adminMenuPages['260_tools_batch'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Batch',
            'menu_title' => 'Batch',
            'parent_slug' => 'oes_tools',
            'menu_slug' => 'oes_tools_batch',
            'position' => 80
        ],
        'tool' => 'batch',
        'is_core_page' => true
    ];



    if (function_exists('\OES\Rights\user_is_oes_admin') &&
        \OES\Rights\user_is_oes_admin()) $adminMenuPages['100_admin'] = [
        'subpage' => true,
        'page_parameters' => [
            'page_title' => 'Admin',
            'menu_title' => 'Admin',
            'menu_slug' => 'oes_admin',
            'position' => 99,
            'parent_slug' => 'oes_settings'
        ],
        'tabs' => [
            'admin-features' => 'Features',
            'admin' => 'Visibility',
        ],
        'is_core_page' => true
    ];


    /**
     * Filters the OES settings pages.
     *
     * @param array $settings The OES settings pages.
     */
    $adminMenuPages = apply_filters('oes/admin_menu_pages', $adminMenuPages);

    ksort($adminMenuPages);
    foreach ($adminMenuPages as $adminMenuPage) {
        if (isset($adminMenuPage['subpage']) || isset($adminMenuPage['sub_page'])) {
            new Subpage($adminMenuPage);
        }
        else {
            new Page($adminMenuPage);
        }
    }
}


/**
 * Create a help tab on OES remark page.
 * @return void
 */
function help_tab(): void
{
    $screen = get_current_screen();
    $functionName = str_replace('-', '_', $screen->id) . '_help_tabs';
    if ($screen->id && function_exists($functionName)) {
        call_user_func($functionName, $screen);
    }
}