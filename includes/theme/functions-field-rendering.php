<?php

namespace OES\Field;

/**
 * Render a field as HTML.
 */
function render(array $args): string
{
    global $oes_post, $oes_term, $oes_language;

    if ((empty($oes_post) && empty($oes_term)) || empty($args['field'])) {
        return '';
    }

    $field = $args['field'];

    $value = empty($oes_post)
        ? get_term_field_value($field, $oes_term)
        : get_post_field_value($field, $oes_post, $args);

    if (empty($value)) {
        return '';
    }

    $header = build_header($args, $oes_language, $oes_post, $oes_term);

    $prefix = $args['prefix'][$oes_language]
        ?? $args['prefix_' . $oes_language]
        ?? ($args['prefix'] ?? '');

    $class = $args['class'] ?? '';

    return sprintf(
        '%s<div class="%s">%s%s</div>',
        $header,
        esc_attr($class),
        $prefix,
        $value
    );
}

/**
 * Get field value for a taxonomy term.
 */
function get_term_field_value(string $field, object $term): mixed
{
    return oes_get_field($field, $term->taxonomy . '_' . $term->object_ID);
}

/**
 * Get field value for a post context.
 */
function get_post_field_value(string $field, object $post, array $args): mixed
{
    if (!empty($args['parent'])) {
        return oes_get_field_display_value($field, $post->parent_ID);
    }

    if (!empty($args['version'])) {
        $versionId = \OES\Versioning\get_current_version_id($post->object_ID);

        return $versionId
            ? oes_get_field_display_value($field, $versionId)
            : '';
    }

    if (!empty($args['relation'])) {
        return get_relation_field_value($field, $post, $args);
    }

    $type = $args['type'] ?? 'value-display';

    return $post->fields[$field][$type] ?? '';
}

/**
 * Render relation field values.
 */
function get_relation_field_value(
    string $field,
    object $post,
    array $args
): string {

    $rawValue = oes_get_field($field, $post->object_ID);
    $fieldObject = oes_get_field_object($field, $post->object_ID);

    $posts = [];

    if (($fieldObject['return_format'] ?? '') === 'id' && is_array($rawValue)) {
        foreach ($rawValue as $id) {

            $targetId = $args['relation'] === 'version'
                ? \OES\Versioning\get_current_version_id($id)
                : \OES\Versioning\get_parent_id($id);

            if ($targetId) {
                $posts[] = get_post($targetId);
            }
        }
    }

    if (!empty($args['list-class'])) {
        $args['class'] = $args['list-class'];
    }

    return oes_display_post_array_as_list(
        $posts,
        $args['list-id'] ?? false,
        $args
    );
}

/**
 * Build field header HTML.
 */
function build_header(
    array $args,
    string $language = '',
    ?object $post = null,
    ?object $term = null
): string {

    $headerText = $args['header'][$language]
        ?? $args['header']
        ?? ($args['header_' . $language] ?? '');

    if (empty($headerText) || !is_string($headerText)) {
        return '';
    }

    if ($post) {
        return $post->generate_table_of_contents_header($headerText);
    }

    if ($term) {
        return $term->generate_table_of_contents_header($headerText);
    }

    return '';
}