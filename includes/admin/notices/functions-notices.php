<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Store an OES notice to transient to display it after page refresh.
 *
 * @param string $notice The OES notice.
 * @param string $type The OES notice type. Valid values are 'info', 'warning', 'error', 'success'. Default is 'info'.
 * @param boolean $dismissible If the notice is dismissible. Default is true.
 * @param string $name The transit name.
 * @param int $expiration The expiration time for the transient in seconds. Default is 10.
 *
 * @return boolean Return true or false of set_transient.
 */
function add_oes_notice_after_refresh(string $notice, string $type = 'info', bool $dismissible = true, string $name = 'oes-transient-notice', int $expiration = 10 * MINUTE_IN_SECONDS): bool
{
    /* get notice from transient */
    $notices = get_transient($name);

    /* check if not empty*/
    $noticesArray = empty($notices) ? [] : json_decode($notices, true);

    /* add notice */
    $noticesArray[] = [
        'notice' => $notice,
        'type' => (in_array($type, ['info', 'warning', 'error', 'success']) ? $type : 'info'),
        'dismissible' => ($dismissible ? 'is-dismissible' : '')
    ];

    /* update option */
    return set_transient($name, json_encode($noticesArray, JSON_UNESCAPED_UNICODE), $expiration);
}


/**
 * Get a html representation of an OES notice.
 *
 * @param array $notice The OES notice. Valid parameters are_
 *  'type'          : The OES notice type. Valid values are 'info', 'warning', 'error', 'success'. Default is 'info'.
 *  'dismissible'   : If the notice is dismissible. Default is true.
 *
 * @return string Return the admin notice.
 */
function get_admin_note_html(array $notice): string{

    $notice = array_merge([
        'type' => 'info',
        'dismissible' => true
    ], $notice);

    /* validate type */
    $type = (in_array($notice['type'], ['info', 'warning', 'error', 'success']) ? $notice['type'] : 'info');

    /* return admin notice */
    return sprintf('<div class="notice notice-%s %s"><p>%s</p></div>',
        $type,
        $notice['dismissible'] ? 'is-dismissible' : '',
        $notice['notice'] ?? __('[Notice text is missing.]', 'oes')
    );
}


/**
 * Display an OES notice.
 *
 * @param array $notice The OES notice. Valid parameters are_
 *  'type'          : The OES notice type. Valid values are 'info', 'warning', 'error', 'success'. Default is 'info'.
 *  'dismissible'   : If the notice is dismissible. Default is true.
 * @return void
 */
function display_admin_note(array $notice): void
{
    echo get_admin_note_html($notice);
}