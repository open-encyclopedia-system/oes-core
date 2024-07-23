<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Create container pages.
 * @return void
 */
function initialize_container_pages(): void
{
    $oes = OES();
    if (isset($oes->admin_pages['container']))
        foreach ($oes->admin_pages['container'] as $key => $page)
            if (is_array($page)) create_container($key, $page);

    /* add container page for versioning posts */
    foreach ($oes->post_types as $postTypeKey => $postType)
        if (!empty($postType['parent'] ?? '') && !isset($oes->admin_pages['container'][$postTypeKey]))
            create_container($postTypeKey);
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
    if (!isset($args['hide']) || !$args['hide']) {
        if (empty($args)) $args = prepare_container_page_args($key);

        /* validate page parameter */
        $args['page_parameters'] = array_merge([
            'menu_title' => '[' . $key . ']',
            'page_title' => '[' . $key . ']',
            'position' => '20',
            'menu_slug' => 'container_' . $key,
            'icon_url' => plugins_url(OES_BASENAME . '/assets/images/oes_cubic_18x18.png'),
        ], $args['page_parameters'] ?? []);

        /* validate info page */
        $args['info_page'] = array_merge([
            'elements' => [],
            'label' => __('Recently worked on', 'oes')
        ], $args['info_page']);

        new Container($args);
    }
}


/**
 * Prepare the container arguments for a versioning post type.
 *
 * @param string $postTypeKey The post type key.
 * @return array
 */
function prepare_container_page_args(string $postTypeKey = ''): array
{

    /* prepare elements and sub pages */
    $postType = OES()->post_types[$postTypeKey] ?? false;
    if (!$postType) return [];

    $elements = [
        $postType['parent'],
        $postTypeKey
    ];
    $subPages = $elements;
    foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomy) $subPages[] = $taxonomy;
    foreach (get_post_type_object($postType['parent'])->taxonomies ?? [] as $taxonomy) $subPages[] = $taxonomy;

    /* prepare container args */
    $args = [
        'main_slug' => 'container_' . $postTypeKey,
        'page_parameters' => [
            'menu_title' => '[' . $postType['label'] . ']',
            'position' => '20'
        ],
        'subpages' => $subPages,
        'info_page' => [
            'elements' => $elements,
            'label' => __('Recently worked on', 'oes')
        ],
        'generated' => true,
        'hide' => false
    ];


    /**
     * Filters the version container page arguments.
     *
     * @param string $args The page arguments.
     * @param array $postTypeKey The post type key.
     */
    return apply_filters('oes/page_version_container', $args, $postTypeKey);
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

    if (oes_starts_with($hook, 'oes-settings')) \OES\ACF\enqueue_select2();
}


/**
 * Initialize the OES Settings pages
 * @return void
 */
function initialize_admin_menu_pages(): void
{

    $adminMenuPages = [
        '010_settings' => [
            'page_parameters' => [
                'page_title' => 'OES Settings',
                'menu_title' => 'OES Settings',
                'position' => 55,
                'menu_slug' => 'oes_settings',
            ],
            'separator' => 'before',
            'is_core_page' => true
        ],
        '020_information' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Information',
                'menu_title' => 'Information',
                'menu_slug' => 'oes_settings',
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-information',
            'is_core_page' => true
        ],
        '040_writing' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Writing',
                'menu_title' => 'Writing',
                'menu_slug' => 'oes_settings_writing',
                'position' => 20,
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-writing',
            'is_core_page' => true
        ],
        '050_reading' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Reading',
                'menu_title' => 'Reading',
                'menu_slug' => 'oes_settings_reading',
                'position' => 30,
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-reading',
            'is_core_page' => true
        ],
        '051_schema' => [
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
        ],
        '052_labels' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Labels',
                'menu_title' => 'Labels',
                'menu_slug' => 'oes_settings_labels',
                'position' => 32,
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-labels',
            'is_core_page' => true
        ],
        '070_lod' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Linked Open Data',
                'menu_title' => 'Linked Open Data',
                'menu_slug' => 'oes_settings_lod',
                'position' => 60,
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-lod',
            'is_core_page' => true
        ],
        '090_project' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Project',
                'menu_title' => 'Project',
                'menu_slug' => 'oes_settings_project',
                'position' => 90,
                'parent_slug' => 'oes_settings'
            ],
            'view_file_name' => 'view-settings-project',
            'is_core_page' => true
        ],
        '210_tools' => [
            'page_parameters' => [
                'page_title' => 'OES Tools',
                'menu_title' => 'OES Tools',
                'menu_slug' => 'oes_tools',
                'position' => 56
            ],
            'is_core_page' => true
        ],
        '220_tools_information' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Information',
                'menu_title' => 'Information',
                'menu_slug' => 'oes_tools',
                'parent_slug' => 'oes_tools'
            ],
            'view_file_name' => 'view-tools',
            'is_core_page' => true
        ],
        '225_data_model' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Data Model',
                'menu_title' => 'Data Model',
                'parent_slug' => 'oes_tools',
                'menu_slug' => 'oes_tools_model',
                'position' => 2
            ],
            'view_file_name' => 'view-tools-model',
            'is_core_page' => true
        ],
        '230_tools_import' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Import',
                'menu_title' => 'Import',
                'parent_slug' => 'oes_tools',
                'menu_slug' => 'oes_tools_import',
                'position' => 3
            ],
            'view_file_name' => 'view-tools-import',
            'is_core_page' => true
        ],
        '240_tools_export' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Export',
                'menu_title' => 'Export',
                'parent_slug' => 'oes_tools',
                'menu_slug' => 'oes_tools_export',
                'position' => 5
            ],
            'view_file_name' => 'view-tools-export',
            'is_core_page' => true
        ],
        '250_tools_operations' => [
            'subpage' => true,
            'page_parameters' => [
                'page_title' => 'Operations',
                'menu_title' => 'Operations',
                'parent_slug' => 'oes_tools',
                'menu_slug' => 'oes_tools_operations',
                'position' => 7
            ],
            'view_file_name' => 'view-tools-operations',
            'is_core_page' => true
        ]
    ];

    /* add admin page for admin user */
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
        'view_file_name' => 'view-settings-admin',
        'is_core_page' => true
    ];


    /**
     * Filters the OES settings pages.
     *
     * @param array $settings The OES settings pages.
     */
    $adminMenuPages = apply_filters('oes/admin_menu_pages', $adminMenuPages);

    /* initialize pages */
    ksort($adminMenuPages);
    foreach ($adminMenuPages as $adminMenuPage) {
        if (isset($adminMenuPage['subpage']) || isset($adminMenuPage['sub_page'])) new Subpage($adminMenuPage);
        else new Page($adminMenuPage);
    }
}