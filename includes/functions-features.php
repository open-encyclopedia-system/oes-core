<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Features;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Includes various utility functions essential for handling fields, HTML, posts, and text processing across the plugin.
 * These functions are used throughout the OES plugin to support various operations.
 *
 * @return void
 */
function utility_functions(): void
{
    include_once __DIR__ . '/functions-field.php';
    include_once __DIR__ . '/functions-html.php';
    include_once __DIR__ . '/functions-post.php';
    include_once __DIR__ . '/functions-text-processing.php';
}

/**
 * Creates and initializes the database tables required for OES operations.
 * This feature enables the "Operations" functionality, allowing data from CSV files to be transformed into OES operations
 * that can be evaluated before importing as posts, tags, etc.
 *
 * @return void
 */
function database(): void
{
    if (is_admin()) {
        include_once __DIR__ . '/admin/db/initialize-db.php';
        include_once __DIR__ . '/admin/db/class-operation.php';
        include_once __DIR__ . '/admin/db/functions-operation.php';
    }
}

/**
 * Includes the tools necessary for configuring the OES data model and its behavior via the admin panel.
 * This feature enables tools such as the data model configuration tool, import/export tools, and general tools for managing
 * OES features.
 *
 * @oesDevelopment Make sure this is only included if needed.
 * @return void
 */
function tools(): void
{
    include_once __DIR__ . '/admin/tools/class-tool.php';
    include_once __DIR__ . '/admin/tools/class-form_table.php';
    include_once __DIR__ . '/admin/tools/functions-tool.php';
    include_once __DIR__ . '/admin/functions-shortcode.php';
    if (is_admin()) {
        add_action('admin_init', '\OES\Admin\Tools\include_tools');
    }
}

/**
 * Includes messaging functionality to display admin notices within the editorial layer.
 * This feature enables the display of admin messages to provide notifications or instructions to users in the admin interface.
 *
 * @oesDevelopment
 * @return void
 */
function admin_messages(): void
{
    if (is_admin()) {
        include_once __DIR__ . '/admin/functions-notices.php';
        add_action('admin_notices', 'OES\Admin\display_oes_notices_after_refresh', 12);
    }
}

/**
 * Includes various admin functionalities that enhance the editorial layer.
 * This feature enables functionalities such as modifying the page icon, adding select2 classes, and more.
 *
 * @return void
 */
function admin_functions(): void
{

    include_once __DIR__ . '/admin/functions-admin.php';
    include_once __DIR__ . '/admin/functions-acf.php';
    if (is_admin()) {
        add_action('admin_head', '\OES\Admin\set_page_icon');
        add_filter('admin_body_class', '\OES\Admin\set_oes_body_class');
    }
}

/**
 * Sets up the dashboard in the editorial layer for OES purposes.
 * This feature customizes the admin dashboard by adding elements like a welcome message specific to OES.
 *
 * @oesDevelopment
 * @param bool $enabled Whether the dashboard setup should be enabled. Defaults to true.
 *
 * @return void
 */
function dashboard(bool $enabled = true): void
{
    if (is_admin() && $enabled) {
        include_once __DIR__ . '/admin/functions-dashboard.php';
        add_action('wp_dashboard_setup', '\OES\Dashboard\modify');
    }
}

/**
 * Include admin pages inside the editorial layer.
 *
 * This enables the "Admin Pages" feature, allowing for admin pages within the editorial layer
 * and providing functionalities for these pages, such as settings options or container pages.
 *
 * @return void
 */
function admin_pages(): void
{
    if (is_admin()) {
        include_once __DIR__ . '/admin/pages/class-page.php';
        include_once __DIR__ . '/admin/pages/class-subpage.php';
        include_once __DIR__ . '/admin/pages/class-container.php';
        include_once __DIR__ . '/admin/pages/class-module_page.php';
        include_once __DIR__ . '/admin/pages/functions-pages.php';
        include_once __DIR__ . '/admin/functions-help_tabs.php';
        add_action('admin_enqueue_scripts', 'OES\Admin\add_page_scripts');
        add_action('oes/data_model_registered', 'OES\Admin\initialize_admin_menu_pages');
        add_action('oes/data_model_registered', '\OES\Admin\initialize_container_pages');
        add_action('admin_head', '\OES\Admin\help_tab');
    }
}

/**
 * Include assets.
 *
 * This function enqueues all CSS and JS assets needed within the editorial layer for the OES Core plugin.
 *
 * @oesDevelopment Unify enqueue with action calls.
 *
 * @return void
 */
function assets(): void
{
    include_once __DIR__ . '/admin/functions-assets.php';
    add_action('wp_enqueue_scripts', 'oes_register_scripts_and_styles');
    oes_add_style('oes-theme', '/assets/css/theme.css');
    if (is_admin()) {
        add_action('admin_enqueue_scripts', 'oes_load_assets');
        oes_add_style_admin('oes-admin', '/assets/css/admin.css');
        oes_add_script_admin('oes-admin', '/assets/js/admin.js');
    }
}

/**
 * Include modification of columns for post type lists inside the editorial layer.
 *
 * This enables the "Admin Columns" feature, allowing for the modification of columns in the post type lists
 * inside the editorial layer.
 *
 * @return void
 */
function columns(): void
{
    if (is_admin()) {
        include_once __DIR__ . '/admin/functions-columns.php';
        add_action('restrict_manage_posts', '\OES\Admin\columns_filter_dropdown', 10, 2);
        add_action('pre_get_posts', '\OES\Admin\columns_pre_get_posts');
    }
}

/**
 * Include user-specific settings.
 *
 * This enables the "OES Profile" feature, which allows for adding user-specific settings to a user profile.
 *
 * @return void
 */
function user_profile_settings(): void
{
    include_once __DIR__ . '/admin/functions-profile.php';
    add_action('show_user_profile', '\OES\Profile\add_settings');
    add_action('edit_user_profile', '\OES\Profile\add_settings');
    add_action('personal_options_update', '\OES\Profile\save_settings');
    add_action('edit_user_profile_update', '\OES\Profile\save_settings');
    add_action('wp_footer', '\OES\Profile\show_language_box');
}

/**
 * Include the data model.
 *
 * This enables the "Data Model" feature, allowing for the modification of WordPress objects such as 'post type'
 * and 'taxonomy'. It creates custom post types and taxonomies according to the project data model file.
 * Additionally, it generates forms with fields for structured data.
 *
 * @return void
 */
function data_model(): void
{
    include_once __DIR__ . '/admin/functions-model.php';
    add_filter('init', '\OES\Model\register_model');
}

/**
 * Include the factory.
 *
 * This enables the "Factory" feature, allowing for modification of the OES data model through the editorial layer.
 *
 * @return void
 */
function data_model_factory(): void
{
    if (is_admin()) {
        include_once __DIR__ . '/admin/functions-factory.php';
        if (get_option('oes_admin-factory_mode')) {
            add_action('admin_notices', '\OES\Factory\display_factory_notice');
            add_filter('acf/post_type/registration_args', '\OES\Factory\set_global_parameters', 10, 2);
            add_filter('acf/taxonomy/registration_args', '\OES\Factory\set_global_parameters', 10, 2);
            add_filter('acf/field_group/additional_field_settings_tabs', '\OES\Factory\additional_field_settings_tabs');
            add_action('acf/field_group/render_field_settings_tab/oes', '\OES\Factory\render_field_settings_tab');
            add_action('acf/field_group/render_field_settings_tab/oes_language', '\OES\Factory\render_field_settings_tab_language');
        }
    }
}

/**
 * Include calculated data values.
 *
 * This enables the "Formula" feature, which allows specifying a pattern for fields, post titles, or post names.
 * The field value, post title, or post name is then computed according to the specified pattern (e.g., citation field).
 *
 * @return void
 */
function formula_functions(): void
{
    include_once __DIR__ . '/functions-formulas.php';
    add_action('acf/save_post', '\OES\Formula\calculate_post_args_from_formula');
    add_filter('acf/update_value', '\OES\Formula\calculate_field_value_from_formula', 10, 3);
}

/**
 * Include the versioning feature for OES post types.
 *
 * This enables the "Versioning" feature, which creates a version-controlled post type "Parent" and a version-controlled
 * post type "Version" that includes specific fields, such as a version number. The "Translating" feature is a special case
 * of "Versioning" where a version-controlled post "Origin Post" is linked to a post "Translation" of the same post type.
 *
 * @return void
 */
function versioning(): void
{
    include_once __DIR__ . '/functions-versioning.php';
    if (is_admin()) {
        add_filter('get_sample_permalink_html', 'OES\Versioning\get_sample_permalink_html', 10, 2);
        add_action('add_meta_boxes', 'OES\Versioning\add_meta_boxes');
        add_action('save_post', 'OES\Versioning\save_post', 10, 2);
        add_action('admin_action_oes_copy_version', 'OES\Versioning\admin_action_oes_copy_version');
        add_action('admin_action_oes_create_version', 'OES\Versioning\admin_action_oes_create_version');
        add_action('admin_action_oes_create_translation', 'OES\Versioning\admin_action_oes_create_translation');
    }
}

/**
 * Include icons feature.
 *
 * This enables the "Icons" feature, to enable the use of inline SVG icons via the \OES\Icon namespace.
 *
 * @return void
 */
function icons(): void
{
    include_once __DIR__ . '/theme/icons/class-icon_manager.php';
    include_once __DIR__ . '/theme/icons/class-icons.php';
    include_once __DIR__ . '/theme/icons/functions-icons.php';
}

/**
 * Include popup feature.
 *
 * This enables the "Popup" feature, providing an editor tool where text can be marked to be displayed as a popup.
 *
 * @return void
 */
function popup(): void
{
    include_once __DIR__ . '/popup/functions-popup.php';

    oes_add_style('oes-popup', '/includes/popup/assets/popup.css');
    oes_add_script('oes-popup', '/includes/popup/assets/popup.js');
    oes_add_script_admin('oes-admin-popup', '/assets/js/admin-popup.min.js', ['jquery']);
    oes_add_script_admin('oes-popup-admin', '/includes/popup/assets/popup-admin.js', ['wp-rich-text', 'wp-element', 'wp-editor']);
    add_filter('the_content', '\OES\Popup\render_for_frontend');
}

/**
 * Include notes feature.
 *
 * This enables the "Notes" feature, which allows marking text as a note. The note is rendered as a popup and linked
 * to a computed number, creating a list of all existing notes in the text.
 *
 * @return void
 */
function notes(): void
{
    oes_add_style('oes-note', '/includes/popup/assets/note.css');
    oes_add_script('oes-note', '/includes/popup/assets/note.js');
    oes_add_script_admin('oes-note-admin', '/includes/popup/assets/note-admin.js', ['wp-rich-text', 'wp-element', 'wp-editor']);
    include_once __DIR__ . '/popup/functions-note.php';
    add_filter('oes/popups_render_for_frontend', '\OES\Popup\add_notes_replace_variable');
}

/**
 * Include OES Core blocks for the Gutenberg editor.
 *
 * This enables the "Blocks" feature. OES blocks can be used to display OES-modified posts or taxonomies
 * on the frontend. It also includes blocks for features such as featured posts, a print button, or a language switch.
 *
 * @return void
 */
function blocks(): void
{
    oes_add_style('oes-blocks', '/includes/blocks/blocks.css', [], null, 'all', true);
    include_once __DIR__ . '/blocks/functions-blocks.php';
    add_filter('block_categories_all', '\OES\Block\register_categories');
    add_action('enqueue_block_assets', '\OES\Block\assets', 1);
}

/**
 * Include theme classes and functions.
 *
 * This enables the "Data Processing" feature. It modifies the WordPress display for pages, posts, and
 * taxonomies by adding OES data, such as metadata for posts, version information, dropdown data in list
 * displays, etc.
 *
 * @oesDevelopment Is thie needed for caching or could this be only called in frontend?
 * @return void
 */
function theme_classes(): void
{
    include_once __DIR__ . '/theme/functions-theme.php';
    include_once __DIR__ . '/theme/data/functions-data.php';
    include_once __DIR__ . '/theme/data/functions-parts.php';
    add_filter('init', 'oes_set_language_cookie', 999);
    add_filter('the_content', 'oes_the_content', 12, 1);
    add_filter('render_block_core/heading', 'oes_render_block_core_heading', 10, 2);
    include_once __DIR__ . '/theme/data/class-object.php';
    include_once __DIR__ . '/theme/data/class-post.php';
    include_once __DIR__ . '/theme/data/class-page.php';
    include_once __DIR__ . '/theme/data/class-attachment.php';
    include_once __DIR__ . '/theme/data/class-term.php';
    include_once __DIR__ . '/theme/data/class-taxonomy.php';
    include_once __DIR__ . '/theme/data/class-archive.php';
    include_once __DIR__ . '/theme/data/class-archive_loop.php';
    include_once __DIR__ . '/theme/data/class-post_archive.php';
    include_once __DIR__ . '/theme/data/class-taxonomy_archive.php';
    include_once __DIR__ . '/theme/data/class-index_archive.php';

    include_once __DIR__ . '/theme/data/class-template_redirect.php';
    add_action('template_redirect', 'oes_prepare_data');
}

/**
 * Include caching functions.
 *
 * This enables the "Caching" feature to store the cache in the database and ensure it persists across page loads.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function cache(bool $enabled = true): void
{
    if ($enabled) {
        include_once __DIR__ . '/admin/functions-caching.php';

        if (is_admin()) {
            add_action('save_post', '\OES\Caching\clear_archive_cache');
            add_action('deleted_post', '\OES\Caching\clear_archive_cache');
            add_action('trashed_post', '\OES\Caching\clear_archive_cache');
        }
    }
}

/**
 * Include figures.
 *
 * This enables the "Figures" feature. It modifies the display of images and galleries by adding a lightbox
 * and additional caption text.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function figures(bool $enabled = true): void
{
    if ($enabled) {
        include_once __DIR__ . '/theme/figures/functions-figures.php';
        include_once __DIR__ . '/theme/figures/functions-panel.php';
        include_once __DIR__ . '/theme/figures/class-panel.php';
        include_once __DIR__ . '/theme/figures/class-gallery_panel.php';
        include_once __DIR__ . '/theme/figures/class-image_panel.php';
        oes_add_style('oes-panel', '/includes/theme/figures/panel.css');
        oes_add_script('oes-figures', '/includes/theme/figures/figures.min.js', ['jquery']);
    }
}

/**
 * Include filter.
 *
 * This enables the "Filter" feature. It creates a filter that can be used in list displays of posts on the
 * frontend.
 *
 * @return void
 */
function filter(): void
{
    include_once __DIR__ . '/theme/filter/functions-filter.php';
    include_once __DIR__ . '/theme/filter/class-filter_renderer.php';
    oes_add_script('oes-filter', '/includes/theme/filter/filter.min.js');
}

/**
 * Include label functionality.
 *
 * This enables the "Labels" feature, which provides a function to retrieve labels.
 *
 * @return void
 */
function labels(): void
{
    include_once __DIR__ . '/theme/functions-labels.php';
}

/**
 * Include language switch.
 *
 * This enables the "Language Switch" feature, which is used in projects that include two or more languages.
 * The switch allows users to change the website's language.
 *
 * @oesDevelopment Is this needed outside of frontend?
 *
 * @param bool $blockTheme
 * @return void
 */
function language_switch(bool $blockTheme = true): void
{
    include_once __DIR__ . '/theme/navigation/class-language_switch.php';
    include_once __DIR__ . '/theme/navigation/functions-navigation.php';

    foreach ([
                 '404_template_hierarchy',
                 'page_template_hierarchy',
                 'frontpage_template_hierarchy',
                 'single_template_hierarchy',
                 'archive_template_hierarchy',
             ] as $hook) {
        add_filter($hook, '\OES\Navigation\redirect_page');
    }

    if ($blockTheme) {
        include_once __DIR__ . '/theme/navigation/functions-classic_menu.php';
        add_action('admin_head-customize.php', '\OES\Navigation\admin_head_nav_menus');
        add_action('admin_head-nav-menus.php', '\OES\Navigation\admin_head_nav_menus');
        add_action('init', '\OES\Navigation\init');
        add_filter('nav_menu_item_title', '\OES\Navigation\nav_menu_item_title', 10, 2);
        add_filter('nav_menu_link_attributes', '\OES\Navigation\nav_menu_link_attributes', 10, 2);
        add_filter('nav_menu_css_class', '\OES\Navigation\nav_menu_css_class', 10, 2);
    }
}

/**
 * Include search modification.
 *
 * This enables the "Search" feature. It modifies the WordPress search functionality and the display of the results.
 * @return void
 */
function search(): void
{
    if (!is_admin()) {
        include_once __DIR__ . '/theme/search/class-search.php';
    }

    //needed for ajax call
    include_once __DIR__ . '/theme/search/functions-search.php';
    include_once __DIR__ . '/theme/search/class-search_results.php';
    include_once __DIR__ . '/theme/search/class-search_query.php';
}

/**
 * Include LOD API.
 *
 * This enables the "Linked Open Data" feature, which allows searching for linked open data, such as GND or
 * Geonames. The results can be used as shortcodes or copied into post objects.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function lod_api(bool $enabled = true): void
{
    if ($enabled) {
        include_once __DIR__ . '/api/functions-rest_api.php';
        include_once __DIR__ . '/api/class-rest_api.php';
        add_action('wp_enqueue_scripts', '\OES\API\scripts');
        add_action('oes/initialized', '\OES\API\initialize');
        add_action('wp_ajax_oes_lod_search_query', '\OES\API\lod_search_query');
        add_action('wp_ajax_oes_lod_add_post_meta', '\OES\API\lod_add_post_meta');
        add_action('wp_ajax_oes_lod_box', '\OES\API\lod_box');
        add_action('wp_ajax_nopriv_oes_lod_box', '\OES\API\lod_box');

        if (is_admin()) {
            add_action('admin_enqueue_scripts', '\OES\API\admin_scripts');
            add_action('enqueue_block_editor_assets', '\OES\API\sidebar_enqueue');
        }
    }
}

/**
 * Include admin OES remarks.
 *
 * This enables the "OES Remarks" feature. It creates a page containing all OES remarks.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function remarks(bool $enabled = true): void
{
    if (is_admin() && $enabled) {
        include_once __DIR__ . '/admin/remarks/functions-remarks.php';
        add_action('admin_menu', '\OES\Remarks\create_page');
        add_filter('set-screen-option', '\OES\Remarks\set_screen_option', 10, 3);
    }
}

/**
 * Include admin manual.
 *
 * This enables the "Admin Manual" feature, which allows creating internal manual pages.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function manual(bool $enabled = true): void
{
    if (is_admin() && $enabled) {
        include_once __DIR__ . '/admin/functions-manual.php';
        add_action('init', '\OES\Manual\register');
        add_filter('parent_file', '\OES\Manual\modify_parent_file');
        add_action('admin_enqueue_scripts', '\OES\Manual\include_style');
        add_action('admin_head', '\OES\Manual\include_stylesheet');
        add_action('admin_footer', '\OES\Manual\toc_panel');
    }
}

/**
 * Include admin tasks.
 *
 * This enables the "Tasks" feature. It allows creating and managing internal tasks.
 *
 * @param bool $enabled Whether the feature setup should be enabled. Defaults to true.
 * @return void
 */
function tasks(bool $enabled = true): void
{
    if (is_admin() && $enabled) {
        include_once __DIR__ . '/admin/functions-tasks.php';
        add_action('init', '\OES\Tasks\init');
        add_action('manage_oes_task_posts_custom_column', '\OES\Tasks\posts_custom_column', 10, 2);
        add_filter('get_sample_permalink_html', '\OES\Tasks\hide_permalink', 10, 2);
        add_filter('acf/update_value/key=field_oes_task__date', '\OES\Tasks\update_value');
        add_filter('manage_oes_task_posts_columns', '\OES\Tasks\posts_columns');
        add_action('manage_edit-oes_task_sortable_columns', '\OES\Tasks\sortable_columns');
    }
}

/**
 * Include shortcodes.
 *
 * This enables the use of shortcodes.
 *
 * @return void
 */
function shortcodes(): void
{
    include_once __DIR__ . '/shortcodes.php';
}
