<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Include a file relative to plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Core Plugin path.
 * @param bool $includes Use "includes" directory. Default is true.
 * @return void
 */
function oes_include(string $file, string $root = '', bool $includes = true): void
{
    if (empty($root)) $root = OES_CORE_PLUGIN;
    $path = oes_get_path(($includes ? '/includes/' : '/') . $file, $root);
    if (file_exists($path)) include_once($path);
}


/**
 * Include a file relative to project plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Project Plugin path.
 * @param bool $includes Use "includes" directory. Default is true.
 * @return void
 */
function oes_include_project(string $file, string $root = '', bool $includes = true): void
{
    if (empty($root)) $root = OES_PROJECT_PLUGIN;
    if (function_exists('oes_include')) oes_include($file, $root, $includes);
}


/**
 * Return the path to a file within the plugin.
 *
 * @param string $path A string containing the relative path.
 * @param string $root Optional string containing the absolute path inside the plugin. Default is OES_PATH.
 * @return string Returns the path within the plugin.
 */
function oes_get_path(string $path = '', string $root = ''): string
{
    return (empty($root) ? OES_CORE_PLUGIN : $root) . $path;
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
 * @param string $root Optional string containing the absolute root path to '/includes/admin/views/'.
 *  Default is OES Core Plugin path.
 * @return void
 */
function oes_get_view(string $path = '', array $args = [], string $root = ''): void
{
    /* get default path */
    if (empty($root)) $root = OES_CORE_PLUGIN;

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
 * @return void
 */
function oes_get_project_view(string $path = '', array $args = [], string $root = ''): void
{
    /* get default path */
    if (empty($root)) $root = OES_PROJECT_PLUGIN;
    if (function_exists('oes_get_view')) oes_get_view($path, $args, $root);
}


/**
 * Get data from json file.
 *
 * @param string $path The file path.
 * @return array Returns decoded json file or empty.
 */
function oes_get_json_data_from_file(string $path): array
{
    /* check if path empty */
    if (empty($path)) {
        oes_write_log(__('Empty file.', 'oes'));
        return [];
    }

    /* check if file exists */
    if (!file_exists($path)) {
        oes_write_log(sprintf(__('File "%s" not found.', 'oes'), $path));
        return [];
    }

    /* get file content */
    $fileAsString = file_get_contents($path);

    /* check if file is empty */
    if (empty($fileAsString)) {
        oes_write_log(sprintf(__('File "%s" has no content.', 'oes'), $path));
        return [];
    }

    return json_decode($fileAsString, true);
}


/**
 * Export data as json file.
 *
 * @param string $name The file name (should end with .json)
 * @param array $data The data as array.
 * @return bool Return false if json encoding run into a problem.
 */
function oes_export_json_data(string $name, array $data): bool
{
    header('Content-disposition: attachment; filename=' . $name);
    header('Content-type: application/json');
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo $json;
    return is_string($json);
}


/**
 * Hide WordPress Update notifications
 */
function oes_hide_update_notification(): void
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
 * @return void
 */
function oes_hide_obsolete_menu_structure(): void
{
    add_action('admin_menu', 'oes_hide_obsolete_menu_structure_filter');
}


/**
 * Hide obsolete WordPress menu structure (action call).
 * @return void
 */
function oes_hide_obsolete_menu_structure_filter(): void
{
    remove_menu_page('jetpack'); /* Jetpack (not needed for OES) */
    remove_menu_page('edit.php'); /* Posts (not needed for OES) */
    remove_menu_page('edit-comments.php'); /* Comments (not needed for OES) */
}


/**
 * Add a field group to the page object containing the language field.
 *
 * @param array $fieldTypes
 * @return void
 */
function oes_add_fields_to_page(array $fieldTypes = []): void
{
    \OES\Model\add_fields_to_page($fieldTypes);
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
    if (file_exists(OES_CORE_PLUGIN . $customIconPath))
        if (getimagesize(OES_CORE_PLUGIN . $customIconPath))
            $menuIcon = plugins_url(OES_BASENAME. $customIconPath);

    /* overwrite default */
    if (!$menuIcon) $menuIcon = 'dashicons-clipboard';
    return $menuIcon;
}


/**
 * Compare two arrays or values and merge recursively (array_merge_recursive didn't do the trick).
 *
 * @param mixed $array1 The array or value to be merged into.
 * @param mixed $array2 The array or value that will be merged into $array1.
 * @return mixed Returns the merged array or the merged value.
 */
function oes_merge_array_recursively($array1, $array2, $inner = false)
{
    foreach ($array1 as $key => $value)
        if (isset($array2[$key])) {
            if (!$inner && is_array($value)) {
                $array1[$key] = oes_merge_array_recursively($value, $array2[$key], true);
            } elseif ($array2[$key] != $value) {
                $array1[$key] = $array2[$key];
            }
        }
    return $array1;
}


/**
 * Compare two arrays or values and combine recursively (array_merge_recursive didn't do the trick).
 *
 * @param mixed $array1 The array or value to be combined into.
 * @param mixed $array2 The array or value that will be combined into $array1.
 * @return mixed Returns the combined array or the combined value.
 */
function oes_combine_array_recursively($array1, $array2, $depth = 'all')
{

    if ($depth === 'all' or (int)$depth > 0) {

        if (is_int($depth)) $depth--;
        if (is_array($array1))
            foreach ($array1 as $key => $value)
                if (is_array($array2) && isset($array2[$key]))
                    $array1[$key] = oes_combine_array_recursively($value, $array2[$key], $depth);

        /* add old values */
        if (is_array($array2))
            foreach ($array2 as $key2 => $value)
                if (!isset($array1[$key2])) $array1[$key2] = $value;
    }

    return $array1;
}


/**
 * Check if option exists in database. get_option will return false negatives for null, 0, '' and miss 'false'.
 *
 * @param string $option The option name.
 * @param bool $siteWide Search site wide. Default is false.
 * @return bool|int|mysqli_result|resource|null Return the option.
 */
function oes_option_exists(string $option, bool $siteWide = false)
{
    global $wpdb;
    return $wpdb->query($wpdb->prepare("SELECT * FROM " . ($siteWide ? $wpdb->base_prefix : $wpdb->prefix) .
        "options WHERE option_name ='%s' LIMIT 1", $option));
}


/**
 * Log messages for WordPress Debugging
 * @oesDevelopment Choose other form of display, use messaging instead.
 * 
 * @param mixed $messages The log messages.
 * @return bool Return false for further processing.
 */
function oes_write_log($messages): bool {
    if (true === WP_DEBUG) {
        if (is_array($messages) || is_object($messages)) error_log(print_r($messages, true));
        else error_log($messages);
    }
    return false;
}