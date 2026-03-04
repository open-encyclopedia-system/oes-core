<?php

namespace OES\DB;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create or update the oes_operations table.
 *
 * @return void
 */
function install_operation_table(): void
{
    $version = '2.0';
    $installed = get_option('oes_operations_table_version');

    if ($installed === $version) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'oes_operations';
    $charsetCollate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    if ($installed && version_compare($installed, $version, '<')) {
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        operation varchar(30) NOT NULL,
        operation_object varchar(30) NOT NULL,
        operation_object_id bigint(20) unsigned NOT NULL DEFAULT 0,
        operation_type varchar(32) DEFAULT '',
        operation_key varchar(255) NOT NULL,
        operation_value longtext NOT NULL,
        operation_status varchar(30) NOT NULL DEFAULT 'pending',
        operation_comment text NOT NULL,
        operation_author bigint(20) unsigned NOT NULL DEFAULT 0,
        operation_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        operation_temp varchar(50) DEFAULT '',
        operation_sequence int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        KEY operation_status (operation_status),
        KEY operation_object (operation_object),
        KEY operation_object_id (operation_object_id)
    ) {$charsetCollate};";

    dbDelta($sql);

    update_option('oes_operations_table_version', $version);
}

/**
 * Create or update the oes_cache table.
 *
 * @return void
 */
function install_cache_table(): void
{
    $version = '2.0';
    $installed = get_option('oes_cache_table_version');

    if ($installed === $version) {
        return;
    }

    global $wpdb;

    $table = $wpdb->prefix . 'oes_cache';
    $charsetCollate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    if ($installed && version_compare($installed, $version, '<')) {
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }

    $sql = "CREATE TABLE {$table} (
            cache_key varchar(191) NOT NULL,
            part int(11) NOT NULL DEFAULT 0,
            cache_value longtext NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			object_type varchar(50) NOT NULL,
			archive_class varchar(100) NOT NULL,
			cache_language varchar(10) NOT NULL,
			additional json NOT NULL,
            PRIMARY KEY (cache_key, part)
            ) {$charsetCollate};";

    dbDelta($sql);

    update_option('oes_cache_table_version', $version);
}