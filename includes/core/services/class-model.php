<?php

namespace OES\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Registry for post types, taxonomies and media groups.
 */
final class Model implements \OES\Service\Exportable
{
    private array $post_types = [];
    private array $taxonomies = [];
    private array $media_groups = [];

    public function post_types(): array
    {
        return $this->post_types;
    }

    public function taxonomies(): array
    {
        return $this->taxonomies;
    }

    public function media_groups(): array
    {
        return $this->media_groups;
    }

    public function post_type(string $key): ?array
    {
        return $this->post_types[$key] ?? null;
    }

    public function taxonomy(string $key): ?array
    {
        return $this->taxonomies[$key] ?? null;
    }

    public function set_post_type(string $postTypeKey, array $oesArgs, array $registerArgs = []): void
    {
        foreach (\OES\Model\get_post_type_oes_args_defaults() as $configKey => $default) {
            $this->post_types[$postTypeKey][$configKey] = $oesArgs[$configKey] ?? $default;
        }

        if (empty($this->post_types[$postTypeKey]['label'])) {
            $this->post_types[$postTypeKey]['label'] = $registerArgs['label'] ?? $postTypeKey;
        }

        foreach (['parent', 'version'] as $parameter) {
            if (!empty($oesArgs[$parameter]) && ($oesArgs[$parameter] !== 'none')) {
                $this->post_types[$postTypeKey][$parameter] = $oesArgs[$parameter];
            }
        }

        $options = \OES\Model\get_schema_config($this->post_types[$postTypeKey]['schema'] ?? '');

        foreach ($options as $group) {
            foreach ($group as $key => $parameter) {
                $this->post_types[$postTypeKey][$key] = $oesArgs[$key] ?? (empty($parameter['multiple']) ? '' : []);
            }
        }

        $themeLabels = array_merge([], $oesArgs['theme_labels'] ?? []);
        if (!empty($themeLabels)) {
            $this->post_types[$postTypeKey]['theme_labels'] = $themeLabels;
        }
    }

    public function set_taxonomy(string $key, array $oesArgs): void
    {
        foreach (\OES\Model\get_taxonomy_oes_args_defaults() as $configKey => $default) {
            $this->taxonomies[$key][$configKey] = $oesArgs[$configKey] ?? $default;
        }

        if (empty($this->taxonomies[$key]['label'])) {
            $this->taxonomies[$key]['label'] = $key;
        }
    }

    public function set_field_options(string $component, string $key, array $fields, int|string $postID, array $languageKeys, array $apiKeys): void
    {
        $component = match ($component) {
            'taxonomy', 'taxonomies' => 'taxonomies',
            'post_type', 'post_types' => 'post_types',
            default => throw new \InvalidArgumentException("Unknown component '$component'"),
        };

        $target = &$this->$component;

        $target[$key]['acf_ids'][$postID] = $postID;

        foreach ($fields as $field) {

            if ($field['endpoint'] ?? false) {
                continue;
            }

            $target[$key]['field_options'][$field['key']]['label'] = $field['label'];
            $target[$key]['field_options'][$field['key']]['type'] = $field['type'];

            if (!empty($field['taxonomy'] ?? '')) {
                $target[$key]['field_options'][$field['key']]['taxonomy'] = $field['taxonomy'] ?? '';
            }

            foreach ($languageKeys as $language) {
                $target[$key]['field_options'][$field['key']]['label_translation_' . $language] =
                    $field['label_translation_' . $language] ?? $field['label'];
            }

            foreach ([
                         'pattern' => [],
                         'language_dependent' => false,
                         'display_option' => 'none',
                         'display_prefix' => ''
                     ] as $optionKey => $defaultOption) {
                $target[$key]['field_options'][$field['key']][$optionKey] = $field[$optionKey] ?? $defaultOption;
            }

            foreach ($apiKeys as $apiKey => $ignore) {
                if (isset($field[$apiKey . '_properties'])) {
                    $target[$key]['field_options'][$field['key']][$apiKey . '_properties'] =
                        $field[$apiKey . '_properties'];
                }
            }
        }
    }

    public function set_media_field_options(array $fields, int|string $postID, array $languageKeys): void
    {
        $this->media_groups['acf_ids'][] = $postID;

        foreach ($fields as $field) {
            $this->media_groups['fields'][$field['key']]['label'] = $field['label'];
            $this->media_groups['fields'][$field['key']]['type'] = $field['type'];

            foreach ($languageKeys as $language) {
                $this->media_groups['fields'][$field['key']]['label_translation_' . $language] =
                    $field['label_translation_' . $language] ?? $field['label'];
            }

            foreach ([
                         'pattern' => [],
                         'language_dependent' => false,
                         'display_option' => 'none',
                         'display_prefix' => ''
                     ] as $optionKey => $defaultOption) {
                $this->media_groups['fields'][$field['key']][$optionKey] = $field[$optionKey] ?? $defaultOption;
            }
        }
    }

    public function set_media_parameters(array $oesArgs, int|string|null $postID = null): void
    {
        $this->media_groups = $oesArgs;

        if (!empty($postID)) {
            $this->media_groups['post_ID'] = $postID;
        }
    }

    public function export(): array
    {
        return ['post_types' => $this->post_types, 'taxonomies' => $this->taxonomies, 'media_groups' => $this->media_groups];
    }

    public function import(array $data): void
    {
        $this->post_types = is_array($data['post_types'] ?? null) ? $data['post_types'] : [];
        $this->taxonomies = is_array($data['taxonomies'] ?? null) ? $data['taxonomies'] : [];
        $this->media_groups = is_array($data['media_groups'] ?? null) ? $data['media_groups'] : [];
    }
}
