<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

final class Languages implements \OES\Service\Exportable
{
    private const DEFAULTS = [
        'language0' => ['label' => 'Deutsch', 'abb' => 'DEU', 'locale' => 'de_DE'],
    ];

    private array $items = self::DEFAULTS;

    public function load(mixed $raw): void
    {
        if (!is_array($raw)) {
            return;
        }

        foreach ($raw as $key => $language) {
            if (!is_array($language)) {
                continue;
            }
            $key = (string)$key;
            $normalized = str_starts_with($key, 'language') ? $key : 'language' . $key;

            $this->items[$normalized] = array_merge(['label' => 'Label missing', 'abb' => $key], $language);
        }
    }

    public function all(): array
    {
        return $this->items;
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function get(string $key): ?array
    {
        return $this->items[$key] ?? null;
    }

    public function has_multiple(): bool
    {
        return count($this->items) > 1;
    }

    public function export(): array
    {
        return $this->items;
    }

    public function import(array $data): void
    {
        $this->items = $data ?: self::DEFAULTS;
    }
}
