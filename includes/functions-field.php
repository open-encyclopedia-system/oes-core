<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Format a post ID or term object into the string ACF expects for get_field() calls.
 *
 * For posts, returns the raw integer ID.
 * For terms, returns "taxonomy_termid" (e.g. "category_42").
 *
 * @param int  $id     The post or term ID.
 * @param bool $isPost Whether this ID belongs to a post (true) or a term (false).
 *
 * @return int|string
 */
function oes_format_object_id(int $id, bool $isPost)
{
    if ($isPost) {
        return $id;
    }

    $term = get_term($id);
    if ($term instanceof WP_Term) {
        return "{$term->taxonomy}_{$id}";
    }

    return $id;
}

/**
 * Get an ACF field value from a post or term, resolving parent references if needed.
 *
 * Supports both WP_Post and WP_Term objects (or their IDs) and handles
 * ACF-style 'parent__field_name' for parent reference resolution.
 *
 * @param string              $fieldName   The ACF field name or key, optionally prefixed with 'parent__'.
 * @param WP_Post|WP_Term|int $object      The post or term object or ID the field is associated with.
 * @param bool                $formatValue Whether to apply ACF formatting. Defaults to true.
 *
 * @return mixed The ACF field value, or null if not found.
 */
function oes_get_field(string $fieldName, $object = false, bool $formatValue = true)
{
    if (!$object) return null;

    [$resolvedID, $resolvedField, $isPost] = oes_resolve_field_context($object, $fieldName);
    if (empty($resolvedID) || empty($resolvedField)) {
        return null;
    }

    $objectID = oes_format_object_id($resolvedID, $isPost);

    return get_field($resolvedField, $objectID, $formatValue);
}

/**
 * Retrieve a field value from a post or taxonomy term, with fallbacks and formatting.
 *
 * @param int|string $objectID Post ID or Term ID
 * @param string     $fieldKey The ACF field key or special identifier (e.g. 'display-title')
 * @param array      $args     Additional arguments
 *
 * @return mixed The resolved value or empty string on failure.
 */
function oes_get_object_display_value($objectID, string $fieldKey, array $args = [])
{
    [$resolvedObjectID, $resolvedFieldKey, $isPost] = oes_resolve_field_context($objectID, $fieldKey);

    if ($isPost) {
        if ($resolvedFieldKey === 'display-title') {
            return oes_get_display_title($resolvedObjectID);
        }

        if ($resolvedFieldKey === 'wp-title') {
            return get_the_title($resolvedObjectID);
        }

        if (str_starts_with($resolvedFieldKey, 'taxonomy__') || str_starts_with($resolvedFieldKey, 'parent_taxonomy__')) {
            [$postID, $taxonomy] = oes_resolve_taxonomy_context($resolvedObjectID, $resolvedFieldKey);
            if ($taxonomy === 'invalid') return '';

            $terms = get_the_terms($postID, $taxonomy);
            return !empty($terms) ? oes_get_display_title($terms[0]) : '';
        }

        return oes_get_field_display_value($resolvedFieldKey, $resolvedObjectID, $args);
    }

    $term = get_term($resolvedObjectID);
    if (!$term || is_wp_error($term)) {
        return '';
    }

    if ($resolvedFieldKey === 'display-title') {
        return oes_get_display_title($term);
    }

    if ($resolvedFieldKey === 'wp-title') {
        return $term->name;
    }

    return oes_get_field_display_value($resolvedFieldKey, 'term_' . $resolvedObjectID, $args);
}

/**
 * Main function to get an ACF field object.
 *
 * @param string    $fieldName The field name or key.
 * @param int|false $postID    Optional post ID for context.
 *
 * @return array|false The field object.
 */
function oes_get_field_object(string $fieldName, $postID = false)
{
    if ($postID) {
        return get_field_object($fieldName, $postID);
    }
    return get_field_object($fieldName);
}

/**
 * Get all custom field objects for a specific post_id.
 *
 * @param mixed $postID The post_id of which the value is saved against.
 *
 * @return array The fields.
 */
function oes_get_field_objects($postID = false): array
{
    return get_field_objects($postID);
}

/**
 * Get the display label for a select field value (single or multiple).
 *
 * @param string    $fieldName The field name.
 * @param int|false $postID    The post ID.
 *
 * @return array|string Returns the selected label(s), or an empty string if not found.
 */
function oes_get_select_field_value(string $fieldName, $postID = false)
{
    $fieldObject = oes_get_field_object($fieldName, $postID);
    $fieldValue  = oes_get_field($fieldName, $postID);
    $choices     = $fieldObject['choices'] ?? [];

    // Multiple select
    if (!empty($fieldObject['multiple'])) {
        $result = [];
        foreach ((array) $fieldValue as $singleValue) {
            $label          = $choices[$singleValue] ?? $singleValue;
            $result[$singleValue] = oes_get_translated_string($label);
        }
        return $result;
    }

    // Single select
    if (is_string($fieldValue) && isset($choices[$fieldValue])) {
        return oes_get_translated_string($choices[$fieldValue]);
    }

    return '';
}

/**
 * Get value for frontend display of an ACF field.
 *
 * @param string    $fieldName The field name.
 * @param int|false $postID    An int containing the post ID.
 * @param array     $args      Further information. Valid parameters are:
 *                             'value-is-link' : bool — display value as link.
 *                             'list-id'       : The list CSS id.
 *
 * @return string The display value.
 *
 * TODO redo
 */
function oes_get_field_display_value(string $fieldName, $postID, array $args = [])
{
    $args = array_merge([
        'value-is-link' => true,
        'list-id'       => false,
        'list-class'    => false
    ], $args);

    [$postID, $fieldName] = oes_resolve_field_context($postID, $fieldName);

    if(isset($args['value'])){
        $value = $args['value'];
    }
    else {
        if (empty($postID) || empty($fieldName)) {
            $value = null;
        }
        else {
            $value = get_field($fieldName, $postID);
        }
    }

    $fieldObject = oes_get_field_object($fieldName, $postID);

    if (!isset($fieldObject['type'])) {
        return '';
    }

    if (has_filter('oes/get_field_display_value-' . $fieldName . '-args')) {
        $args = apply_filters('oes/get_field_display_value-' . $fieldName . '-args', $args, $value, $fieldObject, $postID);
    }

    if (has_filter('oes/get_field_display_value-' . $fieldName)) {
        return apply_filters('oes/get_field_display_value-' . $fieldName, $fieldObject, $value, $args, $postID);
    }

    switch ($fieldObject['type']) {

        case 'relationship':
            $newArgs          = $args;
            $newArgs['class'] = $args['list-class'];

            if (isset($fieldObject['return_format']) &&
                $fieldObject['return_format'] === 'id' &&
                is_array($value)
            ) {
                $value = array_map('get_post', $value);
            }

            return oes_display_post_array_as_list($value, $args['list-id'], $newArgs);

        case 'post_object':
            return $value
                ? oes_get_html_anchor(oes_get_display_title($value, $args), get_permalink($value))
                : '';

        case 'select':
        case 'radio':
            $selectedValue = !empty($value) ? oes_get_select_field_value($fieldName, $postID) : '';
            return is_array($selectedValue) ? implode(', ', $selectedValue) : $selectedValue;

        case 'link':
            if (!empty($value)) {
                $url = $value['url'] ?? 'Link missing';
                return $args['value-is-link']
                    ? oes_get_html_anchor(
                        empty($value['title']) ? $url : $value['title'],
                        $url,
                        false,
                        false,
                        $value['target'] ?? '_blank'
                    )
                    : $url;
            }
            return '';

        case 'url':
            return oes_get_html_anchor($value, $value, false, false, '_blank');

        case 'taxonomy':
            $tags = [];
            if ($value) {
                foreach (is_array($value) ? $value : explode(';', $value) as $tag) {
                    if ($getTerm = get_term($tag)) {
                        $tags[] = $getTerm;
                    }
                }
            }
            return oes_display_post_array_as_list(
                $tags,
                $args['list-id'],
                [
                    'class'     => $args['list-class'],
                    'permalink' => $args['value-is-link'],
                    'language'  => $args['language'] ?? ''
                ]
            );

        case 'date_picker':
        case 'date_time_picker':
            return empty($value) ? '' : oes_convert_date_to_formatted_string($value);

        case 'color_picker':
        case 'email':
        case 'number':
        case 'time_picker':
        case 'true_false':
        case 'text':
        case 'textarea':
        case 'wysiwyg':
        case 'range':
            return is_string($value) ? $value : '';

        case 'file':
            if (!empty($value['url'] ?? false)) {
                return oes_get_html_anchor(
                    empty($value['title']) ? $value['url'] : $value['title'],
                    $value['url'],
                    '',
                    '',
                    '_blank'
                );
            }
            return ''; //@oesDevelopment

        case 'button_group':
        case 'accordion':
        case 'checkbox':
        case 'google_map':
        case 'image':
        case 'tab':
            return ''; //@oesDevelopment

        case 'repeater':
            if ($value) {
                if (has_filter('oes/acf_pro_display_repeater_field')) {
                    $value = apply_filters('oes/acf_pro_display_repeater_field', $value, $fieldObject);
                } elseif (is_array($value)) {
                    $flattenValue = [];
                    if (!empty($fieldObject['sub_fields'])) {
                        foreach ($value as $singleValue) {
                            $singleValueDisplay = [];
                            foreach ($fieldObject['sub_fields'] as $subFieldObject) {
                                $loopArgs = $args;
                                if (isset($singleValue[$subFieldObject['key']])) {
                                    $loopArgs['value'] = $singleValue[$subFieldObject['key']];
                                }
                                $displayValue       = oes_get_field_display_value($subFieldObject['key'], $postID, $loopArgs);
                                $singleValueDisplay[] = (!is_string($displayValue) || empty($displayValue)) ? '-' : $displayValue;
                            }
                            $flattenValue[] = implode(', ', $singleValueDisplay);
                        }
                    }
                    $value = empty($flattenValue)
                        ? ''
                        : '<div class="oes-field-repeater">' . implode('</div><div class="oes-field-repeater">', $flattenValue) . '</div>';
                }
            }
            return $value;

        default:
            oes_write_log('Field type not found: ' . $fieldObject['type']);
            return '';
    }
}


/**
 * Get value, display value, and display type for an ACF field.
 *
 * @param string    $fieldName The field name.
 * @param int|false $postID    The post ID.
 * @param array     $args      Further information.
 *
 * @return array Returns an array containing 'value', 'value-display', 'display'.
 */
function oes_get_field_display_value_array(string $fieldName, $postID, array $args = []): array
{
    $args = array_merge(['value-is-link' => true, 'list-id' => false], $args);

    $displayType = match (oes_get_field_object($fieldName, $postID)['type']) {
        'date_picker' => 'date',
        'relationship' => 'list',
        'select', 'radio', 'link', 'url', 'taxonomy' => 'select',
        default => 'simple',
    };

    return [
        'value'         => oes_get_field($fieldName, $postID),
        'value-display' => oes_get_field_display_value($fieldName, $postID, $args),
        'display'       => $displayType
    ];
}

/**
 * Get all text fields connected to a post type or taxonomy.
 *
 * @param string $objectKey The post type key or taxonomy.
 *
 * @return array
 */
function oes_get_all_text_object_fields(string $objectKey): array
{
    return oes_get_all_object_fields($objectKey, ['text', 'textarea', 'wysiwyg', 'url'], true);
}


/**
 * Get all fields connected to a post type or taxonomy, optionally filtered by field type.
 *
 * @param string   $objectKey  The post type key or taxonomy.
 * @param string[] $fieldTypes The field types to include. Empty means all.
 * @param bool     $skipTabs   Skip tab fields. Default is false.
 *
 * @return array Returns an array of field objects keyed by field key.
 */
function oes_get_all_object_fields(string $objectKey, array $fieldTypes = [], bool $skipTabs = false): array
{
    $objectFields = [];

    $args = [];
    if (post_type_exists($objectKey)) {
        $args['post_type'] = $objectKey;
    }
    elseif (taxonomy_exists($objectKey)) {
        $args['taxonomy'] = $objectKey;
    }

    foreach (acf_get_field_groups($args) as $acfGroup) {
        foreach (acf_get_fields($acfGroup['key']) as $field) {
            if ($field['type'] === 'message') continue;
            if ($field['type'] === 'tab' && $skipTabs) continue;
            if (!empty($fieldTypes) && !in_array($field['type'], $fieldTypes)) continue;

            $objectFields[$field['key']] = $field;
        }
    }

    return $objectFields;
}

//TODO
function oes_get_all_object_fields_from_global(string $objectKey, array $fieldTypes = [], bool $skipTabs = false): array
{
    global $oes;

    $fields = $oes->post_types[$objectKey]['field_options'] ?? ($oes->taxonomies[$objectKey]['field_options'] ?? []);

    if(empty($fields)){
        return oes_get_all_object_fields($objectKey, $fieldTypes, $skipTabs);
    }

    if(empty($fieldTypes) && !$skipTabs){
        return $fields;
    }

    $collectFields = [];
    foreach($fields as $fieldKey => $field){

        if (!empty($fieldTypes) && !in_array($field['type'], $fieldTypes)) {
            continue;
        }

        $collectFields[$fieldKey] = $field;
    }

    return $collectFields;
}

/**
 * Get object select options, including field options, connected taxonomies, parent fields, etc.
 *
 * @param string $object      The object key, post type key or taxonomy key.
 * @param bool   $isPostType  Indicating if object is post type. Default is true.
 * @param array  $args        Filter which sections to include. Valid keys:
 *                            'title', 'fields', 'taxonomies', 'parent', 'parent-taxonomies'.
 * @param array  $fieldTypes  Limit to specific field types.
 *
 * @return array Return select options.
 */
function oes_get_object_select_options(
    string $object = '',
    bool   $isPostType = true,
    array  $args = [],
    array  $fieldTypes = []
): array {
    if (empty($object)) return [];

    $oes        = OES();
    $objectData = $isPostType ? ($oes->post_types[$object] ?? []) : ($oes->taxonomies[$object] ?? []);
    if (empty($objectData)) return [];

    $allFields = oes_get_all_object_fields($object, $fieldTypes, true);
    $selects   = [];

    // Reusable helper: sort an options array and store it in $selects under $key.
    $addSection = static function (string $key, array $options) use (&$selects): void {
        asort($options);
        $selects[$key] = $options;
    };

    // Title options
    if (!$args || ($args['title'] ?? false)) {
        $titleOptions = [
            'wp-title' => $isPostType ? __('Post Title (WordPress)', 'oes') : __('Name (WordPress)', 'oes')
        ];
        foreach ($allFields as $fieldKey => $singleField) {
            if (in_array($singleField['type'], ['text', 'textarea', 'wysiwyg', 'date_picker'])) {
                $titleOptions[$fieldKey] = empty($singleField['label']) ? $fieldKey : $singleField['label'];
            }
        }
        $addSection('title', $titleOptions);
    }

    // Field options (and relationship-derived post type options)
    $fieldOptions                  = [];
    $postTypesRelationshipsOptions = [];

    $excludedTypes = ['tab', 'message', 'accordion', 'clone', 'group', 'flexible_content'];

    if ((!$args || ($args['fields'] ?? false)) && !empty($objectData['field_options'])) {
        foreach ($objectData['field_options'] as $fieldKey => $field) {
            if (!isset($field['type'])) continue;
            if (in_array($field['type'], $excludedTypes)) continue;
            if (!empty($fieldTypes) && !in_array($field['type'], $fieldTypes)) continue;

            $fieldOptions[$fieldKey] = empty($field['label']) ? $fieldKey : $field['label'];

            if ($isPostType && in_array($field['type'], ['relationship', 'post_object'])) {
                $checkForPostTypes = get_field_object($fieldKey)['post_type'] ?? [];
                if (is_string($checkForPostTypes)) $checkForPostTypes = [$checkForPostTypes];
                foreach ($checkForPostTypes as $singlePostType) {
                    $postTypesRelationshipsOptions['post_type__' . $singlePostType] =
                        __('Post Type: ', 'oes') . ($oes->post_types[$singlePostType]['label'] ?? $singlePostType);
                }
            }
        }
        $addSection('fields', $fieldOptions);
    }

    // Taxonomy options
    $taxonomyOptions = [];
    if ($isPostType && (!$args || ($args['taxonomies'] ?? false))) {
        foreach (get_post_type_object($object)->taxonomies ?? [] as $taxonomy) {
            $taxonomyOptions['taxonomy__' . $taxonomy] =
                __('Taxonomy: ', 'oes') . ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);
        }
        $addSection('taxonomies', $taxonomyOptions);
    }

    // Parent field + taxonomy options
    $parentFieldOptions   = [];
    $parentTaxonomyOptions = [];
    $excludedParentTypes  = array_merge($excludedTypes, ['repeater']);

    if ($isPostType && !empty($objectData['parent'])) {
        if (!$args || ($args['parent'] ?? false)) {
            foreach ($oes->post_types[$objectData['parent']]['field_options'] ?? [] as $parentFieldKey => $parentField) {
                if (!isset($parentField['type'])) continue;
                if (in_array($parentField['type'], $excludedParentTypes)) continue;
                if (!empty($fieldTypes) && !in_array($parentField['type'], $fieldTypes)) continue;

                $parentFieldOptions['parent__' . $parentFieldKey] =
                    __('Parent Field: ', 'oes') . (empty($parentField['label']) ? $parentFieldKey : $parentField['label']);
            }
            $addSection('parent', $parentFieldOptions);
        }

        if (!$args || ($args['parent-taxonomies'] ?? false)) {
            foreach (get_post_type_object($objectData['parent'])->taxonomies ?? [] as $taxonomy) {
                $parentTaxonomyOptions['parent_taxonomy__' . $taxonomy] =
                    __('Parent Taxonomy: ', 'oes') . ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);
            }
            $addSection('parent-taxonomies', $parentTaxonomyOptions);
        }
    }

    $selects['all'] = array_merge(
        $fieldOptions,
        $postTypesRelationshipsOptions,
        $taxonomyOptions,
        $parentFieldOptions,
        $parentTaxonomyOptions
    );

    return $selects;
}


/**
 * Resolves an ACF field key and its associated post or term ID, handling parent references.
 *
 * @param WP_Post|WP_Term|int $object   A WP_Post object, WP_Term object, or their respective IDs.
 * @param string              $fieldKey The ACF field key, possibly prefixed with 'parent__'.
 * @param bool                $isPost   Whether the object is a post. Ignored for WP_Post/WP_Term instances.
 *
 * @return array{0:int, 1:string, 2:bool} [resolved ID, resolved field key, isPost].
 *                                        Returns an empty array if resolution fails.
 */
function oes_resolve_field_context($object, string $fieldKey, bool $isPost = true): array
{
    $objectID = 0;

    if (!is_int($object) && !is_string($object)) {
        if ($object instanceof WP_Post) {
            $isPost   = true;
            $objectID = $object->ID;
        } elseif ($object instanceof WP_Term) {
            $isPost   = false;
            $objectID = $object->term_id;
        }
    } else {
        $objectID = $object;
    }

    if (!$objectID) {
        return [];
    }

    if (str_starts_with($fieldKey, 'parent__')) {
        $objectID = \OES\Versioning\get_parent_id($objectID);
        $fieldKey = substr($fieldKey, 8);
    }

    return [(int)$objectID, $fieldKey, $isPost];
}


/**
 * Resolves a taxonomy key and its associated post ID, handling parent references.
 *
 * Accepts prefixes 'parent_taxonomy__' or 'taxonomy__' and strips them to yield
 * the bare taxonomy name. Resolves parent post ID when the 'parent_taxonomy__' prefix is used.
 *
 * @param WP_Post|int $object      A WP_Post object or post ID.
 * @param string      $taxonomyKey The taxonomy key, optionally prefixed with
 *                                 'parent_taxonomy__' or 'taxonomy__'.
 *
 * @return array{0:int, 1:string} [resolved post ID, resolved taxonomy key].
 *                                Returns an empty array if resolution fails.
 */
function oes_resolve_taxonomy_context($object, string $taxonomyKey): array
{
    $objectID = 0;

    if (!is_int($object) && !is_string($object)) {
        if ($object instanceof WP_Post) {
            $objectID = $object->ID;
        }
    } else {
        $objectID = $object;
    }

    if (!$objectID) {
        return [];
    }

    // Handle 'parent_taxonomy__' prefix (17 chars) — also resolves to parent post
    if (str_starts_with($taxonomyKey, 'parent_taxonomy__')) {
        $objectID    = \OES\Versioning\get_parent_id($objectID);
        $taxonomyKey = substr($taxonomyKey, 17);
    } elseif (str_starts_with($taxonomyKey, 'taxonomy__')) {
        // Handle 'taxonomy__' prefix (10 chars)
        $taxonomyKey = substr($taxonomyKey, 10);
    }

    if (!taxonomy_exists($taxonomyKey)) $taxonomyKey = 'invalid';

    return [$objectID, $taxonomyKey];
}