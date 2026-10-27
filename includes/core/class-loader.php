<?php

namespace OES\Core;

use OES\Service\Exportable;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Decides between cache hit and rebuild, and fans the bundle out to the services.
 */
final class Loader
{
    /** @param array<string,Exportable> $services */
    public function __construct(
        private readonly Cache $cache,
        private readonly array $services
    ) {}

    /**
     * @param callable $build Fills the services. Called only on a cache miss.
     * @return bool True on cache hit.
     */
    public function load(callable $build): bool
    {
        $bundle = $this->cache->get();

        if (is_array($bundle)) {
            foreach ($this->services as $name => $service) {
                $service->import(is_array($bundle[$name] ?? null) ? $bundle[$name] : []);
            }
            return true;
        }

        $revisionBefore = $this->cache->revision();

        $build();

        $export = array_map(function ($service) {
            return $service->export();
        }, $this->services);

        $this->cache->set($export, $revisionBefore);

        return false;
    }
}
