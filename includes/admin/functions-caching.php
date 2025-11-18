<?php

/**
 * @file
 * @todoReview Review for 2.4.x
 * @oesDevelopment in 2.4.0:
 *  - use oes database table instead of transient?
 *  - make into class?
 */

namespace OES\Caching;

const OES_MAX_TRANSIENT_SIZE = 3072; // 3KB limit for WordPress options table

/**
 * Retrieve cached data from a transient.
 *
 * @param string $key The cache key used to store the transient.
 * @return mixed The cached data, or false if the cache does not exist or has expired.
 */
function get_cache(string $key)
{
    $count = (int)get_transient("{$key}_count");
    if (!$count) {
        return null;
    }

    $serialized = '';
    for ($i = 0; $i < $count; $i++) {
        $part = get_transient("{$key}_part_{$i}");
        if ($part === false) {
            return null; // Missing part
        }
        $serialized .= $part;
    }

    return @unserialize($serialized);
}

/**
 * Store data in the cache using a transient.
 *
 * @param string $key The cache key used to store the data.
 * @param mixed $data The data to cache.
 * @param mixed $expires Expiration timespan in seconds.
 * @param int $chunkSize Maximum size per transient part (in bytes).
 *
 * @return bool True on success, false on failure.
 */
function set_cache(string $key, $data, bool $expires = false, int $chunkSize = OES_MAX_TRANSIENT_SIZE): bool
{
    if ($expires) {
        $now = current_time('timestamp');
        $tomorrow_2am = strtotime('tomorrow 2:00am', $now);
        $expiration = $tomorrow_2am - $now;
    } else {
        $expiration = 0;
    }

    clear_cache_parts($key);

    $serialized = serialize($data);
    $chunks = safe_chunk_split($serialized, $chunkSize);

    foreach ($chunks as $i => $chunk) {
        $partKey = "{$key}_part_{$i}";
        if (!set_transient_noautoload($partKey, $chunk, $expiration)) {
            return false; // Early exit on failure
        }
    }

    // Store the number of parts for later retrieval
    return set_transient_noautoload("{$key}_count", count($chunks), $expiration);
}

/**
 * Set a WordPress transient that does NOT autoload.
 *
 * @param string $transient Transient name (without _transient_ prefix).
 * @param mixed $value Value to store.
 * @param int $expiration Optional. Time until expiration in seconds. Default: 0 (no expiration).
 *
 * @return bool True if set successfully, false otherwise.
 */
function set_transient_noautoload(string $transient, $value, $expiration = 0): bool
{
    global $wpdb;

    if (!set_transient($transient, $value, $expiration)) {
        return false;
    }

    if (!wp_using_ext_object_cache()) {
        $option = '_transient_' . $transient;

        $wpdb->update(
            $wpdb->options,
            array('autoload' => 'no'),
            array('option_name' => $option)
        );
    }

    return true;
}

/**
 * Splits a UTF-8 string safely at byte boundaries without breaking characters.
 *
 * @param string $string The full string to split.
 * @param int $chunk_size Maximum chunk size in bytes.
 *
 * @return array An array of UTF-8-safe string chunks.
 */
function safe_chunk_split(string $string, int $chunk_size): array
{
    $chunks = [];
    $offset = 0;
    $length = strlen($string);

    while ($offset < $length) {
        $chunk = mb_strcut($string, $offset, $chunk_size, 'UTF-8');
        $chunks[] = $chunk;
        $offset += strlen($chunk);
    }

    return $chunks;
}

/**
 * Deletes all transient parts associated with a chunked cache entry.
 *
 * This function loops through transients named with the pattern "{$key}_part_{$i}"
 * and deletes each one until no further parts are found, or until a safety limit is reached.
 *
 * @param string $key The base cache key used to store the transient parts.
 *
 * @return void
 */
function clear_cache_parts(string $key): void
{
    $count = (int)get_transient("{$key}_count");

    if ($count) {
        // Delete known number of parts
        for ($i = 0; $i < $count; $i++) {
            delete_transient("{$key}_part_{$i}");
        }
    } else {
        // Fallback: unknown count, avoid infinite loop
        for ($i = 0; $i < 1000; $i++) {
            $partKey = "{$key}_part_{$i}";
            if (!get_transient($partKey)) {
                break;
            }
            delete_transient($partKey);
        }
    }
    delete_transient("{$key}_count");
}

/**
 * Handles cache clearing when a post is saved, trashed, or deleted.
 *
 * @param int $postID The post ID.
 * @return void
 */
function clear_archive_cache_post(int $postID): void
{
    if (wp_is_post_autosave($postID) || wp_is_post_revision($postID)) {
        return;
    }

    $post = get_post($postID);
    if (!$post instanceof \WP_Post) {
        return;
    }

    if (!in_array($post->post_status, ['publish', 'trash'])) {
        return;
    }

    global $oes;
    $postType = $post->post_type;

    if (!isset($oes->post_types[$postType])) {
        return;
    }

    $class = $postType . '_Post_Archive';
    if (!class_exists($class)) {
        $class = 'OES_Post_Archive';
    }

    $language = oes_get_post_language($postID);

    clear_archive_cache($postType, $class, $language);
}

/**
 * Handles cache clearing when a term is deleted.
 *
 * @param int $termID The term ID.
 * @param int $tt_id Term taxonomy ID.
 * @param string $taxonomy The taxonomy name.
 * @param \WP_Term $deletedTerm The term object before deletion.
 * @return void
 */
function clear_archive_cache_term(int $termID, $tt_id, string $taxonomy, $deletedTerm = null): void
{
    if ($deletedTerm instanceof \WP_Term) {
        $term = $deletedTerm;
    } else {
        $term = get_term($termID, $taxonomy);

        if (!$term || is_wp_error($term)) {
            return;
        }
    }

    global $oes;
    if (!isset($oes->taxonomies[$term->taxonomy])) {
        return;
    }

    $class = $term->taxonomy . '_Taxonomy_Archive';
    if (!class_exists($class)) {
        $class = 'OES_Taxonomy_Archive';
    }

    clear_archive_cache($term->taxonomy, $class);
}

/**
 * Clears archive cache for a specific object (post type or taxonomy) in all languages.
 *
 * @param string $objectKey Post type or taxonomy key.
 * @param string $class Archive class name (or fallback).
 * @return void
 */
function clear_archive_cache(string $objectKey, string $class, string $postLanguage = 'all'): void
{
    global $oes;

    // Prepare cache keys for one or all languages
    $languages = $postLanguage === 'all'
        ? array_keys($oes->languages)
        : [$postLanguage];

    $keys = array_map(
        static fn(string $language): string => 'oes_cache-' . $objectKey . '-' . sanitize_key($class) . '-' . sanitize_key($language),
        $languages
    );

    $keys = modify_cache_keys($keys, ['object' => $objectKey, 'class' => $class]);

    array_map('\OES\Caching\clear_cache_parts', $keys);

    // Clear index
    foreach ($oes->theme_index_pages as $indexKey => $indexData) {
        if (in_array($objectKey, $indexData['objects'] ?? [])) {

            $archiveClass = $indexKey . '_Index_Archive';
            if (!class_exists($archiveClass)) $archiveClass = 'OES_Index_Archive';

            $keys = array_map(
                static fn(string $language): string => 'oes_cache-' . $indexKey . '-' . sanitize_key($archiveClass) . '-' . sanitize_key($language),
                array_keys($oes->languages)
            );

            $keys = modify_cache_keys($keys, ['index' => $indexKey, 'class' => $archiveClass]);

            array_map('\OES\Caching\clear_cache_parts', $keys);
        }
    }
}

/**
 * Regenerate archive cache by deleting the old one and storing fresh data.
 *
 * @param string $postType The post type to regenerate archive cache for.
 */
function regenerate_archive_cache(string $postType, array $args = []): void
{
    global $oes;

    $class = $args['class'] ?? $postType . '_Post_Archive';
    if (!class_exists($class)) {
        $class = 'OES_Post_Archive';
    }

    // Prepare initial cache keys
    $keys = [];
    foreach ($oes->languages as $language => $_) {
        $keys[] = 'oes_cache-' . $postType . '-' . sanitize_key($class) . '-' . sanitize_key($language);
    }

    $keys = modify_cache_keys($keys, ['postType' => $postType, 'class' => $class]);

    foreach ($keys as $index => $key) {
        $language = array_keys($oes->languages)[$index] ?? null;
        if (!$language) {
            continue;
        }

        clear_cache_parts($key);

        $args = [
            'post-type' => $postType,
            'language' => $language,
        ];

        oes_set_archive_data($class, $args, false);
    }
}

/**
 * Regenerates a transient cache entry based on its structured cache key.
 *
 * This function attempts to parse the given transient key into its component parts
 * (post type, class, and language), then triggers the regeneration of the archive cache
 * using those parts. If regeneration fails, the error is logged.
 *
 * @param string $key The full transient key, expected to follow the format:
 *                    'oes_cache-{post_type}-{class}-{language}'.
 *
 * @return bool Returns true if regeneration was successful, false otherwise.
 */
function regenerate_transient(string $key): bool
{
    $parts = parse_cache_key($key);

    if (!$parts) {
        return false;
    }

    try {
        regenerate_archive_cache($parts['post_type'], [
            'class' => $parts['class'] ?? false,
            'language' => $parts['language'] ?? false,
        ]);
        return true;
    } catch (\Throwable $e) {
        error_log('Failed to regenerate transient: ' . $e->getMessage());
        return false;
    }
}

/**
 * Parse a cache key generated by OES into its component parts.
 *
 * Expected key format: oes_cache-{post_type}-{class}-{language}
 *
 * @param string $key The full transient cache key.
 *
 * @return array|null Returns an associative array with 'post_type', 'class', and 'language' keys,
 *                    or null if the key does not match the expected format.
 */
function parse_cache_key(string $key)
{
    if (!str_starts_with($key, 'oes_cache-')) {
        return null;
    }
    $suffix = substr($key, strlen('oes_cache-'));

    $parts = explode('-', $suffix, 3);
    if (count($parts) !== 3) {
        return null;
    }

    return [
        'post_type' => $parts[0],
        'class' => $parts[1],
        'language' => $parts[2],
    ];
}

/**
 * Retrieves all OES-related transients and their timeouts.
 *
 * @return array List of rows with option_name and option_value.
 */
function get_transients(): array
{
    global $wpdb;

    $likeClause = $wpdb->esc_like('oes_cache-') . '%';

    return $wpdb->get_results($wpdb->prepare("
                SELECT option_name, option_value 
                FROM {$wpdb->options} 
                WHERE option_name LIKE %s OR option_name LIKE %s
                ORDER BY option_name ASC
            ", '_transient_' . $likeClause, '_transient_timeout_' . $likeClause));
}

/**
 * Register dynamic action hooks for scheduled events.
 */
function maybe_schedule_regeneration_archive(): void
{
    global $oes;

    foreach (array_keys($oes->post_types) as $postType) {
        add_action("oes_regenerate_archive_cache_{$postType}", function () use ($postType) {
            regenerate_archive_cache($postType);
        });
    }
}

/**
 * Schedule archive regeneration for a given post type only once per request.
 *
 * @param int $postID The post being saved/deleted.
 */
function maybe_schedule_regeneration($postID): void
{
    global $oes;

    $post = get_post($postID);
    if (!$post || !isset($oes->post_types[$post->post_type])) {
        return;
    }

    static $scheduled = [];

    if (isset($scheduled[$post->post_type])) {
        return;
    }

    $scheduled[$post->post_type] = true;

    if (!wp_next_scheduled("oes_regenerate_archive_cache_{$post->post_type}")) {
        wp_schedule_single_event(strtotime('tomorrow 2:00am'), "oes_regenerate_archive_cache_{$post->post_type}");
    }
}

/**
 * Applies a filter to modify the provided cache keys before use.
 *
 * @param array $keys Array of cache keys to be filtered.
 * @param array $args Optional. Context arguments for the filter (e.g., object type, class name). Default [].
 * @return array The filtered array of cache keys.
 */
function modify_cache_keys(array $keys, array $args = []): array
{
    if (has_filter('oes/modify_cache_keys')) {
        return apply_filters('oes/modify_cache_keys', $keys, $args);
    }
    return $keys;
}

