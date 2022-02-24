<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit;


/**
 * Store an OES notice to transient to display it after page refresh.
 *
 * @param string $notice The OES notice.
 * @param string $type The OES notice type. Valid values are 'info', 'warning', 'error', 'success'. Default is 'info'.
 * @param boolean $dismissible If the notice is dismissible. Default is true.
 * @param int $expiration The expiration time for the transient in seconds. Default is 10.
 *
 * @return boolean Return true or false of set_transient.
 */
function add_oes_notice_after_refresh(string $notice, string $type = 'info', bool $dismissible = true, int $expiration = 10): bool
{
    /* get notice from transient */
    $notices = get_transient('oes-transient-notice');

    /* check if not empty*/
    $noticesArray = empty($notices) ? [] : json_decode($notices, true);

    /* add notice */
    $noticesArray[] = [
        'notice' => $notice,
        'type' => (in_array($type, ['info', 'warning', 'error', 'success']) ? $type : 'info'),
        'dismissible' => ($dismissible ? 'is-dismissible' : '')
    ];

    /* update option */
    return set_transient('oes-transient-notice', json_encode($noticesArray, JSON_UNESCAPED_UNICODE), $expiration);
}


/* Display messages */
add_action('admin_notices', 'OES\Admin\display_oes_notices_after_refresh', 12);

/**
 * Display the messages that are stored in the option for oes messages.
 *
 * @return void
 */
function display_oes_notices_after_refresh()
{
    /* get notice from transient */
    $notices = get_transient('oes-transient-notice');
    if($notices && !empty($notices)){

        /* check if not empty*/
        $noticesArray = json_decode($notices, true);

        /* display all notices */
        foreach ($noticesArray as $notice) {
            printf('<div class="notice notice-%s %s"><p>%s</p></div>',
                $notice['type'] ?? 'info',
                $notice['dismissible'] ?? 'is-dismissible',
                $notice['notice'] ?? __('[Notice text is missing.]', 'oes')
            );
        }

        /* clear transient */
        delete_transient('oes-transient-notice');
    }
}