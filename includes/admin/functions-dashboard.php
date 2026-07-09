<?php

namespace OES\Dashboard;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Modify the WordPress dashboard and display OES information.
 * @return void
 */
function modify(): void
{
    if (!current_user_can('edit_posts')) {
        return;
    }

    $features = \OES\Admin\get_features();

    if (!$features || ($features['manual'] ?? false)) {
        add_meta_box(
            'dashboard_oes_manual',
            __('Guidelines', 'oes'),
            '\OES\Guidelines\dashboard_html',
            'dashboard',
            'normal');
    }

    if (!$features || ($features['remarks'] ?? false)) {
        add_meta_box(
            'dashboard_oes_remarks',
            __('Remarks', 'oes'),
            '\OES\Remarks\dashboard_html',
            'dashboard',
            'normal');
    }

    if (!$features || ($features['task'] ?? false)) {
        add_meta_box(
            'dashboard_oes_task',
            __('Tasks', 'oes'),
            '\OES\Tasks\dashboard_html',
            'dashboard',
            'normal');
    }
}

/**
 * Register meta boxes for dashboard.
 *
 * @return void
 */
function add_meta_boxes(): void
{
    $screen = get_current_screen();

    add_meta_box(
        'oes_content',
        __('Published Content', 'oes'),
        '\OES\Dashboard\content_meta_box',
        $screen->id
    );

    add_meta_box(
        'oes_feature',
        __('OES Features Overview', 'oes'),
        '\OES\Dashboard\features_meta_box',
        $screen->id
    );

    add_meta_box(
        'oes_data_model',
        __('Data Model Overview', 'oes'),
        '\OES\Dashboard\data_model_meta_box',
        $screen->id
    );

    add_meta_box(
        'oes_quick_access',
        __('Quick Access', 'oes'),
        '\OES\Dashboard\quick_access_meta_box',
        $screen->id,
        'normal'
    );

    add_meta_box(
        'oes_manual',
        __('Manual & Help', 'oes'),
        '\OES\Dashboard\manual_meta_box',
        $screen->id,
        'normal'
    );

    add_meta_box(
        'oes_modules',
        __('Modules', 'oes'),
        '\OES\Dashboard\modules_meta_box',
        $screen->id,
        'side'
    );
}

/**
 * Render a list of links with an optional description line.
 *
 * @param array $links Each item: ['link' => string, 'label' => string, 'description' => string (optional), 'external' => bool (optional)]
 * @param array $args ['sort' => bool, 'icon' => string|null]
 */
function render_link_list(array $links, array $args = []): void
{
    $args = wp_parse_args($args, [
        'sort' => false,
        'icon' => null,
    ]);

    if ($args['sort']) {
        ksort($links);
    }

    echo '<ul class="oes-link-list">';

    foreach ($links as $link) {

        if (empty($link['link']) || empty($link['label'])) {
            continue;
        }

        echo '<li>';

        if ($args['icon']) {
            printf(
                '<img src="%s" class="oes-manual-icon" alt="">',
                esc_url($args['icon'])
            );
        }

        $target = !empty($link['external']) ? ' target="_blank" rel="noopener"' : '';

        printf(
            '<a href="%s"%s>%s</a>',
            esc_url($link['link']),
            $target,
            esc_html($link['label'])
        );

        if (!empty($link['description'])) {
            printf(
                '<div class="oes-link-sub">%s</div>',
                wp_kses_post($link['description'])
            );
        }

        echo '</li>';
    }

    echo '</ul>';
}

/**
 * Display Data Model meta box.
 * @return void
 */
function data_model_meta_box(): void
{
    global $oes;

    if (empty($oes)) {
        return;
    }

    echo '<div class="oes-dashboard-actions">';

    echo '<p>';
    printf(
        esc_html__('Your encyclopaedia currently has %1$d post types and %2$d taxonomies.', 'oes'),
        count($oes->post_types ?? []),
        count($oes->taxonomies ?? [])
    );
    echo '</p>';

    if (\OES\Rights\user_can_edit_config()) {

        echo '<p class="oes-button-group">';
        oes_render_button(admin_url('admin.php?page=oes_tools_model'), __('View Full Status', 'oes'));
        echo ' ';
        oes_render_button(admin_url('admin.php?page=oes_tools_model&tab=model'), __('Export / Reload Data Model', 'oes'));
        echo '</p>';

        echo '<p class="description">';
        esc_html_e('Edit content types, classifications, and field groups directly in the factory.', 'oes');
        echo '</p>';

        echo '<p class="oes-button-group">';
        oes_render_button(admin_url('admin.php?page=oes_tools_model&tab=factory'), __('Open Data Model Factory', 'oes'), 'primary');
        echo '</p>';
    }

    echo '</div>';
}

/**
 * Display Published Content meta box.
 * @return void
 */
function content_meta_box(): void
{
    global $oes;

    if (empty($oes)) {
        return;
    }

    echo '<p>';
    esc_html_e('Public and indexable now, based on your visibility settings.', 'oes');
    echo '</p>';

    $pagesMarkup = '';
    $pages = wp_count_posts('page')->publish ?? 0;
    if ($pages > 0) {
        $pagesMarkup = sprintf(
            '<li><span class="dashicons dashicons-admin-page"></span>%d %s</li>',
            intval($pages),
            esc_html__('Pages', 'oes')
        );
    }

    $list = [];

    $postTypes = get_post_types(
        [
            'public' => true,
            '_builtin' => false,
        ],
        'objects'
    );
    ksort($postTypes);

    foreach ($postTypes as $postType) {

        if (!isset($oes->post_types[$postType->name])) {
            continue;
        }

        $count = wp_count_posts($postType->name)->publish ?? 0;

        if ($count === 0 || !$postType->has_archive) {
            continue;
        }

        $identifier = $oes->post_types[$postType->name]['type'] ?? 'default';
        $class = oes_get_menu_icon_path($identifier);

        $list[$identifier][] = sprintf(
            '<li><span class="dashicons %s"></span><a href="%s">%d %s</a></li>',
            esc_attr($class),
            esc_url(get_post_type_archive_link($postType->name)),
            intval($count),
            esc_html($postType->labels->name)
        );
    }

    $taxonomies = get_taxonomies(
        [
            'public' => true,
            '_builtin' => false,
        ],
        'objects'
    );
    ksort($taxonomies);

    foreach ($taxonomies as $taxonomy) {

        if (!isset($oes->taxonomies[$taxonomy->name]) || empty($taxonomy->rewrite['slug'] ?? '')) {
            continue;
        }

        $terms = wp_count_terms($taxonomy->name, ['hide_empty' => true]);

        if (is_wp_error($terms) || (int)$terms === 0) {
            continue;
        }

        $identifier = $oes->taxonomies[$taxonomy->name]['type'] ?? 'default';
        $class = oes_get_menu_icon_path($identifier);

        $list[$identifier][] = sprintf(
            '<li><span class="dashicons %s"></span><a href="%s">%d %s</a></li>',
            esc_attr($class),
            esc_url(get_home_url() . '/' . $taxonomy->rewrite['slug'] . '/'),
            intval($terms),
            esc_html($taxonomy->labels->name)
        );
    }

    ksort($list);

    echo '<ul class="oes-content-status-list">';
    echo $pagesMarkup;
    foreach ($list as $group) {
        echo implode('', $group);
    }
    echo '</ul>';
}

/**
 * Display Quick Access meta box.
 * @return void
 */
function quick_access_meta_box(): void
{
    $links = [
        [
            'link' => admin_url('admin.php?page=oes_settings_languages'),
            'label' => __('Languages', 'oes'),
            'description' => __('Define labels for templates for multiple languages.', 'oes'),
        ],
        [
            'link' => admin_url('admin.php?page=oes_settings_schema'),
            'label' => __('Schema', 'oes'),
            'description' => __('Defines the representation of objects, their properties and relationships between objects.', 'oes'),
        ],
        [
            'link' => admin_url('admin.php?page=oes_settings_search'),
            'label' => __('Search', 'oes'),
            'description' => __('Define which parts of your encyclopaedia can be searched and how they are to be displayed.', 'oes'),
        ],
        [
            'link' => admin_url('themes.php'),
            'label' => __('Theme', 'oes'),
            'description' => __('Access the WordPress theme customizer.', 'oes'),
        ],
    ];

    render_link_list($links);
}

/**
 * Display Modules meta box.
 * @return void
 */
function modules_meta_box(): void
{
    global $oes;
    render_link_list($oes->module_pages ?? [], ['sort' => true]);
}

/**
 * Display Manual meta box.
 * @return void
 */
function manual_meta_box(): void
{
    echo '<p>';
    printf(
        __('The full user manual is available (in German) at %1$smanual.open-encyclopedia-system.org%2$s.', 'oes'),
        '<a href="https://manual.open-encyclopedia-system.org/" target="_blank" rel="noopener">',
        '</a>'
    );
    echo '</p>';

    $links = [
        [
            'link' => 'https://manual.open-encyclopedia-system.org/book/inhalte-erstellen/',
            'label' => __('Inhalte erstellen', 'oes'),
            'external' => true,
        ],
        [
            'link' => 'https://manual.open-encyclopedia-system.org/book/verlinkungen/',
            'label' => __('Verlinkung', 'oes'),
            'external' => true,
        ],
        [
            'link' => 'https://manual.open-encyclopedia-system.org/book/best-practice/',
            'label' => __('Empfehlungen und Good Practices', 'oes'),
            'external' => true,
        ],
        [
            'link' => 'https://manual.open-encyclopedia-system.org/book/settings/',
            'label' => __('OES Settings', 'oes'),
            'external' => true,
        ],
        [
            'link' => 'https://manual.open-encyclopedia-system.org/book/datamodel-factory/',
            'label' => __('Datenmodell', 'oes'),
            'external' => true,
        ],
    ];

    render_link_list($links, [
        'icon' => plugins_url(OES_BASENAME . '/assets/images/oes_manual_icon.png'),
    ]);

    echo '<p>';
    printf(
        __('Contact us at %1$sinfo@open-encyclopedia-system.org%2$s.', 'oes'),
        '<a href="mailto:info@open-encyclopedia-system.org">',
        '</a>'
    );
    echo '</p>';
}

/**
 * Display Features meta box.
 * @return void
 */
function features_meta_box(): void
{
    $catalog = get_feature_catalog();

    if (empty($catalog)) {
        echo '<p class="description">';
        esc_html_e('No features are registered.', 'oes');
        echo '</p>';
        return;
    }

    echo '<ul class="oes-feature-group-list">';

    $enabledFeatures = \OES\Admin\get_features();

    $renderedAny = false;

    foreach ($catalog as $featureGroup) {

        $rows = [];

        foreach ($featureGroup['features'] ?? [] as $feature) {

            if (!isset($feature['enable'])) {
                continue;
            }

            $isEnabled = !$enabledFeatures || ($enabledFeatures[$feature['enable']] ?? false);

            $badgeClass = $isEnabled ? 'oes-badge oes-badge--enabled' : 'oes-badge oes-badge--disabled';
            $badgeText = $isEnabled ? __('Enabled', 'oes') : __('Disabled', 'oes');

            $name = $feature['name'] ?? '';

            $title = !empty($feature['settings'])
                ? sprintf('<a href="%s">%s</a>', esc_url(admin_url('admin.php?page=' . $feature['settings'])), esc_html($name))
                : esc_html($name);

            $rows[] = [
                'name' => $name,
                'markup' => $title . ' <span class="' . esc_attr($badgeClass) . '">' . esc_html($badgeText) . '</span>',
            ];
        }

        if (empty($rows)) {
            continue;
        }

        usort($rows, fn($a, $b) => strnatcasecmp($a['name'], $b['name']));

        $renderedAny = true;

        foreach ($rows as $row) {
            echo '<li>' . $row['markup'] . '</li>';
        }
    }

    echo '</ul>';

    if (!$renderedAny) {
        echo '<p class="description">';
        esc_html_e('No features are registered.', 'oes');
        echo '</p>';
    }

    echo '<p class="oes-button-group">';
    oes_render_button(admin_url('admin.php?page=oes_settings_features'), __('View Details and Setting Options', 'oes'), 'primary');
    echo '</p>';
}

/**
 * Feature catalog: descriptive data for every OES feature, grouped by
 * category.
 */
function get_feature_catalog(): array
{
    $features = [
        'extended_publishing' => [
            'group' => __('Extended Publishing Functions', 'oes'),
            'features' => [
                'open_access' => [
                    'name' => __('Open Access Publishing and Sustainable Referencing', 'oes'),
                    'description' => __('Publication of content (individual articles, datasets) in open access under a Creative Commons license directly via the website.', 'oes'),
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/inhalte-erstellen/'
                ],
                'cc_licensing' => [
                    'name' => __('CC Licensing', 'oes'),
                    'description' => __('Publication of content under a Creative Commons license directly via the website. Enable this in schema.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                ],
                'doi_support' => [
                    'name' => __('DOI Support', 'oes'),
                    'description' => __('Assignment of DOIs (Digital Object Identifier) for articles – generated manually or automatically via OES – for permanent citability.', 'oes'),
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/doi-module/',
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                ],
                'citation' => [
                    'name' => __('Citation', 'oes'),
                    'description' => __('Automatic generation of a standardized citation style for each article.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                ],
                'versioning' => [
                    'name' => __('Versioning', 'oes'),
                    'description' => __('Incremental publishing through multiple versions of an article, each with its own DOI – optionally in different languages.', 'oes'),
                    'actions' => [
                        'configure' => [
                            'page' => 'oes_tools_model&tab=factory',
                            'label' => __('Edit Data Model', 'oes')
                        ]
                    ],
                ],
            ],
        ],
        'open_connected_data' => [
            'group' => __('Open and Connected Data', 'oes'),
            'features' => [
                'linking' => [
                    'name' => __('Linking', 'oes'),
                    'description' => __('Internal and external links for contextualization and interconnection of content.', 'oes'),
                    'actions' => [],
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/verlinkungen/'
                ],
                'index_creation' => [
                    'name' => __('Index Creation', 'oes'),
                    'description' => __('Linking and tagging for automated index (register) generation.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_index',
                            'label' => __('Edit Index', 'oes')
                        ]
                    ],
                ],
                'lod' => [
                    'name' => __('Linked Open Data (LoD)', 'oes'),
                    'description' => __('Data enrichment through connection with authority files (GND, GeoNames, ROR, LoC). Import can be semi-automated.', 'oes'),
                    'enable' => 'lod_apis',
                    'settings' => 'oes_settings_lod',
                    'actions' => [
                        'gnd' => [
                            'page' => 'oes_settings_lod',
                            'label' => __('GND', 'oes'),
                        ],
                        'geonames' => [
                            'page' => 'oes_settings_lod&tab=geonames',
                            'label' => __('GeoNames', 'oes'),
                        ],
                        'ror' => [
                            'page' => 'oes_settings_lod&tab=ror',
                            'label' => __('ROR', 'oes'),
                        ],
                        'orcid' => [
                            'page' => 'oes_settings_lod&tab=orcid',
                            'label' => __('ORCID', 'oes'),
                        ],
                        'loc' => [
                            'page' => 'oes_settings_lod&tab=loc',
                            'label' => __('LoC', 'oes'),
                        ],
                        'edit' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/normdaten/'
                ],
            ],
        ],
        'structure_markup' => [
            'group' => __('Structure and Markup of Content', 'oes'),
            'features' => [
                'media_integration' => [
                    'name' => __('Media Integration', 'oes'),
                    'description' => __('Integration of images, audio, and video via OES-specific blocks with structured metadata.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_media',
                            'label' => __('Edit Media Behaviour', 'oes')
                        ]
                    ],
                ],
                'endnotes' => [
                    'name' => __('Endnotes', 'oes'),
                    'description' => __('Management of endnotes with automatic pop-ups and structured lists.', 'oes'),
                    'actions' => [],
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/verlinkungen/?chapter=1345&section=1347'
                ],
                'structured_metadata' => [
                    'name' => __('Structured Metadata', 'oes'),
                    'description' => __('Capture of authors, publication date, and index links.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                ],
                'tagging' => [
                    'name' => __('Tagging', 'oes'),
                    'description' => __('Assignment of thematic keywords for improved categorization and discoverability.', 'oes'),
                    'actions' => [],
                ],
            ],
        ],
        'output' => [
            'group' => __('Output', 'oes'),
            'features' => [
                'pdf_support' => [
                    'name' => __('PDF Support', 'oes'),
                    'description' => __('Customized PDF rendering via browser print for a citable print version in addition to the web view.', 'oes'),
                    'actions' => [],
                ],
                'multilingual_support' => [
                    'name' => __('Multilingual Support', 'oes'),
                    'description' => __('Support for multilingual content and a multilingual user interface.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_languages',
                            'label' => __('Edit Languages', 'oes')
                        ]
                    ],
                ],
                'design_customization' => [
                    'name' => __('Design Customization', 'oes'),
                    'description' => __('Flexible presentation of structured data with OES Block Theme and OES Classic Theme.', 'oes'),
                    'actions' => [
                        'theme' => [
                            'url' => admin_url('themes.php'),
                            'label' => __('Appearances', 'oes')
                        ]
                    ],
                ],
                'full_text_search' => [
                    'name' => __('Full-Text Search', 'oes'),
                    'description' => __('Enhanced search with precise logic, contextual highlighting, and configurable result display.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_search',
                            'label' => __('Edit Search', 'oes')
                        ]
                    ],
                ],
                'date_format' => [
                    'name' => __('Date Format', 'oes'),
                    'description' => __('Define the date format which will be applied to all displayed dates.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_languages&tab=theme-date',
                            'label' => __('Edit Date Format', 'oes')
                        ]
                    ],
                ],
                'labels' => [
                    'name' => __('Labels', 'oes'),
                    'description' => __('Define labels for the templates that will be rendered on certain part of the pages or for specific languages.', 'oes'),
                    'actions' => [
                        'edit_general' => [
                            'page' => 'oes_settings_languages&tab=theme-labels-general',
                            'label' => __('Edit General Labels', 'oes')
                        ],
                        'edit_media' => [
                            'page' => 'oes_settings_languages&tab=theme-labels-media',
                            'label' => __('Edit Media Labels', 'oes')
                        ],
                        'edit_objects' => [
                            'page' => 'oes_settings_languages&tab=theme-labels-objects',
                            'label' => __('Edit Object Labels', 'oes')
                        ]
                    ],
                ],
            ],
        ],
        'editorial_tools' => [
            'group' => __('Editorial Tools', 'oes'),
            'features' => [
                'internal_data_fields' => [
                    'name' => __('Internal Data Fields (Remarks)', 'oes'),
                    'description' => __('Each post includes editorial status and team notes fields collected centrally.', 'oes'),
                    'enable' => 'remarks',
                    'settings' => 'admin_oes_remarks',
                    'actions' => [
                        'view' => [
                            'page' => 'admin_oes_remarks',
                            'label' => __('View Remarks', 'oes')
                        ]
                    ],
                ],
                'task_management' => [
                    'name' => __('Task Management', 'oes'),
                    'description' => __('Assign tasks with due dates for collaborative editorial processes.', 'oes'),
                    'enable' => 'task',
                    'settings' => 'oes_task',
                    'actions' => [
                        'view' => [
                            'post_type' => 'oes_task',
                            'label' => __('View Tasks', 'oes')
                        ]
                    ],
                ],
                'manuals_guidelines' => [
                    'name' => __('Guidelines', 'oes'),
                    'description' => __('Create application-specific editorial manuals, e.g., author guidelines and citation rules.', 'oes'),
                    'enable' => 'manual',
                    'settings' => 'admin_manual',
                    'actions' => [
                        'view' => [
                            'page' => 'admin_manual',
                            'label' => __('View Guidelines', 'oes')
                        ]
                    ],
                ],
                'data_import_csv' => [
                    'name' => __('Data Import (CSV)', 'oes'),
                    'description' => __('Import structured content via CSV with validation and dependency checks.', 'oes'),
                    'actions' => [
                        'tool' => [
                            'page' => 'oes_tools_import',
                            'label' => __('Import Data', 'oes')
                        ]
                    ],
                ],
                'data_export_csv' => [
                    'name' => __('Data Export (CSV)', 'oes'),
                    'description' => __('Export structured CSV for processing, archiving, or integration with third-party systems.', 'oes'),
                    'actions' => [
                        'tool' => [
                            'page' => 'oes_tools_export',
                            'label' => __('Export Data', 'oes')
                        ]
                    ],
                ],
            ],
        ],
        'advanced' => [
            'group' => __('Advanced (Admin)', 'oes'),
            'features' => [
                'data_model_factory' => [
                    'name' => __('Data Model Factory', 'oes'),
                    'description' => __('Define and manage custom post types and taxonomies for your application and customize input forms and their fields.', 'oes'),
                    'enable' => 'factory',
                    'settings' => 'oes_tools_model&tab=factory',
                    'actions' => [
                        'factory' => [
                            'page' => 'oes_tools_model&tab=factory',
                            'label' => __('Factory', 'oes')
                        ]
                    ],
                ],
                'oes_objects' => [
                    'name' => __('OES Objects', 'oes'),
                    'description' => __('todo.', 'oes'),
                    'actions' => [
                        'oes_objects' => [
                            'post_type' => 'oes_object',
                            'label' => __('View OES Objects', 'oes')
                        ]
                    ],
                ],
                'schema' => [
                    'name' => __('Schema', 'oes'),
                    'description' => __('Define the data schema and its representation of text objects, their properties and relationships between objects.', 'oes'),
                    'actions' => [
                        'schema' => [
                            'page' => 'oes_settings_schema',
                            'label' => __('Edit Schema', 'oes')
                        ]
                    ],
                    'manual' => 'https://manual.open-encyclopedia-system.org/book/settings/?chapter=896'
                ],
                'container' => [
                    'name' => __('Container', 'oes'),
                    'description' => __('Organize admin menu items into a new top menu with sub menu items and to display currently worked on post objects.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_advanced&tab=admin-container',
                            'label' => __('Edit Container', 'oes')
                        ]
                    ],
                ],
                'columns' => [
                    'name' => __('Columns', 'oes'),
                    'description' => __('Add or remove columns for the list views of post objects in the admin area.', 'oes'),
                    'actions' => [
                        'edit' => [
                            'page' => 'oes_settings_advanced',
                            'label' => __('Edit Columns', 'oes')
                        ]
                    ],
                ],
                'caching' => [
                    'name' => __('Caching', 'oes'),
                    'description' => __('Provides a caching system for optimizing archive and index views.', 'oes'),
                    'enable' => 'cache',
                    'settings' => 'oes_tools_cache',
                    'actions' => [
                        'tool' => [
                            'page' => 'oes_tools_cache',
                            'label' => __('View Cache', 'oes')
                        ]
                    ],
                ],
                'batch' => [
                    'name' => __('Batch Processing', 'oes'),
                    'description' => __('todo.', 'oes'),
                    'actions' => [
                        'tool' => [
                            'page' => 'oes_tools_batch',
                            'label' => __('Batch Tool', 'oes')
                        ]
                    ],
                ],
                'extended_access_rights' => [
                    'name' => __('Extended Access Rights', 'oes'),
                    'description' => __('Define OES roles for differentiated access and workflows.', 'oes'),
                    'actions' => [],
                ],
            ]
        ]

    ];

    /**
     * Allow other plugins to add or modify features.
     */
    return apply_filters('oes_features', $features);
}