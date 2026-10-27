<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * General theme settings: config post ID, theme index pages, theme labels, search.
 */
final class Theme implements \OES\Service\Exportable
{
    private const KEYS = ['theme_index_pages', 'theme_labels', 'search'];

    private ?int $config_post = null;
    private array $values = [];

    public function set_config_post(int|string|null $postID): void
    {
        $this->config_post = $postID === null ? null : (int)$postID;
    }

    public function config_post(): ?int
    {
        return $this->config_post;
    }

    public function load(array $config): void
    {
        foreach (self::KEYS as $key) {
            if (isset($config[$key])) {
                $this->values[$key] = $config[$key];
            }
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function export(): array
    {
        return ['config_post' => $this->config_post, 'values' => $this->values];
    }

    public function import(array $data): void
    {
        $this->config_post = isset($data['config_post']) ? (int)$data['config_post'] : null;
        $this->values = is_array($data['values'] ?? null) ? $data['values'] : [];
    }
}
