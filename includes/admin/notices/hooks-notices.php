<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_notices', 'OES\Admin\display_oes_notices_after_refresh', 12);


/**
 * Display the messages that are stored in the option for oes messages.
 *
 * @return void
 */
function display_oes_notices_after_refresh(): void
{
    /* get notice from transient */
    $notices = get_transient('oes-transient-notice');
    if(!empty($notices)){

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