<?php


if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* include custom user roles */
add_action('init', 'oes_user_roles');

/**
 * Add custom user roles (including the "Read-only (OES)"-Role.
 * TODO @nextRelease: add rights definition to stored user role to handle rights in hooks.
 */
function oes_user_roles()
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
                $result = add_role($roleKey,
                    $role['display_name'] ?? $roleKey, $role['capabilities'] ?? []);
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


/**
 * Check if user has OES admin rights (the user has 'manage_options' capabilities).
 *
 * @return bool Return if user has OES admin rights.
 */
function oes_user_is_oes_admin(): bool
{
    return user_can(wp_get_current_user(), 'manage_options');
}


/**
 * Check if user has "Read-Only (OES)" role,
 *
 * @return bool Return if user has "Read-Only (OES)" role.
 */
function oes_user_is_read_only(): bool
{
    global $current_user;
    return in_array('oes-readonly', $current_user->roles);
}


function oes_user_rights_add_role_notice()
{
    global $current_user;
    if (in_array('oes-readonly', $current_user->roles))
        printf('<div class="notice notice-error"><p style="color:red">%s</p></div>',
            __('Your user has OES read-only privileges. This means that you can not change or edit data. ' .
                'You can edit fields and texts but you will not be able to submit your changes or import data ' .
                '(eg via the LOD Searchbox).', 'oes'));
}


/**
 * Prevent self administration.
 */
function oes_user_rights_prevent_self_administration()
{
    global $current_user, $wp_admin_bar;
    if (in_array('oes-readonly', $current_user->roles)) {
        $wp_admin_bar->remove_menu('edit-profile', 'user-actions', 'user-info');
    }
}


/**
 * Prevent self administration.
 */
function oes_user_rights_prevent_self_administration_profile()
{
    global $current_user;
    if (in_array('oes-readonly', $current_user->roles)) {
        if ('IS_PROFILE_PAGE' === true) {
            wp_die('You are not allowed to change profile data.');
        }
        remove_menu_page('profile.php');
        remove_submenu_page('users.php', 'profile.php');
    }
}


/**
 * Prevent an url access to restricted pages.
 */
function oes_user_rights_prevent_url_access()
{
    global $current_screen, $current_user;

    /* if user does not have read-only role, exit early */
    if (!in_array('oes-readonly', $current_user->roles)) return;

    /* redirect to dashboard for this pages */
    $doNotAccessPages = ['edit-acf', 'edit-comments', 'upload', 'tools', 'plugins', 'themes', 'options-general',
        'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink',
        'options-privacy', 'post-new.php', 'profile'];
    if (in_array($current_screen->id, $doNotAccessPages)) {
        wp_safe_redirect(admin_url());
        exit;
    }
}


/**
 * Remove the 'Add new' link inside the button for all OES post types.
 *
 * @return boolean
 */
function oes_user_rights_remove_add_new_link(): bool
{

    global $post_new_file, $post_type_object, $current_user, $current_screen, $oes;

    /* exist early if page is not an OES post type page */
    if (!isset($post_type_object) || !isset($oes->post_types[$post_type_object->name])) {
        return false;
    }

    if (in_array('oes-readonly', $current_user->roles)) {
        $post_new_file = 'edit.php?post_type=' . $current_screen->post_type;
    }

    return true;
}


/**
 * Overwrite css to hide actions.
 */
function oes_user_rights_overwrite_visibility_via_css()
{
    /* check if read-only user */
    global $current_user;
    if (!in_array('oes-readonly', $current_user->roles)) return;

    /* hide sidebar link */
    global $submenu, $oes;

    foreach ($oes->post_types as $postType => $postTypeConfiguration) {
        unset($submenu['edit.php?post_type=' . $postType][10]);
    }

    /* overwrite css */
    ?>
    <style type="text/css">/* Overwrite css for OES read-only User */
        #wp-admin-bar-new-content, /* hide '+ New' from header */
        #wp-admin-bar-comments, /* hide comments icon from header */
        #menu-pages ul, /* hide 'Add new page' */
        .row-actions .delete a, /* hide delete link from tags editor */
        #menu-posts, /* hide menu "Posts" */
        #menu-comments, /* hide menu "Comments" */
        #menu-tools, /* hide menu "Tools" */
        #wp-admin-bar-user-info, /* hide user menu link to profile in admin bar*/
        #menu-users {
            display: none !important;
        }

        .wrap .wp-heading-inline + .page-title-action, /* overwrite 'Add ...' button */
        .oes-create-new-version, /* overwrite 'Create New Version' */
        .oes-copy-version, /* overwrite  'Copy Version' */
        .oes-create-language-version, /* overwrite 'Create Language Version' */
        .edit-tag-actions a, /* overwrite 'Delete' link for tags editor */
        #edit-slug-buttons button[type="button"], /* overwrite 'Edit' button in slug editor */
        .editor-post-switch-to-draft, /* switch to draft button in block editor */
        input[type="submit"],
        input[type="file"] {
            pointer-events: none !important;
            color: #a0a5aa !important;
            background: #f7f7f7 !important;
            border-color: #ddd !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }</style><?php
}


/**
 * Remove the access to new post actions and redirect to list view.
 */
function oes_user_rights_remove_access_to_post_new()
{
    global $_REQUEST, $pagenow, $current_user, $oes;
    if (!empty($_REQUEST['post_type'])
        && isset($oes->post_types[$_REQUEST['post_type']])
        && !empty($pagenow)
        && 'post-new.php' == $pagenow
        && in_array('oes-readonly', $current_user->roles)) {
        wp_safe_redirect(admin_url('edit.php?post_type=' . $_REQUEST['post_type']));
    }
}


/**
 * Remove add new posts form menu.
 */
function oes_user_rights_remove_add_new_menu()
{
    global $current_user, $oes;
    if (in_array('oes-readonly', $current_user->roles)) {

        /* remove submenu for pages and posts  */
        remove_submenu_page('edit.php?post_type=page',
            'post-new.php?post_type=page');

        /* remove submenu for each post type */
        foreach ($oes->post_types as $postType => $ignore) {
            remove_submenu_page('edit.php?post_type=' . $postType,
                'post-new.php?post_type=' . $postType);
        }
    }
}


/**
 * Remove row actions like trash and quick edit for list views.
 *
 * @param $actions
 * @param $post
 * @return array
 */
function oes_user_rights_remove_row_actions($actions, $post): array
{
    global $current_user;

    /* Remove Quick Edit and Delete */
    if (in_array('oes-readonly', $current_user->roles))
        return ['edit' => $actions['edit'], 'view' => $actions['view']];

    return $actions;
}


/**
 * Remove trash link for list views.
 *
 * @param $views
 * @return mixed
 */
function oes_user_rights_remove_trash_link($views)
{
    global $current_user;
    if (in_array('oes-readonly', $current_user->roles))
        unset($views['trash']);
    return $views;
}


/**
 * Remove the publish meta box for OES post types.
 */
function oes_user_rights_remove_publish_meta_box()
{
    global $current_user, $current_screen;
    if (in_array('oes-readonly', $current_user->roles) &&
        isset($oes->post_types[$current_screen->id])) {
        remove_meta_box('submitdiv', $current_screen->id, 'side');
    }
}


/**
 * Disable publish button for read-only user in Gutenberg posts.
 */
function oes_user_rights_disable_publish_button_in_rest()
{
    global $current_user, $current_screen;
    if (in_array('oes-readonly', $current_user->roles)):
        ?>
        <script defer>
            var postLocked = false;
            wp.domReady(() => {
                wp.data.subscribe(function () {
                    if (!postLocked) {
                        postLocked = true;
                        wp.data.dispatch('core/editor').lockPostSaving('readOnlyAccess');
                    }
                });
            });
        </script>
        <script>
            (function (wp) {
                wp.data.dispatch('core/notices').createNotice(
                    'error',
                    'Your user has OES read-only privileges. This means that you can not change or edit data. ' +
                    'You can edit fields and texts but you will not be able to submit your changes or import data ' +
                    '(eg via the LOD Searchbox).',
                    {
                        isDismissible: false
                    }
                );
            })(window.wp);
        </script>
    <?php
    endif;
}