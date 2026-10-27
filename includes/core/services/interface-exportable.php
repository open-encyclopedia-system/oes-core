<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implemented by every service whose state ends up in the compiled-model cache.
 */
interface Exportable
{
    /** @return array Plain, serializable data (no closures, no objects). */
    public function export(): array;

    /** Restore state from a previous export(). Must tolerate malformed input. */
    public function import(array $data): void;
}
