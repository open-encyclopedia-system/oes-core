<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Add favicon to WordPress admin pages. This overwrites the WordPress favicon settings.
 * @return void
 */
function set_page_icon(): void
{
    echo '<link rel="icon" type="image/x-icon" href="' .
        plugins_url(OES_BASENAME . '/assets/images/favicon.ico') .
        '" />';
}


/**
 * Add classes for OES settings and tools pages.
 *
 * @param string $classes The current classes.
 * @return string The modified classes.
 */
function set_oes_body_class(string $classes = ''): string {
    if(isset($_GET['page']) && oes_starts_with($_GET['page'], 'oes_')) $classes .= ' oes-page';
    return $classes;
}


/**
 * Get message to display if user is not an admin.
 *
 * @return string Return display message.
 */
function get_admin_user_only_message(): string {
    if (!\OES\Rights\user_is_oes_admin())
        return '<div class="notice notice-info">' .
            __('Sorry, you are not allowed to use this tool. You must be an admin to access this tool.', 'oes') .
            '</div>';
    return '';
}