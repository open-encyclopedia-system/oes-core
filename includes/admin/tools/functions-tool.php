<?php

/**
 * @file
 * @todoReview Review for 2.4.x
 * @oesDevelopment Only register tools if necessary
 */

namespace OES\Admin\Tools;

use function OES\Rights\user_is_oes_admin;

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
                 'admin_features',
                 'schema',
                 'schema_oes',
                 'schema_oes_single',
                 'schema_oes_archive',
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
                 'project'
             ] as $setting) {
        oes_include($path . 'config/class-config-' . $setting . '.php');
    }

    oes_include($path . 'class-tool-import.php');
    oes_include($path . 'class-tool-operations.php');
    oes_include($path . 'class-tool-export.php');

    if (function_exists('\OES\Rights\user_is_oes_admin') && user_is_oes_admin()) {
        oes_include($path . 'config/class-config-admin.php');
        oes_include($path . 'class-tool-model.php');
        oes_include($path . 'class-tool-factory.php');
        oes_include($path . 'class-tool-batch.php');
    }

    add_action('admin_notices', '\OES\Admin\Tools\admin_notices');
}

/**
 * Register a tool.
 *
 * @param string $class The tool class name.
 * @param string $name The tool name.
 * @return mixed Returns the registered instance of the Tool class.
 */
function register_tool(string $class, string $name = '', array $args = [])
{
    $instance = empty($args) ? new $class($name) : new $class($name, $args);
    return OES()->admin_tools[$instance->name] = $instance;
}

/**
 * Display a tool.
 *
 * @param string $name The tool name.
 * @return void
 */
function display(string $name): void
{
    $oes = OES();
    if (isset($oes->admin_tools[$name])) $oes->admin_tools[$name]->display();
}

/**
 * Display a tool.
 *
 * @oesLegacy Renaming for 2.3
 * @param string $name
 * @return void
 */
function display_tool(string $name): void
{
    display($name);
}

/**
 * Get the expand button.
 *
 * @return string The html representation of the expand button.
 */
function get_expand_button(): string
{
    return '<div class="submit" style="float:right;">' .
        '<a href="javascript:void(0);" id="oes-config-expand-all-button" onClick="oesLabel.toggleAll()" ' .
        'class="button button-secondary">' .
        __('Expand All Rows', 'oes') .
        '</a>' .
        '</div>';
}

/**
 * Display html quotes warning on settings and label page.
 * @return void
 */
function admin_notices(): void
{
    $screen = get_current_screen();

    if(!$screen){
        return;
    }

    if (in_array($screen->id, ['oes-settings_page_oes_settings_schema', 'oes-settings_page_oes_settings_labels']) ){
        \OES\Admin\display_html_quotes_warning();
    }
}