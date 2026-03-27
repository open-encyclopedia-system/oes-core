<?php

namespace OES\Rights;

use WP_Error;
use WP_User;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Determine whether the current user can manage site options.
 *
 * In WordPress, users with the `manage_options` capability are typically Administrators.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_is_admin(): bool
{
    return current_user_has_cap('manage_options');
}

/**
 * Determine whether the current user has the `oes_admin` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_is_oes_admin(): bool
{
    return current_user_has_cap('oes_admin');
}

/**
 * Determine whether the current user has the `oes_read` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_read(): bool
{
    return current_user_has_cap('oes_read');
}

/**
 * Determine whether the current user has the `oes_read_settings` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_read_settings(): bool
{
    return current_user_has_cap('oes_read_settings');
}

/**
 * Determine whether the current user has the `oes_edit_settings` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_edit_settings(): bool
{
    return current_user_has_cap('oes_edit_settings');
}

/**
 * Determine whether the current user has the `oes_read_config` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_read_config(): bool
{
    return current_user_has_cap('oes_read_config');
}

/**
 * Determine whether the current user has the `oes_edit_config` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_edit_config(): bool
{
    return current_user_has_cap('oes_edit_config');
}

/**
 * Determine whether the current user has the `oes_manage_content` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_manage_content(): bool
{
    return current_user_has_cap('oes_manage_content');
}

/**
 * Determine whether the current user has the `oes_manage_cache` capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function user_can_manage_cache(): bool
{
    return current_user_has_cap('oes_manage_cache');
}

/**
 * Determine whether the current user has a capability.
 *
 * @return bool True if the current user has the capability, false otherwise.
 */
function current_user_has_cap(string $capability): bool
{
    return current_user_can($capability);
}

/**
 * Prevent users with the "Blocked (OES)" role from authenticating.
 *
 * This filter runs during the WordPress authentication process and blocks login
 * attempts for users assigned to the `oes_blocked` role.
 *
 * @param WP_User|WP_Error|null $user The authenticated user or error.
 *
 * @return WP_User|WP_Error|null
 */
function block_blocked_users($user)
{
    if ($user instanceof WP_User && in_array('oes_blocked', (array)$user->roles, true)) {
        return new WP_Error(
            'oes_blocked',
            __('This account has been blocked.', 'oes')
        );
    }

    return $user;
}

/**
 * Register the "Blocked (OES)" user role.
 *
 * Users assigned to this role have no capabilities and are prevented
 * from logging in via the authentication filter.
 *
 * @return void
 */
function add_blocked_role(): void
{
    if (!get_role('oes_blocked')) {
        add_role(
            'oes_blocked',
            'Blocked (OES)'
        );
    }
}

/**
 * Register the "Blocked (OES)" user role.
 *
 * Users assigned to this role have no capabilities and are prevented
 * from logging in via the authentication filter.
 *
 * @return void
 */
function add_oes_roles(): void
{
    $editor = get_role('editor');

    if (!$editor) {
        return;
    }

    $baseCaps = $editor->capabilities;

    $managerCaps = $baseCaps;
    $managerCaps['oes_read']          = true;
    $managerCaps['oes_read_settings'] = true;
    $managerCaps['oes_read_config']   = true;
    $managerCaps['oes_manage_cache']  = true;

    if (!get_role('oes_manager')) {
        add_role('oes_manager', __('OES Managing Editor', 'oes'), $managerCaps);
    }

    $adminCaps = $managerCaps;
    $adminCaps['oes_edit_settings']   = true;
    $adminCaps['oes_edit_config']     = true;
    $adminCaps['oes_manage_content']  = true;
    $adminCaps['oes_admin']           = true;

    if (!get_role('oes_admin')) {
        add_role('oes_admin', __('OES Admin', 'oes'), $adminCaps);
    }
}

function add_capabilities(): void
{
    $editor = get_role('editor');

    if($editor){
        $editor->add_cap('oes_read');
    }

    $admin = get_role('administrator');

    if($admin) {
        $admin->add_cap('oes_read');
        $admin->add_cap('oes_read_settings');
        $admin->add_cap('oes_edit_settings');
        $admin->add_cap('oes_read_config');
        $admin->add_cap('oes_edit_config');
        $admin->add_cap('oes_manage_content');
        $admin->add_cap('oes_manage_cache');
        $admin->add_cap('oes_admin');
    }
}