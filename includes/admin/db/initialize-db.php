<?php

namespace OES\Admin\DB;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* prepare table names */
global $wpdb;
$oesTable = $wpdb->prefix . 'oes_operations';

if ($wpdb->get_var("SHOW TABLES LIKE '$oesTable'") != $oesTable) {

    /* Create new table */
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $oesTable (
			id bigint(50) NOT NULL AUTO_INCREMENT,
			operation varchar(30) NOT NULL,
			operation_object varchar(30) NOT NULL,
			operation_object_id bigint(50) NOT NULL,
			operation_type varchar(32),
			operation_key varchar(255) NOT NULL,
			operation_value longtext NOT NULL,
			operation_status varchar(30) NOT NULL,
			operation_comment text NOT NULL,
			operation_author bigint(20) NOT NULL,
			operation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			operation_temp varchar(50),
			operation_sequence text NOT NULL,
			PRIMARY KEY (id)
			) $charsetCollate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
