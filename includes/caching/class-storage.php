<?php

namespace OES\Caching;

/**
 * Class Storage
 *
 * Implements the Storage_Interface to provide database-backed cache storage
 * for the OES plugin. Supports chunked storage of large payloads and stores
 * metadata for objects, classes, languages, and additional info.
 *
 * This class handles:
 * - Storing cache entries in a custom database table
 * - Retrieving chunked cache entries
 * - Deleting cache by key or by prefix
 *
 * Chunking:
 *  - For large payloads, cache_value is split into parts.
 *  - Maximum chunk size is defined by OES_MAX_CHUNK_SIZE.
 */
class Storage implements Storage_Interface
{

    /**
     * Name of the database table used for cache storage.
     *
     * @var string
     */
    protected string $table;

    /**
     * Maximum size (in bytes) for each chunked cache entry.
     * Longtext can store more, but splitting ensures safer inserts and reads.
     */
    const OES_MAX_CHUNK_SIZE = 65535;

    /**
     * Storage constructor.
     *
     * Initializes the cache storage by setting the table name.
     * Uses the WordPress $wpdb global for database access.
     */
    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'oes_cache';
    }

    /** @inheritdoc */
    public function get(string $key)
    {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cache_value 
                 FROM {$this->table}
                 WHERE cache_key = %s
                 ORDER BY part ASC",
                $key
            ),
            ARRAY_A
        );

        if (!$rows) {
            return null;
        }

        $serialized = implode('', array_column($rows, 'cache_value'));

        return unserialize($serialized);
    }

    /** @inheritdoc */
    public function set(string $key, $value, array $args = []): bool
    {
        global $wpdb;

        $serialized = serialize($value);
        $chunks = $this->safe_chunk_split($serialized, self::OES_MAX_CHUNK_SIZE);

        $wpdb->delete($this->table, ['cache_key' => $key]);

        $created = current_time('mysql');

        foreach ($chunks as $index => $chunk) {
            $wpdb->insert($this->table, [
                'cache_key' => $key,
                'part' => $index,
                'cache_value' => $chunk,
                'created_at' => $created,
                'cache_type' => $args['cache_type'] ?? '',
                'object_type' => $args['object_type'] ?? '',
                'class' => $args['class'] ?? '',
                'cache_language' => $args['cache_language'] ?? '',
                'additional' => json_encode($args['additional'] ?? '')
            ]);

            oes_write_log(sprintf(
                'Cache was created for %s at %s.',
                $key,
                $created
            ), 'Cache');
        }

        return true;
    }

    /** @inheritdoc */
    public function delete(string $key): void
    {
        global $wpdb;
        $wpdb->delete($this->table, ['cache_key' => $key]);
    }

    /** @inheritdoc */
    public function delete_by_prefix(string $prefix): void
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table}
                 WHERE cache_key LIKE %s",
                $wpdb->esc_like($prefix) . '%'
            )
        );
    }

    /**
     * Splits a UTF-8 string safely at byte boundaries without breaking characters.
     *
     * @param string $string The full string to split.
     * @param int $chunk_size Maximum chunk size in bytes.
     *
     * @return array An array of UTF-8-safe string chunks.
     */
    protected function safe_chunk_split(string $string, int $chunk_size): array
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

    public function regenerate(string $key): void {

        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cache_type, class, object_type, cache_language, additional 
                 FROM {$this->table}
                 WHERE cache_key = %s
                 AND part = 0",
                $key
            ),
            ARRAY_A
        );

        if (!$rows) {
            return;
        }

        $cacheType = $rows[0]['cache_type'] ?? '';
        if($cacheType == 'archive') {
            $this->regenerate_archive($key, $rows[0]);
        }
        elseif(function_exists('oes_cache_regenerate_' . $cacheType)) {
            call_user_func('oes_cache_regenerate_' . $cacheType, $key, $rows[0]);
        }
    }

    protected function regenerate_archive(string $key, array $row): void {

        $class = $row['class'] ?? '';
        $objectType = $row['object_type'] ?? '';
        $cacheLanguage = $row['cache_language'] ?? '';
        $args = json_decode($row['additional'] ?? '', true);

        $args['language'] = $cacheLanguage;
        $args['object'] = $objectType;

        if(post_type_exists($objectType)){
            $args['post-type'] = $objectType;
        }
        elseif(taxonomy_exists($objectType)){
            $args['taxonomy'] = $objectType;
        }
        else {
            $args['index'] = '';//TODO index?
        }

        $this->delete($key);

        oes_set_archive_data($class, $args, false);
    }
}
