<?php

namespace OES\Caching;

use WP_Post;
use WP_Term;

/**
 * Class Manager
 *
 * Provides a high-level interface for cache management.
 * Acts as a wrapper around any Storage_Interface implementation.
 */
class Manager {

    /**
     * The storage backend used to persist cache entries.
     *
     * @var Storage_Interface
     */
    private Storage_Interface $storage;

    /**
     * Manager constructor.
     *
     * @param Storage_Interface $storage The storage implementation to use (e.g., Database_Storage, Redis_Storage).
     */
    public function __construct(Storage_Interface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Handles cache clearing when a post is saved, trashed, or deleted.
     *
     * @param int $postID The post ID.
     * @return void
     */
    public function clear_archive_cache_post(int $postID): void
    {
        if (wp_is_post_autosave($postID) || wp_is_post_revision($postID)) {
            return;
        }

        $post = get_post($postID);
        if (!$post instanceof WP_Post) {
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

        $this->clear_archive_cache($postType, $class, $language);
    }

    /**
     * Handles cache clearing when a term is saved, trashed or deleted.
     *
     * @param int $termID The term ID.
     * @param int $tt_id Term taxonomy ID.
     * @param string $taxonomy The taxonomy name.
     * @param WP_Term $deletedTerm The term object before deletion.
     * @return void
     */
    public function clear_archive_cache_term(
        int $termID,
            $tt_id,
        string $taxonomy,
        $deletedTerm = null
    ): void {

        if ($deletedTerm instanceof WP_Term) {
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

        $this->clear_archive_cache($term->taxonomy, $class);
    }

    /**
     * Clears archive cache for a specific object (post type or taxonomy) in all languages.
     *
     * @param string $objectKey Post type or taxonomy key.
     * @param string $class Archive class name (or fallback).
     * @param string $postLanguage The cache language.
     * @return void
     */
    private function clear_archive_cache(
        string $objectKey,
        string $class,
        string $postLanguage = 'all'
    ): void {

        global $oes;

        $languages = $postLanguage === 'all'
            ? array_keys($oes->languages)
            : [$postLanguage];

        $keys = array_map(
            static fn(string $language): string => $objectKey . '-' . sanitize_key($class) . '-' . sanitize_key($language),
            $languages
        );

        $keys = $this->modify_cache_keys($keys, ['object' => $objectKey, 'class' => $class]);

        foreach ($keys as $key) {
            $this->delete($key);
        }

        foreach ($oes->theme_index_pages as $indexKey => $indexData) {
            if (in_array($objectKey, $indexData['objects'] ?? [])) {

                $archiveClass = $indexKey . '_Index_Archive';
                if (!class_exists($archiveClass)) $archiveClass = 'OES_Index_Archive';

                $keys = array_map(
                    static fn(string $language): string => 'oes_cache-' . $indexKey . '-' . sanitize_key($archiveClass) . '-' . sanitize_key($language),
                    array_keys($oes->languages)
                );

                $keys = $this->modify_cache_keys($keys, ['index' => $indexKey, 'class' => $archiveClass]);

                foreach ($keys as $key) {
                    $this->delete($key);
                }
            }
        }
    }

    /**
     * Applies a filter to modify the provided cache keys before use.
     *
     * @param array $keys Array of cache keys to be filtered.
     * @param array $args Optional. Context arguments for the filter (e.g., object type, class name). Default [].
     * @return array The filtered array of cache keys.
     */
    protected function modify_cache_keys(array $keys, array $args = []): array
    {
        if (has_filter('oes/modify_cache_keys')) {
            return apply_filters('oes/modify_cache_keys', $keys, $args);
        }
        return $keys;
    }

    /**
     * Retrieve a cached value by key.
     *
     * @param string $key The cache key.
     * @return mixed|null Returns the cached value, or null if not found.
     */
    public function get(string $key)
    {
        return $this->storage->get($key);
    }

    /**
     * Store a value in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to store.
     * @param array $args Optional metadata and settings for the cache entry.
     *
     * @return bool True on success, false on failure.
     */
    public function set(string $key, $value, array $args): bool
    {
        return $this->storage->set($key, $value, $args);
    }

    /**
     * Delete a cached entry by key.
     *
     * @param string $key The cache key to delete.
     * @return void
     */
    public function delete(string $key): void
    {
        $this->storage->delete($key);
    }

    /**
     * Regenerate a cached entry by key.
     *
     * @param string $key The cache key to delete.
     * @return void
     */
    public function regenerate(string $key): void
    {
        $this->storage->regenerate($key);
    }

    /**
     * Delete all cached entries with a key starting with the given prefix.
     *
     * @param string $prefix The key prefix to match.
     * @return void
     */
    public function clear_by_prefix(string $prefix): void
    {
        $this->storage->delete_by_prefix($prefix);
    }
}
