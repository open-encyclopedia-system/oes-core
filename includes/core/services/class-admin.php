<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Admin page configuration (containers, separators).
 */
final class Admin implements \OES\Service\Exportable
{
    private array $containers = [];
    private array $separators = [];
    private array $include_menu = [];

    public function get(string $key): ?array
    {
        if ($key == 'containers') {
            return $this->containers;
        } elseif ($key == 'separators') {
            return $this->separators;
        } elseif ($key == 'include_menu') {
            return $this->include_menu;
        }

        return null;
    }

    public function load_containers(mixed $raw): void
    {
        if (!is_array($raw)) {
            return;
        }
        foreach ($raw as $id => $container) {
            $this->containers[$id] = $container;   // @oesDevelopment validate shape / merge defaults
        }
    }

    public function load_separators(mixed $raw): void
    {
        if (!is_array($raw)) {
            return;
        }
        foreach ($raw as $separator) {
            if (is_string($separator) || is_int($separator)) {
                $this->separators[] = (string)$separator;
            }
        }
    }

    public function load_include_menu(mixed $raw): void
    {
        if (!is_array($raw)) {
            return;
        }
        foreach ($raw as $slug) {
            if (is_string($slug)) {
                $this->include_menu[] = $slug;
            }
        }
    }

    /** Shape kept identical to the old $admin_pages for compat. */
    public function all(): array
    {
        return ['container' => $this->containers, 'separators' => $this->separators, 'include_menu' => $this->include_menu];
    }

    public function add_container(string $key, array $data): void
    {
        $this->containers[$key] = $data;
    }

    public function export(): array
    {
        return $this->all();
    }

    public function import(array $data): void
    {
        $this->containers = is_array($data['container'] ?? null) ? $data['container'] : [];
        $this->separators = is_array($data['separators'] ?? null) ? $data['separators'] : [];
        $this->include_menu = is_array($data['include_menu'] ?? null) ? $data['include_menu'] : [];
    }
}
