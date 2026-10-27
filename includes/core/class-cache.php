<?php

namespace OES\Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Revision-based cache for the compiled model.
 */
final class Cache
{
    private const REVISION_OPTION = 'oes_config_revision';
    private const PAYLOAD_OPTION = 'oes_config_cache';
    private const GROUP = 'oes';

    private bool $bumped = false;

    public function __construct(private readonly string $coreVersion)
    {
    }

    public function revision(): int
    {
        return (int)get_option(self::REVISION_OPTION, 1);
    }

    /** Returns the cached export, or null on miss / stale / version mismatch. */
    public function get(): ?array
    {
        $rev = $this->revision();
        $payload = wp_cache_get(self::PAYLOAD_OPTION, self::GROUP);

        if ($payload === false) {
            $payload = get_option(self::PAYLOAD_OPTION, null);
            if (is_array($payload)) {
                wp_cache_set(self::PAYLOAD_OPTION, $payload, self::GROUP);
            }
        }

        if (!is_array($payload)
            || ($payload['rev'] ?? null) !== $rev
            || ($payload['core'] ?? null) !== $this->coreVersion
            || !is_array($payload['data'] ?? null)) {
            return null;
        }
        return $payload['data'];
    }

    /**
     * @param int $builtAtRevision The revision read BEFORE building. If something invalidated during the build,
     *                             the revision has moved on, and we discard instead of storing a stale model.
     */
    public function set(array $data, int $builtAtRevision): void
    {
        if ($builtAtRevision !== $this->revision()) {
            return;
        }
        $payload = ['rev' => $builtAtRevision, 'core' => $this->coreVersion, 'data' => $data];

        update_option(self::PAYLOAD_OPTION, $payload, false);
        wp_cache_set(self::PAYLOAD_OPTION, $payload, self::GROUP);
    }

    /** Bump once per request, no matter how many hooks fire. */
    public function invalidate(): void
    {
        if ($this->bumped) {
            return;
        }
        $this->bumped = true;
        update_option(self::REVISION_OPTION, $this->revision() + 1, true);
        wp_cache_delete(self::PAYLOAD_OPTION, self::GROUP);
    }

    /** Hook everything that can change the compiled model. */
    public function register_invalidation_hooks(): void
    {
        add_action('oes/config_updated', [$this, 'invalidate']);
        //TODO other invalidation after data model loaded, factory reload etc.?
    }
}
