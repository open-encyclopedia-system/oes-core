<?php

//TODO add phpdoc

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* ---------------------------------------------------------------------------------------------------------------------
 * Internal helpers
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * Warn (WP_DEBUG only) if model data is read before it exists.
 * Reading while the model is being built ('oes/build_data_model') is legitimate and does not warn.
 *
 * @internal
 */
function _oes_maybe_warn_model_not_loaded(string $function): void
{
    if (defined('WP_DEBUG') && WP_DEBUG
        && !did_action('oes/data_model_loaded')
        && !doing_action('oes/build_data_model')) {
        _doing_it_wrong($function, 'Called before the OES data model was loaded.', '3.0.0');
    }
}

/**
 * Return $translations[$language] if it is a non-empty string, otherwise ''.
 *
 * @internal
 */
function _oes_get_translation(mixed $translations, string $language): string
{
    return (is_array($translations) && !empty($translations[$language]) && is_string($translations[$language]))
        ? $translations[$language]
        : '';
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Environment
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * Get the OES plugin version.
 *
 * @return string|null The version or null if not available.
 */
function oes_get_version(): ?string
{
    return OES()->get_version();
}

function oes_is_acf_pro(): bool
{
    return OES()->acf_pro;
}

function oes_is_block_theme(): bool
{
    return OES()->block_theme;
}

function oes_is_legacy(): bool
{
    return OES()->legacy;
}

function oes_application_initialized(): bool
{
    return OES()->application_initialized;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Languages
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * Resolve a language key. An empty key falls back to the current global language ($oes_language),
 * then to 'language0'.
 */
function oes_normalize_language(string $language = ''): string
{
    return $language !== '' ? $language : (string)($GLOBALS['oes_language'] ?? 'language0');
}

function oes_get_languages(): array
{
    return OES()->languages()->all();
}

function oes_has_multiple_languages(): bool
{
    return OES()->languages()->has_multiple();
}

function oes_get_language(string $language = ''): array
{
    $language = oes_normalize_language($language);
    return OES()->languages()->get($language) ?? [];
}

function oes_get_language_param(string $language, string $param, mixed $default = null): mixed
{
    return oes_get_language(oes_normalize_language($language))[$param] ?? $default;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Post types
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_get_post_types(): array
{
    return OES()->model()->post_types();
}

function oes_has_post_type(string $postType): bool
{
    return OES()->model()->post_type($postType) !== null;
}

function oes_get_post_type(string $postType = ''): array
{
    _oes_maybe_warn_model_not_loaded(__FUNCTION__);
    return OES()->model()->post_type($postType) ?? [];
}

function oes_get_post_type_param(string $postType = '', string $param = '', mixed $default = null): mixed
{
    return oes_get_post_type($postType)[$param] ?? $default;
}

/**
 * Get the (translated) label of a post type.
 *
 * Lookup order:
 *  1. Plural label translation for the given language
 *  2. Theme label "archive__header" for the given language
 *  3. Generic (untranslated) post type label
 *  4. $default (if empty: the label registered with WordPress, then the key)
 *
 * @param string $postType Post type key.
 * @param string $language Language key. Normalized via oes_normalize_language().
 * @param string $default  Returned if no label is found.
 */
function oes_get_post_type_label(string $postType = '', string $language = '', string $default = ''): string
{
    if ($postType === '') {
        return $default;
    }

    $language = oes_normalize_language($language);

    $label = _oes_get_translation(oes_get_post_type_param($postType, 'label_translations_plural'), $language);
    if ($label !== '') {
        return $label;
    }

    $label = oes_get_post_type_theme_label($postType, 'archive__header', '', $language);
    if ($label !== '') {
        return $label;
    }

    $label = oes_get_post_type_param($postType, 'label');
    if (is_string($label) && $label !== '') {
        return $label;
    }

    if ($default !== '') {
        return $default;
    }

    return get_post_type_object($postType)->label ?? $postType;
}

/**
 * Get the label of a post type inside admin pages.
 *
 * @param string $postType Post type key.
 * @param string $default  Returned if no label is found.
 */
function oes_get_post_type_admin_label(string $postType = '', string $default = ''): string
{
    if ($postType === '') {
        return $default;
    }

    $label = oes_get_post_type_param($postType, 'label');
    if (is_string($label) && $label !== '') {
        return $label;
    }

    if ($default !== '') {
        return $default;
    }

    return get_post_type_object($postType)->label ?? $postType;
}

/**
 * Get a theme label of a post type.
 *
 * @param string $postType Post type key.
 * @param string $key      Theme label key (e.g. 'archive__header').
 * @param mixed  $default  Returned if no label is set. (Kept before $language for backwards compatibility.)
 * @param string $language Language key. Normalized via oes_normalize_language().
 */
function oes_get_post_type_theme_label(string $postType, string $key, mixed $default = '', string $language = ''): string
{
    $language = oes_normalize_language($language);
    $label = oes_get_post_type_param($postType, 'theme_labels', [])[$key][$language] ?? $default;
    return is_string($label) ? $label : '';
}

function oes_get_post_type_field_config(string $postType, string $fieldKey, mixed $default = []): mixed
{
    return oes_get_post_type_param($postType, 'field_options', [])[$fieldKey] ?? $default;
}

function oes_get_post_type_field_option(string $postType, string $fieldKey, string $option, mixed $default = null): mixed
{
    return oes_get_post_type_field_config($postType, $fieldKey)[$option] ?? $default;
}

function oes_get_post_type_field_label(string $postType, string $fieldKey, string $language = '', mixed $default = null): mixed
{
    $language = oes_normalize_language($language);
    $field = oes_get_post_type_field_config($postType, $fieldKey);
    return $field['label_translation_' . $language] ?? ($field['label'] ?? $default);
}

function oes_get_post_type_titles(string $postType, string $option, mixed $default = null): mixed
{
    return oes_get_post_type_param($postType, 'display_titles', [])[$option] ?? $default;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Taxonomies
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_get_taxonomies(): array
{
    return OES()->model()->taxonomies();
}

function oes_has_taxonomy(string $taxonomy): bool
{
    return OES()->model()->taxonomy($taxonomy) !== null;
}

function oes_get_taxonomy(string $taxonomy = ''): array
{
    _oes_maybe_warn_model_not_loaded(__FUNCTION__);
    return OES()->model()->taxonomy($taxonomy) ?? [];
}

function oes_get_taxonomy_param(string $taxonomy = '', string $param = '', mixed $default = null): mixed
{
    return oes_get_taxonomy($taxonomy)[$param] ?? $default;
}

/**
 * Get the (translated) label of a taxonomy.
 *
 * Lookup order:
 *  1. Plural label translation for the given language
 *  2. Singular label translation for the given language
 *  3. Generic (untranslated) taxonomy label
 *  4. $default (if empty: the label registered with WordPress, then the key)
 *
 * @param string $taxonomy Taxonomy key.
 * @param string $language Language key. Normalized via oes_normalize_language().
 * @param string $default  Returned if no label is found.
 */
function oes_get_taxonomy_label(string $taxonomy = '', string $language = '', string $default = ''): string
{
    if ($taxonomy === '') {
        return $default;
    }

    $language = oes_normalize_language($language);

    foreach (['label_translations_plural', 'label_translations'] as $param) {
        $label = _oes_get_translation(oes_get_taxonomy_param($taxonomy, $param), $language);
        if ($label !== '') {
            return $label;
        }
    }

    $label = oes_get_taxonomy_param($taxonomy, 'label');
    if (is_string($label) && $label !== '') {
        return $label;
    }

    if ($default !== '') {
        return $default;
    }

    return get_taxonomy($taxonomy)->label ?? $taxonomy;
}

function oes_get_taxonomy_field_config(string $taxonomy, string $fieldKey, mixed $default = []): mixed
{
    return oes_get_taxonomy_param($taxonomy, 'field_options', [])[$fieldKey] ?? $default;
}

function oes_get_taxonomy_titles(string $taxonomy, string $option, mixed $default = null): mixed
{
    return oes_get_taxonomy_param($taxonomy, 'display_titles', [])[$option] ?? $default;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Generic object accessors (post types + taxonomies)
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * @param string $component 'post_types' | 'taxonomies' (singular forms 'post_type' | 'taxonomy' are accepted).
 */
function oes_get_component_param(string $component, string $object, string $param, mixed $default = null): mixed
{
    return match ($component) {
        'post_types', 'post_type' => oes_get_post_type_param($object, $param, $default),
        'taxonomies', 'taxonomy'  => oes_get_taxonomy_param($object, $param, $default),
        default                   => $default,
    };
}

function oes_get_object_param(string $object = '', string $param = '', bool $isTaxonomy = false, mixed $default = null): mixed
{
    return $isTaxonomy
        ? oes_get_taxonomy_param($object, $param, $default)
        : oes_get_post_type_param($object, $param, $default);
}

/**
 * Get a localized label for an object, which can be a post type or a taxonomy.
 *
 * @param string $object   The object key (post type or taxonomy).
 * @param string $language Optional. Language key. Normalized via oes_normalize_language().
 * @return string The localized label or the object key as a fallback.
 */
function oes_get_object_label(string $object, string $language = ''): string
{
    if (post_type_exists($object)) {
        return oes_get_post_type_label($object, $language);
    }

    if (taxonomy_exists($object)) {
        return oes_get_taxonomy_label($object, $language);
    }

    return $object;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Media
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_get_media(): array
{
    return OES()->model()->media_groups();
}

function oes_get_media_param(string $param = '', mixed $default = null): mixed
{
    return oes_get_media()[$param] ?? $default;
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Theme (index pages, theme labels, search)
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_get_index_pages(): array
{
    return OES()->theme()->get('theme_index_pages', []);
}

function oes_has_index_page(string $index): bool
{
    return isset(oes_get_index_pages()[$index]);
}

/**
 * @param string $key Index page key. If empty, the current index page ($oes_is_index) is used.
 */
function oes_get_index_page(string $key = ''): array
{
    $key = $key ?: ($GLOBALS['oes_is_index'] ?? '');
    return oes_get_index_pages()[$key] ?? [];
}

function oes_get_theme_labels(): array
{
    return OES()->theme()->get('theme_labels', []);
}

function oes_get_search_param(string $param = '', mixed $default = null): mixed
{
    return OES()->theme()->get('search', [])[$param] ?? $default;
}

function oes_get_config_post(): ?int
{
    return OES()->theme()->config_post();
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Admin
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * @param string $param 'containers' | 'separators' | 'include_menu'
 * @return array|null Null for an unknown parameter.
 */
function oes_get_admin_param(string $param): ?array
{
    return OES()->admin()->get($param);
}

function oes_get_containers(): array
{
    return oes_get_admin_param('containers') ?? [];
}

function oes_get_container(string $key): array
{
    return oes_get_containers()[$key] ?? [];
}

function oes_add_container(string $key, array $data): void
{
    OES()->admin()->add_container($key, $data);
}

function oes_get_separators(): array
{
    return oes_get_admin_param('separators') ?? [];
}

function oes_get_include_menu(): array
{
    return oes_get_admin_param('include_menu') ?? [];
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Registries (APIs, modules, tools)
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_get_apis(): array
{
    return OES()->apis()->all();
}

function oes_get_api(string $apiKey): mixed
{
    return OES()->apis()->get($apiKey);
}

function oes_add_api(string $class, string $apiKey): void
{
    $instance = new $class($apiKey);
    OES()->apis()->set($apiKey, $instance);
}

function oes_get_modules(): array
{
    return OES()->modules()->all();
}

function oes_get_module(string $key): mixed
{
    return OES()->modules()->get($key);
}

function oes_add_module(string $key, mixed $instance): void
{
    OES()->modules()->set($key, $instance);
}

function oes_get_tools(): array
{
    return OES()->tools()->all();
}

function oes_get_tool(string $key): mixed
{
    return OES()->tools()->get($key);
}

function oes_add_tool(string $class, string $name = '', array $args = []): void
{
    $instance = empty($args) ? new $class($name) : new $class($name, $args);
    OES()->tools()->set($instance->name, $instance);
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Build-time setters
 *
 * These write into the compiled model. Call them from 'oes/build_data_model' only, so they run on a cache rebuild.
 * ------------------------------------------------------------------------------------------------------------------ */

function oes_set_post_type(string $key, array $oesArgs, array $registerArgs = []): void
{
    OES()->model()->set_post_type($key, $oesArgs, $registerArgs);
}

function oes_set_taxonomy(string $key, array $oesArgs): void
{
    OES()->model()->set_taxonomy($key, $oesArgs);
}

/**
 * @param string $component 'post_type' | 'taxonomy' (plural forms are accepted).
 */
function oes_add_field_data(string $component, string $key, array $fields, int|string $postID): void
{
    OES()->set_field_options($component, $key, $fields, $postID);
}

function oes_add_media_data(array $oesArgs, int|string|null $postID = null): void
{
    OES()->set_media_parameters($oesArgs, $postID);
}

function oes_add_media_field_data(array $fields, int|string|null $postID = null): void
{
    OES()->set_media_field_options($fields, $postID);
}

function oes_set_general_parameters(array $config, int|string|null $postID = null): void
{
    OES()->set_general_parameters($config, $postID);
}

/* ---------------------------------------------------------------------------------------------------------------------
 * Deprecated
 * ------------------------------------------------------------------------------------------------------------------ */

/**
 * @deprecated 3.1.0 Use oes_get_post_type_label() instead.
 */
function oes_get_localized_post_type_label(string $postType, string $language = ''): string
{
    _deprecated_function(__FUNCTION__, '3.1.0', 'oes_get_post_type_label()');
    return oes_get_post_type_label($postType, $language);
}

/**
 * @deprecated 3.1.0 Use oes_get_taxonomy_label() instead.
 */
function oes_get_localized_taxonomy_label(string $taxonomy, string $language = ''): string
{
    _deprecated_function(__FUNCTION__, '3.1.0', 'oes_get_taxonomy_label()');
    return oes_get_taxonomy_label($taxonomy, $language);
}

if (!function_exists('get_taxonomy_label')) {
    /**
     * Unprefixed legacy name, guarded against collisions.
     *
     * @deprecated 3.1.0 Use oes_get_taxonomy_label() instead.
     */
    function get_taxonomy_label(string $taxonomy, string $language = 'language0'): string
    {
        _deprecated_function(__FUNCTION__, '3.1.0', 'oes_get_taxonomy_label()');
        return oes_get_taxonomy_label($taxonomy, $language);
    }
}
