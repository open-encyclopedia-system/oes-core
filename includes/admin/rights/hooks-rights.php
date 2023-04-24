<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('init', 'oes_user_roles');

/**
 * Add custom user roles (including the "Read-only (OES)"-Role).
 *
 * @return void
 * @oesDevelopment Add rights definition to stored user role to handle rights in hooks.
 */
function oes_user_roles(): void
{

    //remove_role('oes-readonly');

    /* check if there are roles to be registered */
    $oes = OES();
    if (!empty($oes->user_roles) || is_null(get_role('oes-readonly'))) {

        /* the OES read only user role (give editor access and restrict it through hooks) */
        $oes->user_roles['oes-readonly'] = [
            'display_name' => 'Read-only (OES)',
            'capabilities' => [
                'edit_posts' => true,
                'edit_others_posts' => true,
                'edit_published_posts' => true,
                'edit_pages' => true,
                'read' => true,
                'edit_others_pages' => true,
                'edit_published_pages' => true,
                'publish_pages' => true
            ]
        ];

        /* loop through roles and add if necessary */
        $userRoles = $oes->user_roles;
        foreach ($userRoles as $roleKey => $role)
            if (is_null(get_role($roleKey)))
                add_role($roleKey, $role['display_name'] ?? $roleKey, $role['capabilities'] ?? []);
    }


    /* register hooks for user rights */
    if (oes_user_is_read_only()) {
        add_action('admin_notices', 'oes_user_rights_add_role_notice');
        add_action('admin_init', 'oes_user_rights_prevent_self_administration_profile');
        add_action('wp_before_admin_bar_render', 'oes_user_rights_prevent_self_administration');
        add_action('admin_head', 'oes_user_rights_prevent_url_access');
        add_action('admin_head', 'oes_user_rights_remove_add_new_link');
        add_action('admin_menu', 'oes_user_rights_overwrite_visibility_via_css');
        add_action('admin_menu', 'oes_user_rights_remove_access_to_post_new');
        add_action('admin_menu', 'oes_user_rights_remove_add_new_menu');
        add_filter('post_row_actions', 'oes_user_rights_remove_row_actions', 10, 2);
        add_action('do_meta_boxes', 'oes_user_rights_remove_publish_meta_box');
        add_action('admin_footer', 'oes_user_rights_disable_publish_button_in_rest');

        foreach ($oes->post_types as $postType => $postTypeConfiguration)
            add_filter('views_edit-' . $postType, 'oes_user_rights_remove_trash_link');
    }
}
