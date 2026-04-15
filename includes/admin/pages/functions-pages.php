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

    foreach ($oes->post_types ?? [] as $postTypeKey => $postType) {
        $parent = $postType['parent'] ?? '';

        if ($parent && $parent !== 'none' && !isset($oes->admin_pages['container'][$postTypeKey])) {
            $oes->admin_pages['container'][$postTypeKey] = prepare_container_page_args($postTypeKey);
        }
    }

    foreach ($oes->admin_pages['container'] ?? [] as $key => $page) {
        if (!is_array($page) || ($page['hide'] ?? false)) {
            continue;
        }

        create_container($key, $page);
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
    $postType = OES()->post_types[$postTypeKey] ?? null;
    if (!$postType) {
        return [];
    }

    $elements = [$postType['parent'] ?? '', $postTypeKey];

    $subPages = $elements;

    foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomy) {
        $subPages[] = $taxonomy;
    }

    foreach (get_post_type_object($postType['parent'] ?? '')->taxonomies as $taxonomy) {
        $subPages[] = $taxonomy;
    }

    return [
        'page_parameters' => [
            'menu_title' => sprintf('[%s]', $postType['label'] ?? ''),
            'position'   => 26,
            'subpages'   => $subPages,
            'icon_url'   => $postType['type'] ?? 'container',
        ],
        'info_page' => [
            'elements' => $elements,
            'label'    => __('Recently worked on', 'oes'),
        ],
        'generated' => true,
        'hide'      => false,
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
        plugins_url(OES_BASENAME . $file . 'page' . oes_minify() . '.js'),
        ['jquery'],
        false,
        true);
    wp_localize_script(
        'oes-admin_page',
        'oesLanguages',
        OES()->languages ?? []
    );
    wp_enqueue_script('oes-admin_page');

    $file .= str_replace('-', '_', $hook) . oes_minify() . '.js';
    if (file_exists(oes_get_path($file, OES_CORE_PLUGIN))) {
        wp_register_script(
            'oes-admin_' . $hook,
            plugins_url(OES_BASENAME . $file),
            ['jquery'],
            false,
            true);
        wp_enqueue_script('oes-admin_' . $hook);
    }

    if (str_contains($hook, 'oes_settings') || str_contains($hook, 'oes-modules')) {
        \OES\ACF\enqueue_select2();
    }
}

/**
 * Initialize the OES Settings pages
 * @return void
 */
function initialize_admin_menu_pages(): void
{
    global $oes;

    $adminMenuPages = [
        '000_editor_tools' => ['separator' => true, 'position' => 49.9],
        '000_settings'     => ['separator' => true, 'position' => 54.9],
    ];

    if (\OES\Rights\user_can_read_settings()) {

        $adminMenuPages['oes_settings'] = [
            'page_parameters' => [
                'page_title' => __('OES', 'oes'),
                'menu_title' => __('OES', 'oes'),
                'position'   => 55,
                'menu_slug'  => 'oes_settings',
                'icon_url'   => 'oes',
            ],
            'is_core_page' => true
        ];

        $settingsSubpages = [
            'oes_settings_dashboard' => [
                'title' => __('Dashboard', 'oes'),
                'menu_slug'  => 'oes_settings',
                'view_file_name' => 'view-settings-information'
            ],
            'oes_settings_features' => [
                'title' => __('Features', 'oes'),
                'view_file_name' => 'view-settings-features'
            ],
            'oes_settings_index' => [
                'title' => __('Index', 'oes'),
                'tool'       => 'theme-index-pages'
            ],
            'oes_settings_languages' => [
                'title' => __('Languages & Text', 'oes'),
                'tabs' => [
                    'theme-languages'        => __('Languages', 'oes'),
                    'theme-labels-general'   => __('General', 'oes'),
                    'theme-labels-media'     => __('Media', 'oes'),
                    'theme-labels-objects'   => __('Objects', 'oes'),
                    'theme-date'             => __('Date Format', 'oes'),
                ]
            ],
            'oes_settings_media' => [
                'title' => __('Media', 'oes'),
                'tool'       => 'theme-media'
            ],
            'oes_settings_schema' => [
                'title' => __('Schema', 'oes'),
                'view_file_name' => 'view-settings-schema'
            ],
            'oes_settings_search' => [
                'title' => __('Search', 'oes'),
                'tool'       => 'theme-search'
            ],
        ];

        $applicationOptions = \OES\Admin\get_application_settings();
        if(!empty($applicationOptions)) {
            $settingsSubpages['oes_settings_application'] = [
                'title' => __('Application', 'oes'),
                'tool'       => 'application'
            ];
        }

        if (\OES\Rights\user_is_admin()) {
            $settingsSubpages['oes_settings_advanced'] = [
                'title' => __('Advanced', 'oes'),
                'tabs' => [
                    'admin-columns'   => __('Columns', 'oes'),
                    'admin-container' => __('Container', 'oes')
                ]
            ];
        }

        $lodTabs = array_map(function ($apiData) {
            return $apiData->label;
        }, $oes->apis ?? []);
        $settingsSubpages['oes_settings_lod'] = [
            'title' => __('Linked Open Data', 'oes'),
            'tabs'       => $lodTabs
        ];

        if (!$oes->block_theme) {
            $settingsSubpages['oes_settings_theme'] = [
                'title' => __('Theme', 'oes'),
                'tabs' => [
                    'theme-colors' => __('Colors', 'oes'),
                    'theme-logos'  => __('Logos', 'oes')
                ]
            ];
        }

        $adminMenuPages['oes_modules'] = [
            'page_parameters' => [
                'page_title' => __('OES Modules', 'oes'),
                'menu_title' => __('OES Modules', 'oes'),
                'position'   => 56,
                'menu_slug'  => 'oes_modules',
                'icon_url'   => 'oes',
            ],
            'is_core_page' => true
        ];

        $modulesSubpages = [
            'oes_modules_dashboard' => [
                'title' => __('Modules', 'oes'),
                'menu_slug'  => 'oes_modules',
                'view_file_name' => 'view-modules'
            ],
        ];

        $adminMenuPages['oes_tools'] = [
            'page_parameters' => [
                'page_title' => __('OES Tools', 'oes'),
                'menu_title' => __('OES Tools', 'oes'),
                'menu_slug' => 'oes_tools',
                'position' => 57,
                'icon_url' => 'oes'
            ],
            'is_core_page' => true
        ];

        $toolsSubpages = [
            'oes_tools_dashboard' => [
                'title' => __('Dashboard', 'oes'),
                'menu_slug'  => 'oes_tools',
                'view_file_name' => 'view-tools'
            ],
            'oes_tools_model' => [
                'title' => __('Data Model', 'oes'),
                'view_file_name' => 'view-tools-model'
            ],
            'oes_tools_cache' => [
                'title' => __('Cache', 'oes'),
                'view_file_name' => 'view-tools-cache'
            ],
            'oes_tools_import' => [
                'title' => __('Import', 'oes'),
                'capability' => 'oes_manage_content',
                'tool' => 'import'
            ],
            'oes_tools_export' => [
                'title' => __('Export', 'oes'),
                'tool' => 'export'
            ],
            'oes_tools_batch' => [
                'title' => __('Batch Processing', 'oes'),
                'capability' => 'manage_options',
                'tool' => 'batch'
            ]
        ];

        foreach(['oes_settings' => $settingsSubpages, 'oes_modules' => $modulesSubpages, 'oes_tools' => $toolsSubpages] as $slug => $subpages) {
            foreach ($subpages as $pageSlug => $subpage) {

                $pageParameters = [
                    'page_title' => $subpage['title'] ?? '',
                    'menu_title' => $subpage['title'] ?? '',
                    'menu_slug'  => $subpage['menu_slug'] ?? $pageSlug,
                    'capability' => $subpage['capability'] ?? 'oes_read_settings',
                    'parent_slug'=> $slug
                ];

                $adminMenuPages[$pageSlug] = [
                    'subpage'         => true,
                    'is_core_page'    => true,
                    'page_parameters' => $pageParameters,
                ];

                foreach (['view_file_name', 'tool', 'tabs'] as $optKey) {
                    if (isset($subpage[$optKey])) {
                        $adminMenuPages[$pageSlug][$optKey] = $subpage[$optKey];
                    }
                }
            }
        }
    }

    $adminMenuPages = apply_filters('oes/admin_menu_pages', $adminMenuPages);

    foreach ($adminMenuPages as $adminMenuPage) {
        if (!empty($adminMenuPage['subpage'])) {
            new Subpage($adminMenuPage);
        } else {
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

/**
 * Sorts the submenus of the OES top-level menu alphabetically,
 * while keeping "Dashboard" first and "Advanced" last.
 */
function sort_submenus_alphabetically(): void
{
    global $submenu;

    $consideredMenus = ['oes_tools', 'oes_settings'];
    foreach ($consideredMenus as $mainMenu) {
        if (isset($submenu[$mainMenu]) && count($submenu[$mainMenu]) > 1) {

            $dashboardItem = null;
            foreach ($submenu[$mainMenu] as $key => $item) {
                if (strcasecmp($item[0], 'Dashboard') === 0) {
                    $dashboardItem = $item;
                    unset($submenu[$mainMenu][$key]);
                    break;
                }
            }

            $advancedItem = null;
            foreach ($submenu[$mainMenu] as $key => $item) {
                if (strcasecmp($item[0], 'Advanced') === 0) {
                    $advancedItem = $item;
                    unset($submenu[$mainMenu][$key]);
                    break;
                }
            }

            usort($submenu[$mainMenu], function ($a, $b) {
                return strcasecmp($a[0], $b[0]);
            });

            $submenu[$mainMenu] = array_values($submenu[$mainMenu]);

            if ($dashboardItem) {
                array_unshift($submenu[$mainMenu], $dashboardItem);
            }

            if ($advancedItem) {
                $submenu[$mainMenu][] = $advancedItem;
            }
        }
    }
}