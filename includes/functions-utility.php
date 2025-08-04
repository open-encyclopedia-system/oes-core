<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Includes a file relative to the plugin path.
 *
 * @param string $file     Relative file path from the root or includes directory.
 * @param string $root     Optional. Absolute base path. Defaults to OES Core Plugin path.
 * @param bool   $includes Optional. Whether to prepend the 'includes/' directory. Default true.
 * @return void
 */
function oes_include(string $file, string $root = '', bool $includes = true): void
{
    if (empty($root)) {
        $root = OES_CORE_PLUGIN;
    }

    $path = oes_get_path(($includes ? '/includes/' : '/') . $file, $root);

    if (file_exists($path)) {
        include_once($path);
    } else {
        error_log("oes_include: File not found - $path");
    }
}


/**
 * Includes a file relative to the OES Project Plugin path.
 *
 * This is a wrapper for oes_include() using the project plugin as the base path.
 *
 * @param string $file     Relative file path from the root or includes directory.
 * @param string $root     Optional. Absolute base path. Defaults to OES Project Plugin path.
 * @param bool   $includes Optional. Whether to prepend the 'includes/' directory. Default true.
 * @return void
 */
function oes_include_project(string $file, string $root = '', bool $includes = true): void
{
    if (empty($root)) {
        $root = OES_PROJECT_PLUGIN;
    }

    if (function_exists('oes_include')) {
        oes_include($file, $root, $includes);
    } else {
        error_log('oes_include_project: oes_include function does not exist.');
    }
}


/**
 * Returns the absolute path to a file within the plugin.
 *
 * Concatenates a root directory with a relative path, ensuring proper formatting.
 *
 * @param string $path Relative file path from the plugin root.
 * @param string $root Optional. Absolute root path. Defaults to OES_CORE_PLUGIN.
 * @return string Normalized absolute file path.
 */
function oes_get_path(string $path = '', string $root = ''): string
{
    $base = rtrim(empty($root) ? OES_CORE_PLUGIN : $root, '/\\');
    $path = ltrim($path, '/\\');
    return $base . '/' . $path;
}



/**
 * Get the OES plugin version.
 *
 * @return string|null Returns the OES plugin version or null if not available.
 */
function oes_get_version(): ?string
{
    return OES()->get_version();
}


/**
 * Includes a view file from the admin views directory.
 *
 * @param string $path Relative path to the view file from '/includes/admin/views/'. '.php' extension is optional.
 * @param array $args Optional. Variables to extract into the view scope. Keys won't override internal variables.
 * @param string $root Optional. Absolute path to plugin root. Defaults to OES_CORE_PLUGIN.
 * @return void
 */
function oes_get_view(string $path = '', array $args = [], string $root = ''): void
{
    if (empty($root)) {
        $root = OES_CORE_PLUGIN;
    }

    // Ensure path ends with .php and build full path
    $path = oes_get_path('/includes/admin/views/' . (str_ends_with($path, '.php') ? $path : "$path.php"), $root);

    if (file_exists($path)) {
        extract($args, EXTR_SKIP);
        include($path);
    }
}


/**
 * Includes a view file from the admin views directory of the project plugin.
 *
 * This is a wrapper around oes_get_view() using the project plugin root.
 *
 * @param string $path Relative path to the view file from '/includes/admin/views/'. '.php' extension is optional.
 * @param array  $args Optional. Variables to extract into the view scope.
 * @param string $root Optional. Absolute plugin root. Defaults to OES_PROJECT_PLUGIN.
 * @return void
 */
function oes_get_project_view(string $path = '', array $args = [], string $root = ''): void
{
    if (empty($root)) {
        $root = OES_PROJECT_PLUGIN;
    }

    if (function_exists('oes_get_view')) {
        oes_get_view($path, $args, $root);
    } else {
        error_log('oes_get_project_view: oes_get_view function does not exist.');
    }
}


/**
 * Get data from a JSON file.
 *
 * @param string $path The file path.
 * @return array Returns the decoded JSON data, or an empty array if there's an issue.
 */
function oes_get_json_data_from_file(string $path): array
{
    if (empty($path)) {
        oes_write_log(__('Empty file path provided.', 'oes'));
        return [];
    }

    if (!file_exists($path)) {
        oes_write_log(sprintf(__('File "%s" not found.', 'oes'), $path));
        return [];
    }

    $fileAsString = file_get_contents($path);

    if (empty($fileAsString)) {
        oes_write_log(sprintf(__('File "%s" has no content.', 'oes'), $path));
        return [];
    }

    $data = json_decode($fileAsString, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        oes_write_log(sprintf(__('Invalid JSON in file "%s": %s', 'oes'), $path, json_last_error_msg()));
        return [];
    }

    return $data;
}



/**
 * Export data as a JSON file.
 *
 * @param string $name The file name (should end with .json).
 * @param array $data The data as an array.
 * @return bool Returns false if JSON encoding fails.
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
 * Hide WordPress update notifications.
 */
function oes_hide_update_notification(): void
{
    add_filter('pre_site_transient_update_core', 'oes_hide_update_notification_filter');
    add_filter('pre_site_transient_update_plugins', 'oes_hide_update_notification_filter');
    add_filter('pre_site_transient_update_themes', 'oes_hide_update_notification_filter');
}


/**
 * Hide WordPress update notification for the screen.
 *
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
function oes_hide_obsolete_menu_structure(): void
{
    add_action('admin_menu', 'oes_hide_obsolete_menu_structure_filter');
}


/**
 * Hide obsolete WordPress menu structure.
 */
function oes_hide_obsolete_menu_structure_filter(): void
{
    remove_menu_page('jetpack'); // Jetpack7
    remove_menu_page('edit.php'); // Posts7
    remove_menu_page('edit-comments.php'); // Comments7
}


/**
 * Add a field group to the page object containing the language field.
 *
 * @param array $fieldTypes The field types to be added.
 * @return void
 */
function oes_add_fields_to_page(array $fieldTypes = []): void
{
    \OES\Model\add_fields_to_page($fieldTypes);
}


/**
 * Get OES custom icon path for the menu icon.
 *
 * @param string $identifier The icon identifier. Valid options: 'default', 'second', 'parent', 'admin'.
 * @return string Path to the custom icon URL, or 'dashicons-clipboard' if not found.
 */
function oes_get_menu_icon_path(string $identifier = 'default'): string
{
    $icons = [
        'default' => '/assets/images/oes_cubic_18x18.png',
        'second'  => '/assets/images/oes_cubic_18x18_second.png',
        'parent'  => '/assets/images/oes_cubic_18x18_parent.png',
        'admin'   => '/assets/images/oes_cubic_18x18_admin.png',
    ];

    $customIconPath = $icons[$identifier] ?? $icons['default'];
    $fullPath = OES_CORE_PLUGIN . $customIconPath;

    $menuIcon = false;
    if (file_exists($fullPath)) {
        if (getimagesize($fullPath)) {
            $menuIcon = plugins_url(OES_BASENAME . $customIconPath);
        }
    }

    return $menuIcon ?: 'dashicons-clipboard';
}


/**
 * Recursively merge two arrays, overwriting values instead of nesting them.
 *
 * @param mixed $array1 The base array or value.
 * @param mixed $array2 The array or value to merge into the base.
 * @return mixed The merged result.
 */
function oes_merge_array_recursively(mixed $array1, mixed $array2): mixed
{
    if (!is_array($array1) || !is_array($array2)) {
        return $array2;
    }

    foreach ($array2 as $key => $value) {
        if (array_key_exists($key, $array1)) {
            $array1[$key] = oes_merge_array_recursively($array1[$key], $value);
        } else {
            $array1[$key] = $value;
        }
    }

    return $array1;
}


/**
 * Recursively combine two arrays without overwriting existing values in the first array.
 *
 * @param mixed $array1 The base array or value to preserve.
 * @param mixed $array2 The array or value to fill missing entries from.
 * @param int|string $depth Maximum recursion depth, or 'all' for unlimited.
 * @return mixed The combined result.
 */
function oes_combine_array_recursively(mixed $array1, mixed $array2, int|string $depth = 'all'): mixed
{
    if (!is_array($array1) || !is_array($array2)) {
        return $array1;
    }

    if ($depth !== 'all') {
        if (!is_int($depth) || $depth < 1) {
            return $array1;
        }
        $depth--;
    }

    foreach ($array2 as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $array1[$key] = $value;
        } elseif (is_array($array1[$key]) && is_array($value) && ($depth === 'all' || $depth > 0)) {
            $array1[$key] = oes_combine_array_recursively($array1[$key], $value, $depth);
        }
        // else: value exists, do not overwrite
    }

    return $array1;
}


/**
 * Check if an option exists in the database. get_option will return false negatives for null, 0, '' and miss 'false'.
 *
 * @param string $option The option name.
 * @param bool $siteWide Optional. Search site-wide. Default is false.
 * @return bool|int|mysqli_result|resource|null Returns the option.
 */
function oes_option_exists(string $option, bool $siteWide = false)
{
    global $wpdb;
    return $wpdb->query($wpdb->prepare("SELECT * FROM " . ($siteWide ? $wpdb->base_prefix : $wpdb->prefix) .
        "options WHERE option_name ='%s' LIMIT 1", $option));
}


/**
 * Log messages for WordPress debugging.
 *
 * Logs messages or data to the PHP error log when WP_DEBUG is enabled.
 * Can be adapted to use other logging or display mechanisms during development.
 *
 * @param mixed $messages The log messages, strings, arrays, or objects.
 * @return bool Always returns false to avoid interrupting execution flow.
 */
function oes_write_log(mixed $messages): bool {
    if (defined('WP_DEBUG') && WP_DEBUG === true) {
        $logMessage = is_array($messages) || is_object($messages)
            ? print_r($messages, true)
            : var_export($messages, true);

        error_log('[' . date('Y-m-d H:i:s') . '] ' . $logMessage);
    }
    return false;
}


/**
 * Safely get a value from $_POST with optional sanitization.
 *
 * @param string $key The key to retrieve.
 * @param string $type Expected data type: 'int', 'text', 'bool', etc.
 * @param mixed|null $default Fallback value if not set or invalid.
 * @return mixed
 */
function oes_get_global_post_value(string $key, string $type = 'text', mixed $default = null): mixed
{
    if (!isset($_POST[$key])) {
        return $default;
    }

    $value = $_POST[$key];

    return match ($type) {
        'int' => intval($value),
        'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
        default => sanitize_text_field($value),
    };
}


/**
 * Resolves a dynamically constructed class name based on project context.
 *
 * @param string $defaultClass   The fully qualified default class name (e.g., 'OES\Map\Map').
 * @param string $cleanedClass   Optional cleaned version of the class name to avoid duplication.
 * @return string                The resolved class name if it exists, otherwise the default class.
 */
function oes_get_project_class_name(string $defaultClass, string $cleanedClass = ''): string
{
    $base = str_replace(['oes-', '-'], ['', '_'], OES_BASENAME_PROJECT);
    $consideredClass = (empty($cleanedClass) ? $defaultClass : $cleanedClass);
    $cleanSuffix =  str_replace(['OES\\', 'OES', '\\'], ['', '', '_'], $consideredClass);
    $className = $base . $cleanSuffix;
    return class_exists($className) ? $className : $defaultClass;
}
