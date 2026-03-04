<?php

namespace OES\Caching;

/**
 * Interface for cache storage backends.
 */
interface Storage_Interface {

    /**
     * Retrieve a cached value by key.
     *
     * @param string $key The cache key.
     * @return mixed|null Returns the cached value if found, or null if not found.
     */
    public function get(string $key);

    /**
     * Store a value in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to store (can be any PHP type).
     * @param array $args Optional settings for the cache.
     * @return bool True on success, false on failure.
     */
    public function set(string $key, $value, array $args = []): bool;

    /**
     * Delete a cached value by key.
     *
     * @param string $key The cache key to delete.
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Regenerate a cached value by key.
     *
     * @param string $key The cache key to delete.
     * @return void
     */
    public function regenerate(string $key): void;

    /**
     * Delete all cached entries where the key starts with the given prefix.
     *
     * @param string $prefix The key prefix to match for deletion.
     * @return void
     */
    public function delete_by_prefix(string $prefix): void;
}
