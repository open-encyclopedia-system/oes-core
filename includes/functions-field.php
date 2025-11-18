<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get an ACF field value from a post or term, resolving parent references if needed.
 *
 * Supports both WP_Post and WP_Term objects (or their IDs) and handles
 * ACF-style 'parent__field_name' for parent reference resolution.
 *
 * @param string $fieldName The ACF field name or key, optionally prefixed with 'parent__'.
 * @param WP_Post|WP_Term|int $object The post or term object or ID the field is associated with.
 * @param bool $formatValue Whether to apply ACF formatting. Defaults to true.
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

    // For terms, we need to format the ID with taxonomy
    if (!$isPost) {
        $object = get_term($resolvedID);
        if ($object instanceof WP_Term) {
            $taxonomy = $object->taxonomy;
            $resolvedID = "{$taxonomy}_{$resolvedID}";
        }
    }

    return get_field($resolvedField, $resolvedID, $formatValue);
}


/**
 * Retrieve a field value from a post or taxonomy term, with fallbacks and formatting.
 *
 *
 * @param int|string $objectID Post ID or Term ID
 * @param string $fieldKey The ACF field key or special identifier (e.g. 'display-title')
 * @param array $args Additional arguments
 *
 * @return mixed The resolved value or empty string on failure.
 */
function oes_get_object_display_value($objectID, string $fieldKey, array $args = [])
{
    // Resolve object type (post vs term), and field key (handles parent__, etc.)
    [$resolvedObjectID, $resolvedFieldKey, $isPost] = oes_resolve_field_context($objectID, $fieldKey);

    // Handle Post object
    if ($isPost) {
        if ($resolvedFieldKey === 'display-title') {
            return oes_get_display_title($resolvedObjectID);
        } elseif ($resolvedFieldKey === 'wp-title') {
            return get_the_title($resolvedObjectID);
        } elseif (str_starts_with($resolvedFieldKey, 'taxonomy__') || str_starts_with($resolvedFieldKey, 'parent_taxonomy__')) {

            // Prepare data
            $isParent = str_starts_with($fieldKey, 'parent_');
            if ($isParent) {
                $resolvedObjectID = \OES\Versioning\get_parent_id($resolvedObjectID);
                $resolvedTaxonomy = substr($fieldKey, 17);
            } else {
                $resolvedTaxonomy = substr($fieldKey, 10);
            }

            // Take first term
            $terms = get_the_terms($resolvedObjectID, $resolvedTaxonomy);
            if (!empty($terms)) {
                return oes_get_display_title($terms[0]);
            }
            return '';
        } else {
            return oes_get_field_display_value($resolvedFieldKey, $resolvedObjectID, $args);
        }
    }

    // Handle Term object
    $term = get_term($resolvedObjectID);
    if ($term && !is_wp_error($term)) {
        if ($resolvedFieldKey === 'display-title') {
            return oes_get_display_title($term);
        } elseif ($resolvedFieldKey === 'wp-title') {
            return $term->name;
        } else {
            return oes_get_field_display_value($resolvedFieldKey, 'term_' . $resolvedObjectID, $args);
        }
    }

    return '';
}


/**
 * Main function to get acf field object.
 *
 * @param string $fieldName The field name or key.
 *
 * @return array|false Return field object.
 */
function oes_get_field_object(string $fieldName, $postID = false)
{
    if ($postID) return get_field_object($fieldName, $postID);
    return get_field_object($fieldName);
}


/**
 *  Get all custom field objects for a specific post_id.
 *
 * @param mixed $postID The post_id of which the value is saved against
 * @return array The fields
 */
function oes_get_field_objects($postID = false): array
{
    return get_field_objects($postID);
}


/**
 * Get select value from acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID The post ID.
 * @return mixed|string Returns the selected value.
 */
function oes_get_select_field_value(string $fieldName, $postID = false)
{
    $valueArray = oes_get_field_object($fieldName, $postID);
    $fieldValue = oes_get_field($fieldName, $postID);

    // Multiple select
    if (!empty($valueArray['multiple'])) {
        $returnValue = [];

        if (is_array($fieldValue)) {
            foreach ($fieldValue as $singleValue) {
                $returnValue[$singleValue] = $valueArray['choices'][$singleValue] ?? $singleValue;
                $returnValue[$singleValue] = oes_get_translated_string($returnValue[$singleValue]);
            }
        }

        return $returnValue;
    }

    // Single select
    if (is_string($fieldValue) && isset($valueArray['choices'][$fieldValue])) {
        return oes_get_translated_string($valueArray['choices'][$fieldValue]);
    }

    return '';
}


/**
 * Get value for frontend display of an acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'value-is-link' : A boolean identifying if value is to be displayed as link.
 *  'list-id'       : The list css id.
 * @return string Return display value.
 */
function oes_get_field_display_value(string $fieldName, $postID, array $args = [])
{

    /* merge with default parameters */
    $args = array_merge([
        'value-is-link' => true,
        'list-id' => false,
        'list-class' => false
    ], $args);

    /* get field value */
    $value = $args['value'] ?? oes_get_field($fieldName, $postID);

    /* switch field type */
    $fieldObject = oes_get_field_object($fieldName, $postID);
    if (isset($fieldObject['type'])) {


        /**
         * Filter the additional arguments.
         *
         * @param array $args Further arguments.
         * @param mixed $value The field value.
         * @param array $fieldObject The field object.
         * @param int|boolean $postID An int containing the post ID.
         */
        if (has_filter('oes/get_field_display_value-' . $fieldName . '-args'))
            $args = apply_filters('oes/get_field_display_value-' . $fieldName . '-args',
                $args,
                $value,
                $fieldObject,
                $postID);


        /**
         * Filter the display value.
         *
         * @param array $fieldObject The field object.
         * @param mixed $value The field value.
         * @param array $args Further arguments.
         * @param int|boolean $postID An int containing the post ID.
         */
        if (has_filter('oes/get_field_display_value-' . $fieldName))
            return apply_filters('oes/get_field_display_value-' . $fieldName, $fieldObject, $value, $args, $postID);
        else
            switch ($fieldObject['type']) {

                case 'relationship' :
                    $newArgs = $args;
                    $newArgs['class'] = $args['list-class'];

                    /* modify value for return format 'id' */
                    if (isset($fieldObject['return_format']) &&
                        $fieldObject['return_format'] === 'id' &&
                        is_array($value)) {
                        $replaceValue = [];
                        foreach ($value as $singleValue) $replaceValue[] = get_post($singleValue);
                        $value = $replaceValue;
                    }

                    return oes_display_post_array_as_list($value, $args['list-id'], $newArgs);

                case 'post_object' :
                    return $value ?
                        oes_get_html_anchor(oes_get_display_title($value, $args), get_permalink($value)) :
                        '';

                case 'select' :
                case 'radio' :
                    $selectedValue = !empty($value) ? oes_get_select_field_value($fieldName, $postID) : '';
                    return is_array($selectedValue) ? implode(', ', $selectedValue) : $selectedValue;

                case 'link' :
                    if (!empty($value)) {
                        $url = $value['url'] ?? 'Link missing';
                        return $args['value-is-link'] ?
                            oes_get_html_anchor(
                                empty($value['title']) ? $url : $value['title'],
                                $url,
                                false,
                                false,
                                $value['target'] ?? '_blank') :
                            $url;
                    } else return '';

                case 'url' :
                    return oes_get_html_anchor($value, $value, false, false, '_blank');

                case 'taxonomy' :

                    /* get terms */
                    $tags = [];
                    if ($value)
                        foreach (is_array($value) ? $value : explode(';', $value) as $tag)
                            if ($getTerm = get_term($tag)) $tags[] = $getTerm;

                    return oes_display_post_array_as_list(
                        $tags,
                        $args['list-id'],
                        [
                            'class' => $args['list-class'],
                            'permalink' => $args['value-is-link'],
                            'language' => $args['language'] ?? ''
                        ]);

                case 'date_picker' :
                case 'date_time_picker' :
                    return empty($value) ? '' : oes_convert_date_to_formatted_string($value);

                case 'color_picker' :
                case 'email' :
                case 'number' :
                case 'time_picker' :
                case 'true_false' :
                case 'text' :
                case 'textarea' :
                case 'wysiwyg' :
                case 'range' :
                    return is_string($value) ? $value : '';

                case 'file':
                    if (!empty($value['url'] ?? false)) {
                        return oes_get_html_anchor(
                            empty($value['title']) ? $value['url'] : $value['title'],
                            $value['url'],
                            '',
                            '',
                            '_blank');
                    }
                    return ''; //@oesDevelopment

                case 'button_group' :
                case 'accordion' :
                case 'checkbox' :
                case 'google_map' :
                case 'image' :
                case 'tab':
                    return ''; //@oesDevelopment

                case 'repeater' :

                    if ($value) {

                        /**
                         * Filters the repeater value.
                         *
                         * @param array $value The value.
                         * @param array $fieldObject The field.
                         */
                        if (has_filter('oes/acf_pro_display_repeater_field')) {
                            $value = apply_filters('oes/acf_pro_display_repeater_field', $value, $fieldObject);
                        } elseif (is_array($value)) {

                            /* flatten value */
                            $flattenValue = [];
                            if (!empty($fieldObject['sub_fields'])) {
                                foreach ($value as $singleValue) {
                                    $singleValueDisplay = [];
                                    foreach ($fieldObject['sub_fields'] as $subFieldObject) {
                                        $loopArgs = $args;
                                        if (isset($singleValue[$subFieldObject['key']]))
                                            $loopArgs['value'] = $singleValue[$subFieldObject['key']];
                                        $displayValue = oes_get_field_display_value(
                                            $subFieldObject['key'],
                                            $postID,
                                            $loopArgs);
                                        $singleValueDisplay[] = (!is_string($displayValue) || empty($displayValue)) ?
                                            '-' :
                                            $displayValue;
                                    }
                                    $flattenValue[] = implode(', ', $singleValueDisplay);
                                }
                            }
                            $value = empty($flattenValue) ? '' : implode('<br>', $flattenValue);
                        }
                    }
                    return $value;

                default :
                    oes_write_log('Field type not found: ' . $fieldObject['type']);
                    return '';
            }
    }

    return '';
}


/**
 * Get value, value for frontend display, value key and value information of an acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID The post ID.
 * @param array $args Further information. Valid parameters are:
 *  'value-is-link' : A boolean identifying if value is to be displayed as link.
 *  'list-id'       : The list css id.
 * @return array Returns an array containing 'value', 'value-display', 'value-key', 'display-information'
 */
function oes_get_field_display_value_array(string $fieldName, $postID, array $args = []): array
{
    /* merge with default parameters */
    $args = array_merge(['value-is-link' => true, 'list-id' => false], $args);

    /* switch field type  to get display type */
    switch (oes_get_field_object($fieldName, $postID)['type']) {

        case 'date_picker' :
            $displayType = 'date';
            break;

        case 'relationship' :
            $displayType = 'list';
            break;

        case 'select' :
        case 'radio' :
        case 'link' :
        case 'url' :
        case 'taxonomy' :
            $displayType = 'select';
            break;

        case 'text' :
        case 'textarea' :
        case 'wysiwyg' :
        case 'range' :
        case 'button_group' :
        case 'accordion' :
        case 'checkbox' :
        case 'color_picker' :
        case 'date_time_picker' :
        case 'email' :
        case 'file' :
        case 'google_map' :
        case 'image' :
        case 'number' :
        case 'post_object' :
        case 'time_picker' :
        case 'true_false' :
        default :
            $displayType = 'simple';
            break;
    }

    return [
        'value' => oes_get_field($fieldName, $postID),
        'value-display' => oes_get_field_display_value($fieldName, $postID, $args),
        'display' => $displayType
    ];
}


/**
 * Get all text fields connected to a post type or taxonomy.
 *
 * @param string $objectKey The post type key or taxonomies.
 * @return array
 */
function oes_get_all_text_object_fields(string $objectKey): array
{
    return oes_get_all_object_fields($objectKey, ['text', 'textarea', 'wysiwyg', 'url'], true);
}


/**
 * Get all fields connected to a post type or taxonomies. Optional filtered by field types.
 *
 * @param string $objectKey The post type key or taxonomies.
 * @param string[] $fieldTypes The considered field types.
 * @param bool $skipTabs Skip tab fields. Default is false.
 * @return array Returns an array containing the fields.
 */
function oes_get_all_object_fields(string $objectKey, array $fieldTypes = [], bool $skipTabs = false): array
{
    $objectFields = [];

    /* check if post type or taxonomy */
    $args = [];
    if (post_type_exists($objectKey)) $args['post_type'] = $objectKey;
    elseif (taxonomy_exists($objectKey)) $args['taxonomy'] = $objectKey;

    /* loop through all acf field groups connected to this post type */
    foreach (acf_get_field_groups($args) as $acfGroup) {

        /* loop through the fields of this field group */
        foreach (acf_get_fields($acfGroup['key']) as $field) {

            /* skip message fields, tab field or if field does not match the filter option */
            if ($field['type'] == 'message' ||
                ($field['type'] == 'tab' && $skipTabs) ||
                (!empty($fieldTypes) && !in_array($field['type'], $fieldTypes))
            ) continue;

            /* prepare return variable */
            $objectFields[$field['key']] = $field;
        }
    }

    return $objectFields;
}


/**
 * Get object select options, including field options, connected taxonomies, parent fields, etc..
 *
 * @param string $object The object key, post type key or taxonomy key.
 * @param bool $isPostType Indicating if object is post type. Default is true.
 * @param array $args Additional arguments to get specific options. Valid arguments are:
 *  fields              : Include field options.
 *  taxonomies          : Get connected taxonomies.
 *  parent              : Get field options from parent post type.
 *  parent-taxonomies   : Get taxonomies from parent post type.
 * @param array $fieldTypes Get only specific field types.
 *
 *
 * @return array Return select options.
 */
function oes_get_object_select_options(
    string $object = '',
    bool   $isPostType = true,
    array  $args = [],
    array  $fieldTypes = []): array
{
    if (empty($object)) return [];

    /* collect options */
    $selects = [];

    /* get post data and all fields */
    $oes = OES();
    $objectData = $isPostType ? ($oes->post_types[$object] ?? []) : ($oes->taxonomies[$object] ?? []);
    if (empty($objectData)) return [];

    $allFields = oes_get_all_object_fields($object, $fieldTypes, true);

    /* prepare title options */
    $titleOptions = [];
    if (!$args || ($args['title'] ?? false)) {
        $titleOptions['wp-title'] = $isPostType ? __('Post Title (WordPress)', 'oes') : __('Name (WordPress)', 'oes');
        foreach ($allFields as $fieldKey => $singleField)
            if (in_array($singleField['type'], ['text', 'textarea', 'wysiwyg', 'date_picker']))
                $titleOptions[$fieldKey] = empty($singleField['label']) ? $fieldKey : $singleField['label'];
        asort($titleOptions);
        $selects['title'] = $titleOptions;
    }

    /* prepare field options */
    $fieldOptions = [];
    $postTypesRelationshipsOptions = [];
    if ((!$args || ($args['fields'] ?? false)) &&
        (isset($objectData['field_options']) && !empty($objectData['field_options']))) {
        foreach ($objectData['field_options'] as $fieldKey => $field)
            if (isset($field['type']) && !in_array($field['type'], [
                    'tab',
                    'message',
                    'accordion',
                    'clone',
                    'group',
                    'flexible_content',
                    'repeater'
                ]) && (
                    empty($fieldTypes) ||
                    in_array($field['type'], $fieldTypes)
                )) {
                $fieldOptions[$fieldKey] = empty($field['label']) ? $fieldKey : $field['label'];
                if (in_array($field['type'], ['relationship', 'post_object'])) {
                    $checkForPostTypes = get_field_object($fieldKey)['post_type'] ?? [];
                    if (is_string($checkForPostTypes)) $checkForPostTypes = [$checkForPostTypes];
                    if ($isPostType && !empty($checkForPostTypes))
                        foreach ($checkForPostTypes as $singlePostType)
                            $postTypesRelationshipsOptions['post_type__' . $singlePostType] =
                                __('Post Type: ', 'oes') .
                                ($oes->post_types[$singlePostType]['label'] ?? $singlePostType);
                }
            }
        asort($fieldOptions);
        $selects['fields'] = $fieldOptions;
    }

    /* add taxonomies */
    $taxonomyOptions = [];
    if ($isPostType &&
        (!$args || ($args['taxonomies'] ?? false))) {
        foreach (get_post_type_object($object)->taxonomies ?? [] as $taxonomy)
            $taxonomyOptions['taxonomy__' . $taxonomy] = __('Taxonomy: ', 'oes') .
                ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);
        asort($taxonomyOptions);
        $selects['taxonomies'] = $taxonomyOptions;
    }

    /* add parent options */
    $parentFieldOptions = [];
    $parentTaxonomyOptions = [];
    if ($isPostType && isset($objectData['parent']) && $objectData['parent']) {

        /* add parent fields */
        if (!$args || ($args['parent'] ?? false)) {
            foreach ($oes->post_types[$objectData['parent']]['field_options'] ?? [] as $parentFieldKey => $parentField)
                if (isset($parentField['type']) && !in_array($parentField['type'], [
                        'tab',
                        'message',
                        'accordion',
                        'clone',
                        'group',
                        'flexible_content',
                        'repeater'
                    ]))
                    $parentFieldOptions['parent__' . $parentFieldKey] = __('Parent Field: ', 'oes') .
                        (empty($parentField['label']) ? $parentFieldKey : $parentField['label']);
            asort($parentFieldOptions);
            $selects['parent'] = $parentFieldOptions;
        }

        /* add parent taxonomies */
        if (!$args || ($args['parent-taxonomies'] ?? false)) {
            foreach (get_post_type_object($objectData['parent'])->taxonomies ?? [] as $taxonomy)
                $parentTaxonomyOptions['parent_taxonomy__' . $taxonomy] = __('Parent Taxonomy: ', 'oes') .
                    ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);
            asort($parentTaxonomyOptions);
            $selects['parent-taxonomies'] = $parentTaxonomyOptions;
        }
    }

    /* collect all */
    $selects['all'] = array_merge(
        $fieldOptions,
        $postTypesRelationshipsOptions,
        $taxonomyOptions,
        $parentFieldOptions,
        $parentTaxonomyOptions);

    return $selects;
}


/**
 * Resolves an ACF field key and its associated post or term ID, handling parent references.
 *
 * This utility supports ACF-style `parent__field_name` references, which indicate that
 * the field should be retrieved from a parent post. It supports resolving for both posts and terms.
 *
 * @param WP_Post|WP_Term|int $object A WP_Post object, WP_Term object, or their respective IDs.
 * @param string $fieldKey The ACF field key, possibly prefixed with 'parent__'.
 * @param bool $isPost Whether the object refers to a post (true) or a term (false).
 *                                       This is ignored if $object is an instance of WP_Post or WP_Term.
 *
 * @return array{0:int, 1:string, 2:bool} [resolved ID (post/term), resolved field key, isPost boolean].
 *                                        Returns an empty array if resolution fails.
 */
function oes_resolve_field_context($object, string $fieldKey, bool $isPost = true): array
{
    $objectID = 0;

    // Determine object ID and type if not passed as integer
    if (!is_int($object) && !is_string($object)) {
        if ($object instanceof WP_Post) {
            $isPost = true;
            $objectID = $object->ID;
        } elseif ($object instanceof WP_Term) {
            $isPost = false;
            $objectID = $object->term_id;
        }
    } else {
        $objectID = $object;
    }

    if (!$objectID) {
        return [];
    }

    // Handle 'parent__' field reference
    if (str_starts_with($fieldKey, 'parent__')) {
        $objectID = \OES\Versioning\get_parent_id($objectID);
        $fieldKey = substr($fieldKey, 8);
    }

    return [$objectID, $fieldKey, $isPost];
}


/**
 * Resolves a taxonomy key and its associated post ID, handling parent references.
 *
 * This utility supports sting `parent_taxonomy__taxonomy_name` references, which indicate that
 * the field should be retrieved from a parent post.
 *
 * @param WP_Post|int $object A WP_Post object or their respective IDs.
 * @param string $taxonomyKey The taxonomy key, possibly prefixed with 'parent_taxonomy__' or 'taxonomy__'.
 *
 * @return array{0:int, 1:string} [resolved post ID, resolved taxonomy key].
 *                                        Returns an empty array if resolution fails.
 */
function oes_resolve_taxonomy_context($object, string $taxonomyKey): array
{
    $objectID = 0;

    // Determine object ID and type if not passed as integer
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

    // Handle 'parent_' reference
    if (str_starts_with($taxonomyKey, 'parent_taxonomy__')) {
        $objectID = \OES\Versioning\get_parent_id($objectID);
        $taxonomyKey = substr($taxonomyKey, 7);
    }

    // Handle 'taxonomy__' reference
    if (str_starts_with($taxonomyKey, 'taxonomy__')) {
        $taxonomyKey = substr($taxonomyKey, 10);
    }

    if (!taxonomy_exists($taxonomyKey)) $taxonomyKey = 'invalid';

    return [$objectID, $taxonomyKey];
}
