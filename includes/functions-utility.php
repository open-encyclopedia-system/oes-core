<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Include a file relative to plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Core Plugin path.
 */
function oes_include(string $file, $root = false)
{
    if (!$root) $root = OES()->path_core_plugin;
    $path = oes_get_path($file, $root);
    if (file_exists($path)) include_once($path);
}


/**
 * Include a file relative to project plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string|bool $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Project Plugin path.
 */
function oes_include_project(string $file, $root = false)
{
    if (!$root) $root = OES()->path_project_plugin;
    if (function_exists('oes_include')) oes_include($file, $root);
}


/**
 * Return the path to a file within the plugin.
 *
 * @param string $path A string containing the relative path.
 * @param string|bool $root Optional string containing the absolute path inside the plugin. Default is OES_PATH.
 * @return string Returns the path within the plugin.
 */
function oes_get_path(string $path = '', $root = false): string
{
    if (!$root) $root = OES()->path_core_plugin;
    return $root . $path;
}


/**
 * Validate file. Return true if file exists and is readable, return error message if not.
 *
 * @param string $file A string containing the file.
 * @return bool|string Returns true or error message.
 */
function oes_validate_file(string $file)
{
    if (!file_exists($file)) return 'File not found ' . $file . '.';
    else if (!is_readable($file)) return 'File not readable ' . $file . '.';
    return true;
}


/**
 * Get OES plugin version
 *
 * @return string|null Returns OES plugin version or null.
 */
function oes_get_version(): ?string
{
    return OES()->get_version();
}


/**
 * Include a file containing views for the admin layer.
 *
 * @param string $path Optional string containing the path relative to '/includes/admin/views/' to the view file.
 * @param array $args Optional array containing further arguments to be extracted from file.
 * @param string|bool $root Optional string containing the absolute root path to '/includes/admin/views/'.
 *  Default is OES Core Plugin path.
 */
function oes_get_view(string $path = '', array $args = [], $root = false)
{
    /* get default path */
    if (!$root) $root = OES()->path_core_plugin;

    /* allow view file name shortcut */
    if (!oes_ends_with($path, '.php'))
        $path = oes_get_path("/includes/admin/views/$path.php", $root);

    /* include file if existing */
    if (file_exists($path)) {
        extract($args);
        include($path);
    }
}


/**
 * Include a file containing views for the admin layer for the project plugin.
 *
 * @param string $path Optional string containing the path relative to '/includes/admin/views/' to the view file.
 * @param array $args Optional array containing further arguments to be extracted from file.
 * @param string $root |bool Optional string containing the absolute root path to '/includes/admin/views/'.
 *  Default is OES Core Project Plugin path.
 */
function oes_get_project_view(string $path = '', array $args = [], $root = false)
{
    /* get default path */
    if (!$root) $root = OES()->path_project_plugin;
    if (function_exists('oes_get_view')) oes_get_view($path, $args, $root);
}


/**
 * Get data from json file.
 *
 * @param string $path The file path.
 * @return array|mixed Returns decoded json file or empty.
 */
function oes_get_json_data_from_file(string $path)
{

    /* get global OES variable to get and store file information */
    $oes = OES();

    /* check if path empty */
    if (empty($path)) {
        $oes->errors[] = sprintf(__('Empty file.', 'oes'), $path);
        return [];
    }

    /* check if file exists */
    if (!file_exists($path)) {
        $oes->errors[] = sprintf(__('File "%s" not found.', 'oes'), $path);
        return [];
    }

    /* get file content */
    $fileAsString = file_get_contents($path);

    /* check if file is empty */
    if (empty($fileAsString)) {
        $oes->errors[] = sprintf(__('File "%s" has no content.', 'oes'), $path);
        return [];
    }

    return json_decode($fileAsString, true);
}


/**
 * Export data as json file.
 *
 * @param string $name The file name (should end with .json)
 * @param array $data The data as array.
 */
function oes_export_json_data(string $name, array $data)
{
    header('Content-disposition: attachment; filename=' . $name);
    header('Content-type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


/**
 * Hide WordPress Update notifications
 */
function oes_hide_update_notification()
{
    add_filter('pre_site_transient_update_core', 'oes_hide_update_notification_filter');
    add_filter('pre_site_transient_update_plugins', 'oes_hide_update_notification_filter');
    add_filter('pre_site_transient_update_themes', 'oes_hide_update_notification_filter');
}


/**
 * Hide WordPress Update notification for screen.
 * @return object
 */
function oes_hide_update_notification_filter(): object
{
    global $wp_version;
    return (object)['last_checked' => time(), 'version_checked' => $wp_version];
}


/**
 * Hide obsolete WordPress menu structure.
 */
function oes_hide_obsolete_menu_structure()
{
    add_action('admin_menu', 'oes_hide_obsolete_menu_structure_filter');
}


/**
 * Hide obsolete WordPress menu structure (action call).
 */
function oes_hide_obsolete_menu_structure_filter()
{
    remove_menu_page('jetpack'); /* Jetpack (not needed for OES) */
    remove_menu_page('edit.php'); /* Posts (not needed for OES) */
    remove_menu_page('edit-comments.php'); /* Comments (not needed for OES) */
}


/**
 * Get OES custom icon for menu icon.
 *
 * @param string $identifier The identifier for the custom icon. Valid options are: default, secondary, parent.
 * @return string Returns path to custom icon or default menu icon.
 */
function oes_get_menu_icon_path(string $identifier = 'default'): string
{
    /* overwrite menu icon default */
    $customIconPath = '/assets/images/oes_cubic_18x18.png';
    if ($identifier == 'second') $customIconPath = '/assets/images/oes_cubic_18x18_second.png';
    elseif ($identifier == 'parent') $customIconPath = '/assets/images/oes_cubic_18x18_parent.png';
    elseif ($identifier == 'admin') $customIconPath = '/assets/images/oes_cubic_18x18_admin.png';

    $menuIcon = false;
    $oes = OES();
    if (file_exists($oes->path_core_plugin . $customIconPath))
        if (getimagesize($oes->path_core_plugin . $customIconPath))
            $menuIcon = plugins_url($oes->basename . $customIconPath);

    /* overwrite default */
    if (!$menuIcon) $menuIcon = 'dashicons-clipboard';
    return $menuIcon;
}


/**
 * Compare two arrays or values and merge recursively (array_merge_recursive didn't do the trick).
 *
 * @param mixed $value1 The array or value to be merged into.
 * @param mixed $value2 The array or value that will be merged into $value1.
 * @return mixed Returns the merged array or the merged value.
 */
function oes_merge_array_recursively($value1, $value2, $inner = false)
{
    foreach ($value1 as $key => $value)
        if (isset($value2[$key])) {
            if (!$inner && is_array($value)) {
                $value1[$key] = oes_merge_array_recursively($value, $value2[$key], true);
            } elseif ($value2[$key] != $value) {
                $value1[$key] = $value2[$key];
            }
        }
    return $value1;
}


/**
 * Check if option exists in database.
 *
 * @param string $option The option name.
 * @param bool $siteWide Search site wide. Default is false.
 * @return bool|int|mysqli_result|resource|null
 */
function oes_option_exists(string $option, bool $siteWide = false)
{
    global $wpdb;
    return $wpdb->query($wpdb->prepare("SELECT * FROM " . ($siteWide ? $wpdb->base_prefix : $wpdb->prefix) .
        "options WHERE option_name ='%s' LIMIT 1", $option));
}