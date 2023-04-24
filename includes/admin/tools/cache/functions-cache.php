<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Schedule a cache update event.
 * @return void
 */
function oes_schedule_cache_event(): void
{
    /* only prepare schedule event when option is set */
    if (get_option('oes_cache-enabled')) {

        /* clear previous events */
        if(wp_next_scheduled('oes_update_archive_cache')) oes_schedule_clear_cache_event();

        /* prepare event */
        $timeString = get_option('oes_cache-timestamp');
        $interval = get_option('oes_cache-interval');
        wp_schedule_event(
            $timeString ? strtotime($timeString) : time(),
            $interval ?? 'daily',
            'oes_update_archive_cache');
    }
    else {
        oes_schedule_clear_cache_event();
    }
}


/**
 * @oesDevelopment Improve and fix, should also be done on deactivation register_deactivation_hook(__FILE__, 'oes_schedule_events_cleanup');
 * @return void
 */
function oes_schedule_clear_cache_event(): void
{
    wp_clear_scheduled_hook('oes_update_archive_cache');
}


/**
 * Delete all archive cache options.
 *
 * @param string $option The option name part after 'oes_cache_' (a post type, a taxonomy or 'index').
 * @return void
 */
function oes_empty_archive_cache(string $option = ''): void
{

    /* delete all options if no option specified */
    if(empty($option)){

        global $oes;
        foreach ($oes->post_types as $singlePostTypeKey => $ignore)
            delete_option('oes_cache_' . $singlePostTypeKey);

        foreach ($oes->taxonomies as $singleTaxonomyKey => $ignore)
            delete_option('oes_cache_' . $singleTaxonomyKey);

        /* get current cache */
        delete_option('oes_cache_index');
    }
    else {
        delete_option('oes_cache_' . $option);
    }
}


/**
 * Set cache.
 *
 * @param string $object The object. Valid options are 'all', 'index' post type or taxonomy.
 * @param array $additionalObjects Additional objects.
 * @return void
 */
function oes_set_cache(string $object, array $additionalObjects = []): void
{


    /* prepare args */
    $args = [
        'execute-loop' => true,
        'archive-class' => false,
        'language' => 'all'
    ];

    $isPostType = post_type_exists($object);
    if($isPostType) $args['post-type'] = $object;
    else $args['taxonomy'] = $object;


    /**
     * Filters if archive loop uses arguments.
     *
     * @param array $args The arguments.
     */
    if (has_filter('oes/theme_archive_args'))
        $args = apply_filters('oes/theme_archive_args', $args);


    /**
     * Filters if archive loop uses additional arguments.
     *
     * @param array $additionalArgs The additional arguments.
     */
    if (has_filter('oes/theme_archive_additional_args'))
        $additionalObjects = apply_filters('oes/theme_archive_additional_args', $additionalObjects);

    /* execute the loop */
    $archiveClass = $args['archive-class'] ?: $object . '_Archive';
    $oesArchive = class_exists($archiveClass) ?
        new $archiveClass($args) :
        new OES_Archive($args);
    if (!empty($oes_additional_objects)) $oesArchive->set_additional_objects($oes_additional_objects, $additionalObjects);

    $optionValue = [
        'cache_value_raw' => serialize((array)$oesArchive),
        'cache_value_html' => serialize($oesArchive->get_data_as_table()),
        'cache_date' => date('Y-m-d H:i:s', time())
    ];

    /* update cache */
    if(oes_get_cache($object)) oes_update_cache($object, $optionValue);
    else oes_insert_cache($object, $optionValue);
}


/**
 * Insert an operation into the OES operation table.
 *
 * @param string $name The cache name.
 * @param array $args Additional arguments. Valid parameters are:
 *  'cache_value_raw' :   The cache raw value.
 *  'cache_value_html':   The html representation of the raw value.
 *  'cache_status'    :   The cache status.
 *  'cache_comment'   :   The cache comment.
 *  'cache_sequence'  :   The cache sequence.
 *  'cache_date'      :   The cache date.
 *  'cache_temp'      :   Temporary argument.
 *
 *
 * @return int|false The number of rows inserted, or false on error.
 */
function oes_insert_cache(string $name, array $args = [])
{
    /* prepare arguments */
    $argsForInsert = [
        'cache_name' => $name,
        'cache_value_raw' => $args['cache_value_raw'] ?? '',
        'cache_value_html' => $args['cache_value_html'] ?? '',
        'cache_sequence' => $args['cache_sequence'] ?? '',
        'cache_status' => $args['cache_status'] ?? '',
        'cache_comment' => $args['cache_comment'] ?? '',
        'cache_date' => $args['cache_date'] ?? current_time('mysql'),
        'cache_temp' => $args['cache_temp'] ?? ''
    ];

    global $wpdb;
    return $wpdb->insert($wpdb->prefix . 'oes_cache', $argsForInsert);
}


/**
 * Update a cache option in the OES cache table.
 *
 * @param mixed $name The cache name.
 * @param array $values The values to be updated (in column => value pairs).
 *
 * @return int|false The number of rows updated, or false on error or empty values.
 */
function oes_update_cache($name, array $values)
{
    if (!empty($values)) {
        global $wpdb;
        return $wpdb->update($wpdb->prefix . 'oes_cache', $values, ['cache_name' => $name]);
    } else return false;
}


/**
 * Delete a cache from the OES cache table.
 *
 * @param mixed $name The cache name.
 *
 * @return int|false The number of rows updated, or false on error.
 */
function oes_delete_cache($name)
{
    global $wpdb;
    return $wpdb->delete($wpdb->prefix . 'oes_cache', ['cache_name' => $name]);
}


/**
 * Get a cache from the OES cache table.
 *
 * @param mixed $name The cache name.
 *
 * @return array|false|object|stdClass The cache rows, or false on error.
 */
function oes_get_cache($name)
{
    global $wpdb;

    $oesTableName = $wpdb->prefix . 'oes_cache';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $oesTableName WHERE cache_name = %s LIMIT 1", $name));

    if (!$row) return false;
    return $row;
}