<?php

/**
 * @file
 * @todoReview Review for 2.4.x
 * @oesDevelopment in 2.4.0:
 *  - add bookmarks meta box
 *  - add statistics meta box
 *  - add_action('welcome_panel', '\OES\Dashboard\welcome_panel');
 */

namespace OES\Dashboard;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Modify the WordPress dashboard and display OES information instead.
 * @return void
 */
function modify(): void
{
    // TODO @3.3.0
    /* remove meta boxes */
    /*remove_meta_box('dashboard_primary', 'dashboard', 'side'); //WordPress Blog
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); //Plugins
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); //Right now
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

    /* modify meta box */
    /*remove_action('welcome_panel', 'wp_welcome_panel');
    remove_action('try_gutenberg_panel', 'wp_try_gutenberg_panel');

    /* return early if only subscriber */
    if (current_user_can('subscriber')) return;

    /* get options where feature options are stored */
    $features = \OES\Admin\get_features();

    /*
    add_meta_box(
        'dashboard_oes_profile',
        'OES Bookmarks',
        '\OES\Profile\dashboard_html',
        'dashboard',
        'side');

    add_meta_box(
        'dashboard_oes_statistics',
        'OES Statistic',
        '\OES\Profile\dashboard_html',
        'dashboard',
        'side');*/

    if (!$features || ($features['manual'] ?? false))
        add_meta_box(
                'dashboard_oes_manual',
                __('Guidelines', 'oes'),
                '\OES\Guidelines\dashboard_html',
                'dashboard',
                'normal');

    if (!$features || ($features['remarks'] ?? false))
        add_meta_box(
                'dashboard_oes_remarks',
                __('Remarks', 'oes'),
                '\OES\Remarks\dashboard_html',
                'dashboard',
                'normal');

    if (!$features || ($features['task'] ?? false))
        add_meta_box(
                'dashboard_oes_task',
                __('Tasks', 'oes'),
                '\OES\Tasks\dashboard_html',
                'dashboard',
                'normal');
}

/**
 * Modify the welcome panel to introduce users to WordPress.
 * @oesDevelopment Display OES information, setup wizard etc.
 * @return void
 */
function welcome_panel(): void
{
    ?>
    <div class="welcome-panel-content">
        <h2><?php _e('Welcome to OES!'); ?></h2>
        <h4 class="about-description"><?php
            _e('Check out our <a href="https://www.open-encyclopedia-system.org/">website</a>.'); ?></h4>
    </div>
    <?php
}

// TODO @3.0.0
function settings_dashboard(): void
{
    $screen = get_current_screen();

    $name = 'OES';
    if (defined('OES_BASENAME_APPLICATION')) {
        $name = OES_BASENAME_APPLICATION;
    }

    add_meta_box(
            'oes_welcome',
            __('Welcome to ', 'oes') . strtoupper($name),
            '\OES\Dashboard\welcome_meta_box',
            'toplevel_page_oes_settings',
            'normal',
            'high'
    );

    add_meta_box(
            'oes_content',
            __('Published Content Overview', 'oes'),
            '\OES\Dashboard\content_meta_box',
            $screen->id,
            'side'
    );

    add_meta_box(
            'oes_data_model',
            __('Data Model Overview', 'oes'),
            '\OES\Dashboard\data_model_meta_box',
            $screen->id,
            'side'
    );


    add_meta_box(
            'oes_feature',
            __('OES Features Overview', 'oes'),
            '\OES\Dashboard\features_meta_box',
            $screen->id,
            'advanced',
            'default'
    );
}

function welcome_meta_box(): void
{

    global $oes;
    if (!$oes->application_initialized) {
        _e('There is no application initialized.', 'oes'); //TODO How to initialize
    } else {
        _e('Your application has been initialized.', 'oes');
    }

    $theme = wp_get_theme();
    $themeName = esc_html($theme->get('Name'));

    echo '<p>' . esc_html__('Your OES application is running the active theme ', 'oes');

    if (current_user_can('switch_themes')) {
        echo '<strong><a href="' . esc_url(admin_url('themes.php')) . '">'
                . $themeName
                . '</a></strong> ';
    } else {
        echo '<strong>' . $themeName . '</strong> ';
    }
    echo ' (<span class="description">v' . esc_html($theme->get('Version')) . '</span>)';

    if ($theme->parent()) {
        $parent = $theme->parent();
        esc_html_e(' with parent theme ', 'oes');
        echo ' <strong>' . esc_html($parent->get('Name')) . '</strong>';
        echo ' (<span class="description">v' . esc_html($parent->get('Version')) . '</span>).';
    } else {
        echo '.';
    }

    echo '</p>';

    echo '<p>And here a list of valid OES modules that are active</p>';
}

function data_model_meta_box()
{

    global $oes;

    if (empty($oes)) {
        return;
    }

    /**
     * Helper: render object list (post types / taxonomies)
     */
    $render_object_list = static function (array $objects, string $title) {
        if (empty($objects)) {
            return;
        }

        ksort($objects);

        echo '<div>';
        echo '<strong>' . esc_html(sprintf(
                        '%d %s',
                        count($objects),
                        $title
                )) . '</strong>: ';

        $labels = [];
        foreach ($objects as $key => $object) {
            $labels[] = $object['label'] ?? $key;
        }

        echo implode(', ', $labels);
        echo '</div>';
    };

    $render_object_list($oes->post_types ?? [], __('Post Types', 'oes'));
    $render_object_list($oes->taxonomies ?? [], __('Taxonomies', 'oes'));

    echo '<div class="oes-dashboard-actions">';

    echo '<p>';
    esc_html_e(
            'This overview reflects the active OES data model. The editorial configuration may override plugin defaults.',
            'oes'
    );
    echo '</p>';

    if (\OES\Rights\user_can_edit_config()) {

        echo '<p class="oes-button-group">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=oes_tools_model')) . '" class="button button-secondary">';
        esc_html_e('View Full Status', 'oes');
        echo '</a>';
        echo '</p>';

        echo '<p class="oes-button-group">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=oes_tools_model&tab=model')) . '" class="button button-secondary">';
        esc_html_e('Export / Reload Data Model', 'oes');
        echo '</a>';
        echo '</p>';

        echo '<p class="description">';
        esc_html_e(
                'The factory allows you to edit post types, taxonomies, and field groups directly. Changes may affect URLs and frontend rendering.',
                'oes'
        );
        echo '</p>';

        echo '<p class="oes-button-group">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=oes_tools_model&tab=factory')) . '" class="button button-primary">';
        esc_html_e('Open Data Model Factory', 'oes');
        echo '</a>';
        echo '</p>';
    }

    echo '</div>';
}

//TODO @3.0.0
function content_meta_box()
{

    echo '<p>';
    esc_html_e(
            'TODO on empty ', //if demo you can load content here. what if no content
            'oes'
    );
    echo '</p>';

    echo '<p>';
    esc_html_e(
            'Currently, the following content is published and available online. Published content is publicly accessible and may be indexed by search engines, depending on your site visibility settings.',
            'oes'
    );
    echo '</p>';

    echo '<ul class="oes-content-status-list">';

    $pages = wp_count_posts('page')->publish ?? 0;

    if ($pages > 0) {
        printf(
                '<li><strong>%d</strong> %s</li>',
                intval($pages),
                esc_html__('Pages', 'oes')
        );
    }

    $postTypes = get_post_types(
            [
                    'public' => true,
                    '_builtin' => false,
            ],
            'objects'
    );
    ksort($postTypes);

    global $oes;
    foreach ($postTypes as $postType) {

        if (!isset($oes->post_types[$postType->name])) {
            continue;
        }

        $count = wp_count_posts($postType->name)->publish ?? 0;

        if ($count === 0) {
            continue;
        }

        printf(
                '<li><strong>%d</strong> <a href="%s">%s</a></li>',
                intval($count),
                get_post_type_archive_link($postType->name),
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

        if (!isset($oes->taxonomies[$taxonomy->name])) {
            continue;
        }

        $terms = wp_count_terms(
                $taxonomy->name,
                [
                        'hide_empty' => true,
                ]
        );

        if (is_wp_error($terms) || (int)$terms === 0) {
            continue;
        }

        printf(
                '<li><strong>%d</strong> %s</li>',
                intval($terms),
                esc_html($taxonomy->labels->name)
        );
    }

    echo '</ul>';
}

function features_meta_box()
{
    $features = get_features();

    if (empty($features)) {
        echo '<p class="description">';
        esc_html_e('No features are registered.', 'oes');
        echo '</p>';
        return;
    }

    echo '<p class="description">';
    esc_html_e(
            'This table lists all features currently registered in OES and by other compatible plugins.',
            'oes'
    );
    echo '</p>';
    echo '<p class="oes-button-group">';
    echo '<a href="' . esc_url(admin_url('admin.php?page=oes_settings_features')) . '" class="button button-secondary">';
    esc_html_e('View Details and Setting Options', 'oes');
    echo '</a>';
    echo '</p>';

    echo '<p class="description">';
    esc_html_e(
            'You can quick access manual links here.',
            'oes'
    ); //TODO
    echo '</p>';

    foreach ($features as $featureGroup) {

        echo '<h4>' . esc_html($featureGroup['group'] ?? 'Missing Label') . '</h4>';
        echo '<ul class="oes-feature-group-list">';

        foreach ($featureGroup['features'] ?? [] as $featureKey => $feature) {

            if(empty($feature['manual'] ?? '')){
                echo '<li class="oes-grey-out">';
                echo esc_html($feature['name'] ?? $featureKey);
                echo ' - ';
                esc_html_e('Coming soon', 'oes');
                echo '</li>';
            }
            else {
                echo '<li><a href="' . $feature['manual'] . '" target="_blank">';
                echo '<img src="' . esc_url(plugins_url(OES_BASENAME . '/assets/images/oes_manual_icon.png')) . '" alt="Manual" style="width:16px; height:16px; vertical-align:middle; margin-right:5px;">';
                echo esc_html($feature['name'] ?? $featureKey) . '</a></li>';
            }
        }
        echo '</ul>';
    }
}

function get_features(): array
{

    $features = [

        // === Extended Publishing Functions ===
            'extended_publishing' => [
                    'group' => __('Extended Publishing Functions', 'oes'),
                    'features' => [
                            'open_access' => [
                                    'name' => __('Open Access Publishing and Sustainable Referencing', 'oes'),
                                    'description' => __('Publication of content (individual articles, datasets) in open access under a Creative Commons license directly via the website.', 'oes'),
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

        // === Open and Connected Data ===
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

        // === Structure and Markup of Content ===
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

        // === Output ===
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
                                    'description' => __('todo.', 'oes'),
                                    'actions' => [
                                            'edit' => [
                                                    'page' => 'oes_settings_languages&tab=theme-date',
                                                    'label' => __('Edit Date Format', 'oes')
                                            ]
                                    ],
                            ],
                            'labels' => [
                                    'name' => __('Labels', 'oes'),
                                    'description' => __('todo', 'oes'),
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

        // === Editorial Tools ===
            'editorial_tools' => [
                    'group' => __('Editorial Tools', 'oes'),
                    'features' => [
                            'internal_data_fields' => [
                                    'name' => __('Internal Data Fields (Remarks)', 'oes'),
                                    'description' => __('Each post includes editorial status and team notes fields collected centrally.', 'oes'),
                                    'enable' => 'remarks',
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
                                    'description' => __('todo.', 'oes'),
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
                                    'description' => __('todo.', 'oes'),
                                    'actions' => [
                                            'edit' => [
                                                    'page' => 'oes_settings_advanced&tab=admin-container',
                                                'label' => __('Edit Container', 'oes')
                                            ]
                                    ],
                            ],
                            'columns' => [
                                    'name' => __('Columns', 'oes'),
                                    'description' => __('todo.', 'oes'),
                                    'actions' => [
                                            'edit' => [
                                                    'page' => 'oes_settings_advanced',
                                                    'label' => __('Edit Columns', 'oes')
                                            ]
                                    ],
                            ],
                            'caching' => [
                                    'name' => __('Caching', 'oes'),
                                    'description' => __('todo.', 'oes'),
                                    'enable' => 'cache',
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
                            'application' => [
                                    'name' => __('Application', 'oes'),
                                    'description' => __('todo.', 'oes'),
                                    'actions' => [
                                            'edit' => [
                                                    'page' => 'oes_settings_schema'
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