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

        $managerClass = oes_get_application_class_name('\OES\Caching\Manager');
        $storageClass = oes_get_application_class_name('\OES\Caching\Storage');
        $manager = new $managerClass(
            new $storageClass()
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

    $redirectURL = admin_url('admin.php?page=oes_tools_cache&cache_deleted=' . $updated);
    wp_redirect($redirectURL);
    exit;
}

function oes_cache_regenerate(): void {

    $updated = 0;
    $nonce   = $_GET['_wpnonce'] ?? '';

    if (!wp_verify_nonce($nonce, 'oes_cache_regenerate')) {
        wp_die(__('Invalid nonce.'));
    }

    if (\OES\Rights\user_can_manage_cache()) {

        $cacheIDs = (array)$_GET['list_ids'];

        foreach ($cacheIDs as $cacheID) {
            oes_cache()->regenerate($cacheID);
            $updated = 1;
        }
    }

    $redirectURL = admin_url('admin.php?page=oes_tools_cache&cache_regenerated=' . $updated);
    wp_redirect($redirectURL);
    exit;
}