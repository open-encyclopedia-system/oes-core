<?php

namespace OES\Admin\DB;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* prepare table names */
global $wpdb;
$oesTableName = $wpdb->prefix . 'oes';
$oesTableCacheName = $wpdb->prefix . 'oes_cache';
$oesTableOperationName = $wpdb->prefix . 'oes_operation';
$oesTableLabel = $wpdb->prefix . 'oes_label';

/* check if oes table already exists */
if ($wpdb->get_var("SHOW TABLES LIKE '$oesTableName'") != $oesTableName) {

    /* Create new table */
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $oesTableName (
			id bigint(50) NOT NULL AUTO_INCREMENT,
			option_name varchar(255) NOT NULL,
			option_value longtext NOT NULL,
			option_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			option_comment text NOT NULL,
			option_temp varchar(50),
			PRIMARY KEY (id)
			) $charsetCollate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    /* set current database version */
    global $oes;
    add_option('oes_db_version', $oes->db_version);
}

/* check if oes cache table exists */
if ($wpdb->get_var("SHOW TABLES LIKE '$oesTableCacheName'") != $oesTableCacheName) {

    /* Create new table */
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $oesTableCacheName (
			id bigint(50) NOT NULL AUTO_INCREMENT,
			cache_name varchar(255) NOT NULL,
			cache_value_raw longtext NOT NULL,
			cache_value_html longtext NOT NULL,
			cache_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			cache_sequence text NOT NULL,
			cache_status text NOT NULL,
			cache_comment text NOT NULL,
			cache_temp varchar(50),
			PRIMARY KEY (id)
			) $charsetCollate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

if ($wpdb->get_var("SHOW TABLES LIKE '$oesTableOperationName'") != $oesTableOperationName) {

    /* Create new table */
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $oesTableOperationName (
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

if ($wpdb->get_var("SHOW TABLES LIKE '$oesTableLabel'") != $oesTableLabel) {

    /* Create new table */
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $oesTableLabel (
			id bigint(50) NOT NULL AUTO_INCREMENT,
			label_key varchar(255) NOT NULL,
			label_language varchar(50) NOT NULL,
			label_value varchar(50) NOT NULL,
			label_object varchar(32),
			label_name varchar(255) NOT NULL,
			label_description varchar(255) NOT NULL,
			label_component varchar(30) NOT NULL,
			PRIMARY KEY (id)
			) $charsetCollate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
