<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Include tools.
 *
 * @return void
 */
function include_tools(): void
{
    $path = 'admin/tools/';
    oes_include($path . 'config/functions-config.php');
    foreach ([
                 'admin_columns',
                 'admin_container',
                 'admin_menu',
                 'schema',
                 'schema_display',
                 'schema_mapping',
                 'schema_oes',
                 'theme_colors',
                 'theme_date',
                 'theme_languages',
                 'theme_logos',
                 'theme_index_pages',
                 'theme_labels_general',
                 'theme_labels_media',
                 'theme_labels_objects',
                 'theme_media',
                 'theme_search',
                 'application'
             ] as $setting) {
        oes_include($path . 'config/class-config-' . $setting . '.php');
    }

    oes_include($path . 'class-tool-import.php');
    oes_include($path . 'class-tool-export.php');

    if (\OES\Rights\user_is_oes_admin()) {
        oes_include($path . 'class-tool-model.php');
        oes_include($path . 'class-tool-factory.php');
        oes_include($path . 'class-tool-batch.php');
    }
}

/**
 * Register a tool.
 *
 * @param string $class The tool class name.
 * @param string $name The tool name.
 */
function register_tool(string $class, string $name = '', array $args = []): void
{
    _deprecated_function(__FUNCTION__, '3.1.0', 'oes_add_tool');
    oes_add_tool($class, $name, $args);
}

/**
 * Display a tool.
 *
 * @param string $name The tool name.
 * @return void
 */
function display(string $name): void
{
    $tool = oes_get_tool($name);
    if ($tool) {
        $tool->display();
    }
}

/**
 * @deprecated 2.3.0 Use display instead
 *
 * Display a tool.
 * @param string $name
 * @return void
 */
function display_tool(string $name): void
{
    _deprecated_function(__FUNCTION__, '2.3.0', 'display()');
    display($name);
}

/**
 * Get the expand button.
 *
 * @return string The HTML representation of the expand button.
 */
function get_expand_button(): string
{
    return '<div class="submit" style="float:right;">' .
        '<a href="#" id="oes-config-expand-all-button" class="button button-secondary">' .
        __('Expand All Rows', 'oes') .
        '</a>' .
        '</div>';
}
