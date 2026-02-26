<?php

use OES\Caching\Manager;
use OES\Caching\Storage;

/**
 * Get the OES cache manager singleton.
 *
 * @return Manager The singleton cache manager instance.
 */
function oes_cache(): Manager
{
    static $manager = null;

    if (!$manager) {
        $manager = new Manager(
            new Storage()
        );
    }

    return $manager;
}


function oes_cache_delete(): void
{
    $updated = 0;
    $nonce   = $_GET['_wpnonce'] ?? '';

    if (!wp_verify_nonce($nonce, 'oes_cache_delete')) {
        wp_die(__('Invalid nonce.'));
    }

    if (\OES\Rights\user_can_manage_cache()) {

        $cacheIDs = (array)$_GET['list_ids'];

        foreach ($cacheIDs as $cacheID) {
            oes_cache()->delete($cacheID);
            $updated = 1;
        }
    }

    $redirectURL = admin_url('admin.php?page=oes_tools_cache&cache_updated=' . $updated);
    wp_redirect($redirectURL);
    exit;
}
