<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

final class Registry
{
    protected array $items = [];

    /** @param class-string|null $type Optional: only accept instances of this class or interface. */
    public function __construct(private readonly ?string $type = null) {}

    public function set(string $key, mixed $item, bool $replace = false): bool
    {
        if ($this->type !== null && !($item instanceof $this->type)) {
            throw new \InvalidArgumentException("Item '$key' must implement {$this->type}.");
        }
        if (isset($this->items[$key]) && !$replace) {
            return false;
        }
        $this->items[$key] = $item;
        return true;
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }
}