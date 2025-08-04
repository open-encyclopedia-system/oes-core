<?php

/**
 * @file
 * @todoReview Review for 2.4.x
 * @oesDevelopment in 2.4.0:
 *  - use oes database table instead of transient?
 *  - make expiration date part of config or settings?
 *  - hook automatic regeneration by
 *      add_action('save_post', __NAMESPACE__ . '\maybe_schedule_regeneration');
 *      add_action('deleted_post', __NAMESPACE__ . '\maybe_schedule_regeneration');
 *      add_action('trashed_post', __NAMESPACE__ . '\maybe_schedule_regeneration');
 *      add_action('init', __NAMESPACE__ . '\maybe_schedule_regeneration_archive');
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
    $count = (int) get_transient("{$key}_count");
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
 * @param int $chunkSize  Maximum size per transient part (in bytes).
 *
 * @return bool True on success, false on failure.
 */
function set_cache(string $key, $data, bool $expires = false, int $chunkSize = OES_MAX_TRANSIENT_SIZE): bool
{
    if($expires) {
        $now = current_time('timestamp');
        $tomorrow_2am = strtotime('tomorrow 2:00am', $now);
        $expiration = $tomorrow_2am - $now;
    }
    else {
        $expiration = 0;
    }

    clear_cache_parts($key);

    $serialized = serialize($data);
    $chunks = safe_chunk_split($serialized, $chunkSize);

    foreach ($chunks as $i => $chunk) {
        $partKey = "{$key}_part_{$i}";
        if (!set_transient($partKey, $chunk, $expiration)) {
            return false; // Early exit on failure
        }
    }

    // Store the number of parts for later retrieval
    return set_transient("{$key}_count", count($chunks), $expiration);
}

/**
 * Splits a UTF-8 string safely at byte boundaries without breaking characters.
 *
 * @param string $string     The full string to split.
 * @param int    $chunk_size Maximum chunk size in bytes.
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
 * Clear archive cache for a specific post type and all supported languages.
 *
 * This should be hooked to post save/delete actions to invalidate
 * the archive cache when a relevant post is changed.
 *
 * @param int $post_id The ID of the post being saved, deleted, or trashed.
 *
 * @return void
 */
function clear_archive_cache($post_id): void
{
    global $oes;

    $post = get_post($post_id);
    if (!$post) return;

    if (!isset($oes->post_types[$post->post_type])) {
        return;
    }

    $class = $post->post_type . '_Post_Archive';
    if (!class_exists($class)) {
        $class = 'OES_Post_Archive';
    }

    foreach (array_keys($oes->languages) as $language) {
        $key = 'oes_cache-' . $post->post_type . '-' . sanitize_key($class) . '-' . sanitize_key($language);
        clear_cache_parts($key);
    }

    // Clear index
    foreach($oes->theme_index_pages as $indexKey => $indexData){
        if(in_array($post->post_type, $indexData['objects'] ?? [])){

            $archiveClass = $indexKey . '_Index_Archive';
            if (!class_exists($archiveClass)) $archiveClass = 'OES_Index_Archive';

            foreach (array_keys($oes->languages) as $language) {
                $key = 'oes_cache-' . $indexKey . '-' . sanitize_key($archiveClass) . '-' . sanitize_key($language);
                clear_cache_parts($key);
            }
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

    foreach (array_keys($oes->languages) as $language) {
        $key = 'oes_cache-' . $postType . '-' . sanitize_key($class) . '-' . sanitize_key($language);

        clear_cache_parts($key);

        $args = [
            'post-type' => $postType,
            'language' => $language
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
function regenerate_transient(string $key): bool {
    $parts = parse_cache_key($key);

    if (!$parts) {
        return false;
    }

    try {
        regenerate_archive_cache($parts['post_type'], [
            'class'    => $parts['class'] ?? false,
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
        'class'     => $parts[1],
        'language'  => $parts[2],
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
 * @param int $post_id The post being saved/deleted.
 */
function maybe_schedule_regeneration($post_id): void
{
    global $oes;

    $post = get_post($post_id);
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

