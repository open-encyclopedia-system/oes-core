<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('oes/data_model_registered', 'OES\Admin\initialize_container_pages');
add_action('admin_enqueue_scripts', 'OES\Admin\add_page_scripts');


/**
 * Create container pages
 * @return void
 */
function initialize_container_pages(): void
{
    $oes = OES();
    if (isset($oes->admin_pages['container']))
        foreach ($oes->admin_pages['container'] as $key => $page) new Container($key, $page);
}


/**
 * Enqueue script for an admin page.
 * @param string $hook The page hook
 * @return void
 */
function add_page_scripts(string $hook): void
{
    $oes = OES();
    $file = '/includes/admin/pages/js/' . str_replace('-', '_', $hook) . '.js';
    if (file_exists(oes_get_path($file, $oes->path_core_plugin))) {
        wp_register_script('oes-admin_' . $hook, plugins_url($oes->basename . $file), [], false, true);
        wp_enqueue_script('oes-admin_' . $hook);
    }
}