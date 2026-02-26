<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add favicon to WordPress admin pages. This overwrites the WordPress favicon settings.
 * @return void
 */
function set_page_icon(): void
{
    echo '<link rel="icon" type="image/x-icon" href="' .
        plugins_url(OES_BASENAME . '/assets/images/favicon.ico') .
        '" />';
}

/**
 * Add classes for OES settings and tools pages.
 *
 * @param string $classes The current classes.
 * @return string The modified classes.
 */
function set_oes_body_class(string $classes = ''): string {
    if(isset($_GET['page']) && str_starts_with($_GET['page'], 'oes_')) $classes .= ' oes-page';
    return $classes;
}

/**
 * Get message to display if user is not an admin.
 *
 * @return string Return display message.
 */
function get_admin_user_only_message(): string {
    if (!\OES\Rights\user_is_oes_admin())
        return '<div class="notice notice-info">' .
            __('Sorry, you are not allowed to use this tool. You must be an OES admin to access this tool.', 'oes') .
            '</div>';
    return '';
}

/**
 * Get enabled OES features as stored in option.
 *
 * @return array Return the OES features as array.
 */
function get_features(): array
{
    $features = get_option('oes_features');
    return is_string($features) ? json_decode($features, true) : [];
}

/**
 * Update OES features as stored in option.
 */
function update_features(array $features = []): void
{
    update_option('oes_features', json_encode($features));
}

/**
 * Get enabled OES feature as stored in option.
 *
 * @return mixed Return the OES feature.
 */
function get_feature(string $featureKey)
{
    $features = get_features();
    return $features[$featureKey] ?? false;
}

/**
 * Add custom action links to the plugin row on the Plugins screen.
 *
 * @param array $actions Associative array of existing plugin action links.
 *
 * @return array Modified array of plugin action links.
 */
function plugin_action_links(array $actions): array
{
    $new = [
        'oes-settings' => sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=oes_settings')),
            esc_html__('Settings', 'oes')
        ),
        'oes-tools' => sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=oes_tools')),
            esc_html__('Tools', 'oes')
        ),
        'oes-manual' => sprintf(
            '<a href="https://manual.open-encyclopedia-system.org/" target="_blank">%s</a>',
            esc_html__('Manual', 'oes')
        )
    ];
    return array_merge($new, $actions);
}

/**
 * Get application-specific options.
 *
 * @param array $options
 * @return array
 */
function get_application_settings(array $options = []): array
{
    $options = apply_filters('oes/project_options', $options); //@oesLegacy
    return apply_filters('oes/application_options', $options);
}

/**
 * Toggle an OES feature option
 * @return void
 */
function toggle_feature(): void
{
    check_admin_referer('oes_toggle_feature');

    if (!\OES\Rights\user_is_oes_admin()) {
        wp_die(__('You are not allowed to change this.', 'oes'));
    }

    $setting = sanitize_key($_GET['setting'] ?? '');

    if (!$setting) {
        wp_die(__('Missing feature.', 'oes'));
    }

    $features = get_features();

    $current = !empty($features[$setting]);
    $features[$setting] = !$current;

    update_features($features);

    wp_safe_redirect(
        add_query_arg(
            ['updated' => 1],
            admin_url('admin.php?page=oes_settings_features')
        )
    );

    exit;
}