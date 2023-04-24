<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Include a file relative to plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Core Plugin path.
 * @return void
 */
function oes_include(string $file, string $root = ''): void
{
    if (empty($root)) $root = OES()->path_core_plugin;
    $path = oes_get_path($file, $root);
    if (file_exists($path)) include_once($path);
}


/**
 * Include a file relative to project plugin path.
 *
 * @param string $file A string containing the file name including the relative path to $root.
 * @param string $root Optional string containing the absolute path inside the plugin to the file.
 *  Default is OES Project Plugin path.
 * @return void
 */
function oes_include_project(string $file, string $root = ''): void
{
    if (empty($root)) $root = OES()->path_project_plugin;
    if (function_exists('oes_include')) oes_include($file, $root);
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
    if (empty($root)) $root = OES()->path_core_plugin;
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
 * @param string $root Optional string containing the absolute root path to '/includes/admin/views/'.
 *  Default is OES Core Plugin path.
 * @return void
 */
function oes_get_view(string $path = '', array $args = [], string $root = ''): void
{
    /* get default path */
    if (empty($root)) $root = OES()->path_core_plugin;

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
    if (empty($root)) $root = OES()->path_project_plugin;
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
 * @return void
 */
function oes_export_json_data(string $name, array $data): void
{
    header('Content-disposition: attachment; filename=' . $name);
    header('Content-type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
 * Compare two arrays or values and combine recursively (array_merge_recursive didn't do the trick).
 *
 * @param mixed $value1 The array or value to be combined into.
 * @param mixed $value2 The array or value that will be combined into $value1.
 * @return mixed Returns the combined array or the combined value.
 */
function oes_combine_array_recursively($value1, $value2, $depth = 'all')
{

    if ($depth === 'all' or (int)$depth > 0) {

        if (is_int($depth)) $depth--;
        if (is_array($value1))
            foreach ($value1 as $key => $value)
                if (is_array($value2) && isset($value2[$key]))
                    $value1[$key] = oes_combine_array_recursively($value, $value2[$key], $depth);

        /* add old values */
        if (is_array($value2))
            foreach ($value2 as $key2 => $value)
                if (!isset($value1[$key2])) $value1[$key2] = $value;
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


/**
 * Convert hex color string to rgb array
 *
 * @param string $hex The hex string.
 * @param bool $alpha The opacity argument.
 * @return array Return an array with rgb values. Return black, if conversion fails.
 */
function oes_hex_to_rgb(string $hex, bool $alpha = false): array
{
    /* clean string */
    $hex = str_replace('#', '', $hex);

    $rgb = ['r' => 0, 'g' => 0, 'b' => 0];
    switch(strlen($hex)){

        case '6' :
            $rgb = [
                'r' => hexdec(substr($hex, 0, 2)),
                'g' => hexdec(substr($hex, 2, 2)),
                'b' => hexdec(substr($hex, 4, 2))
            ];
            break;

        case'3':
            $rgb = [
                'r' => hexdec(str_repeat(substr($hex, 0, 1), 2)),
                'g' => hexdec(str_repeat(substr($hex, 1, 1), 2)),
                'b' => hexdec(str_repeat(substr($hex, 2, 1), 2))
            ];
            break;
    }

    /* add alpha */
    if ($alpha) $rgb['a'] = $alpha;
    return $rgb;
}


/**
 * Convert hex color to gray scale.
 *
 * @param string $hex The hex color.
 * @param bool $invert Invert color.
 * @return string Return the gray scale hex color.
 */
function oes_hex_color_to_grayscale(string $hex, bool $invert = false): string {

    /* convert */
    $rgb = oes_hex_to_rgb($hex);

    /* optional invert color */
    if($invert){
        $rgb['r'] = 255 - $rgb['r'];
        $rgb['g'] = 255 - $rgb['g'];
        $rgb['b'] = 255 - $rgb['b'];
    }
    $convert  = dechex((int)(0.299 * $rgb['r']) + (int)(0.587 * $rgb['g']) + (int)(0.114 * $rgb['b']));
    return sprintf("#%02x%02x%02x", $convert, $convert, $convert);
}