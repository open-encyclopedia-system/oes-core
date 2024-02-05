<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add style to be registered.
 *
 * @param string $handle A string containing the name of the style.
 * @param string $src A string containing the full url of the style. If false, style is alias.
 * @param array $deps Optional array containing registered style handles that this style depends on.
 * @param string|null|boolean $ver Optional string containing the style version number.
 * @param string $media Optional string containing the media for which this stylesheet has been defined.
 * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
 * @return void
 */
function oes_add_style(
    string $handle,
    string $src,
    array  $deps = [],
           $ver = null,
    string $media = 'all',
    bool   $admin = false): void
{
    global $oes_assets;
    $oes_assets['styles'][$handle] = [
        'handle' => $handle,
        'src' => plugins_url(OES_BASENAME . $src),
        'deps' => $deps,
        'ver' => is_null($ver) ? oes_get_version() : $ver,
        'media' => $media,
        'admin' => $admin
    ];
}


/**
 * Add style to be registered only for editorial layer.
 *
 * @param string $handle A string containing the name of the style.
 * @param string $src A string containing the full url of the style. If false, style is alias.
 * @param array $deps Optional array containing registered style handles that this style depends on.
 * @param string|null|boolean $ver Optional string containing the style version number.
 * @param string $media Optional string containing the media for which this stylesheet has been defined.
 * @return void
 */
function oes_add_style_admin(
    string $handle,
    string $src,
    array  $deps = [],
           $ver = null,
    string $media = 'all'): void
{
    oes_add_style($handle, $src, $deps, $ver, $media, true);
}


/**
 * Add project style to be registered.
 *
 * @param string $handle A string containing the name of the style.
 * @param string $src A string containing the full url of the style. If false, style is alias.
 * @param array $deps Optional array containing registered style handles that this style depends on.
 * @param string|null|boolean $ver Optional string containing the style version number.
 * @param string $media Optional string containing the media for which this stylesheet has been defined.
 * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
 * @return void
 */
function oes_add_project_style(
    string $handle,
    string $src,
    array  $deps = [],
           $ver = null,
    string $media = 'all',
    bool   $admin = false): void
{
    global $oes_assets;
    $oes_assets['styles'][$handle] = [
        'handle' => $handle,
        'src' => plugins_url(OES_BASENAME_PROJECT . $src),
        'deps' => $deps,
        'ver' => is_null($ver) ? oes_get_version() : $ver,
        'media' => $media,
        'admin' => $admin
    ];
}


/**
 * Add script to be registered.
 *
 * @param string $handle A string containing the name of the script.
 * @param string $src A string containing the full url of the script. If false, script is alias.
 * @param array $depends Optional array containing registered script handles that this script depends on.
 * @param string|boolean $ver Optional string containing the script version number.
 * @param boolean $inFooter Optional boolean indicating whether to enqueue the script before body.
 * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
 * @param array $params Optional parameters to load while localizing script.
 * @return void
 */
function oes_add_script(
    string $handle,
    string $src,
    array  $depends = [],
           $ver = false,
    bool   $inFooter = true,
    bool   $admin = false,
    array  $params = []): void
{
    global $oes_assets;
    $oes_assets['scripts'][$handle] = [
        'handle' => $handle,
        'src' => plugins_url(OES_BASENAME . $src),
        'depends' => $depends,
        'ver' => $ver,
        'in_footer' => $inFooter,
        'admin' => $admin,
        'params' => $params
    ];
}


/**
 * Add script to be registered for editorial layer.
 *
 * @param string $handle A string containing the name of the script.
 * @param string $src A string containing the full url of the script. If false, script is alias.
 * @param array $depends Optional array containing registered script handles that this script depends on.
 * @param string|boolean $ver Optional string containing the script version number.
 * @param boolean $inFooter Optional boolean indicating whether to enqueue the script before body.
 * @param array $params Optional parameters to load while localizing script.
 * @return void
 */
function oes_add_script_admin(
    string $handle,
    string $src,
    array  $depends = [],
           $ver = false,
    bool   $inFooter = true,
    array  $params = []): void
{
    oes_add_script($handle, $src, $depends, $ver, $inFooter, true, $params);
}


/**
 * Add project script to be registered.
 *
 * @param string $handle A string containing the name of the script.
 * @param string $src A string containing the full url of the script. If false, script is alias.
 * @param array $depends Optional array containing registered script handles that this script depends on.
 * @param string|boolean $ver Optional string containing the script version number.
 * @param boolean $in_footer Optional boolean indicating whether to enqueue the script before body.
 * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
 * @return void
 */
function oes_add_project_script(
    string $handle,
    string $src,
    array  $depends = [],
           $ver = false,
    bool   $in_footer = true,
    bool   $admin = false): void
{
    global $oes_assets;
    $oes_assets['scripts'][$handle] = [
        'handle' => $handle,
        'src' => plugins_url(OES_BASENAME_PROJECT . $src),
        'depends' => $depends,
        'ver' => $ver,
        'in_footer' => $in_footer,
        'admin' => $admin
    ];
}


/**
 * Register all scripts and styles.
 * @return void
 */
function oes_register_scripts_and_styles(): void
{
    global $oes_assets;
    foreach (($oes_assets['scripts'] ?? []) as $script)
        if (!$script['admin']) {
            wp_register_script(
                $script['handle'],
                $script['src'],
                $script['depends'],
                $script['ver'],
                $script['in_footer']);
            wp_enqueue_script($script['handle']);
        }

    foreach (($oes_assets['styles'] ?? []) as $style)
        if (!$style['admin']) {
            wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
            wp_enqueue_style($style['handle']);
        }
}


/**
 * Load js scripts.
 * @return void
 */
function oes_load_assets(): void
{
    global $oes_assets;
    foreach (($oes_assets['scripts'] ?? []) as $script) {
        wp_register_script($script['handle'], $script['src'], $script['depends'], $script['ver'], $script['in_footer']);
        wp_enqueue_script($script['handle']);
    }

    foreach (($oes_assets['styles'] ?? []) as $style) {
        wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
        wp_enqueue_style($style['handle']);
    }
}