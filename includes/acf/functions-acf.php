<?php

namespace OES\ACF;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Main function to get acf field values.
 *
 * @param string $fieldName The field name or key
 * @param mixed $postID The post_id of which the value is saved against
 *
 * @return mixed Return field value.
 */
function oes_get_field(string $fieldName, $postID = false)
{
    return get_field($fieldName, $postID);
}


/**
 * Main function to get acf field object.
 *
 * @param string $fieldName The field name or key.
 *
 * @return mixed Return field object.
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
 * Get all fields connected to a post type or taxonomies. Optional filtered by field types.
 *
 * @param string $postType The post type key or taxonomies.
 * @param string[]|bool $fieldTypes The considered field types.
 * @param bool $skipTabs Skip tab fields.
 * @return array Returns an array containing the fields.
 */
function get_all_object_fields(string $postType, $fieldTypes = ['text', 'textarea', 'wysiwyg', 'url'], bool $skipTabs = true): array
{
    $postTypeFields = [];

    /* loop through all acf field groups connected to this post type */

    /* check if post type or taxonomy */
    $args = [];
    if (post_type_exists($postType)) $args['post_type'] = $postType;
    elseif (taxonomy_exists($postType)) $args['taxonomy'] = $postType;

    foreach (acf_get_field_groups($args) as $acfGroup) {

        /* loop through the fields of this field group */
        foreach (acf_get_fields($acfGroup['key']) as $field) {

            /* skip message fields */
            if ($field['type'] == 'message') continue;

            /* skip tab fields */
            if ($field['type'] == 'tab' && $skipTabs) continue;

            /* skip field if it does not match filter option */
            if ($fieldTypes && !in_array($field['type'], $fieldTypes)) continue;

            /* prepare return variable */
            $postTypeFields[$field['key']] = $field;
        }
    }

    return $postTypeFields;
}


/**
 * Get select value from acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID The post ID.
 * @return mixed|string Returns the selected value.
 */
function get_select_field_value(string $fieldName, $postID = false)
{
    /* get acf field value and label */
    $valueArray = oes_get_field_object($fieldName, $postID);

    /* check if multiple value */
    if (isset($valueArray['multiple']) && $valueArray['multiple']) {
        $returnValue = [];

        /* loop through values */
        foreach (oes_get_field($fieldName, $postID) as $singleValue) {
            $returnValue[$singleValue] = $valueArray['choices'][$singleValue];
        }
    } /* single value */
    else {
        $returnValue =
            (oes_get_field($fieldName, $postID) &&
                is_string(oes_get_field($fieldName, $postID)) &&
                isset($valueArray['choices'][oes_get_field($fieldName, $postID)])) ?
                $valueArray['choices'][oes_get_field($fieldName, $postID)] :
                '';
    }

    return $returnValue;
}


/**
 * Get value for frontend display of an acf field.
 *
 * @param string $fieldName The field name.
 * @param int|boolean $postID An int containing the post ID.
 * @param array $args An array containing further information. Valid parameters are:
 *  'value-is-link' : A boolean identifying if value is to be displayed as link.
 *  'list-id'       : The list css id.
 * @return array|false|string
 */
function get_field_display_value(string $fieldName, $postID, array $args = [])
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
    if (isset($fieldObject['type']))
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
                return $value ? oes_get_html_anchor(oes_get_display_title($value, $args), get_permalink($value)) : false;

            case 'select' :
            case 'radio' :

                /* get selected value(s) */
                $selectedValue = !empty($value) ? get_select_field_value($fieldName, $postID) : '';

                /* multiple values */
                return is_array($selectedValue) ? implode(', ', $selectedValue) : $selectedValue;

            case 'link' :

                if (!empty($value)) {
                    $value = array_merge(['target' => '_blank', 'url' => 'Link missing'], $value);

                    $url = $value['url'] ?? 'Link missing';
                    return $args['value-is-link'] ?
                        oes_get_html_anchor(empty($value['title']) ? $url : $value['title'], $url, false, false, $value['target']) :
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

                return oes_display_post_array_as_list($tags, $args['list-id'],
                    ['class' => $args['list-class'], 'permalink' => $args['value-is-link'], 'language' => $args['language'] ?? 'language0']);

            case 'date_picker' :
            case 'date_time_picker' :
                return (isset($fieldObject['display_format']) &&
                    !empty($fieldObject['display_format']) &&
                    strtotime(str_replace('/', '-', $value))) ?
                    date_i18n($fieldObject['display_format'], strtotime(str_replace('/', '-', $value))) :
                    $value;

            case 'range' :
            case 'button_group' :
            case 'accordion' :
            case 'checkbox' :
            case 'color_picker' :
            case 'email' :
            case 'file' :
            case 'google_map' :
            case 'image' :
            case 'number' :
            case 'time_picker' :
            case 'true_false' :
            case 'text' :
            case 'textarea' :
            case 'wysiwyg' :
                return $value;

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
                                    if (isset($singleValue[$subFieldObject['key']])) $loopArgs['value'] = $singleValue[$subFieldObject['key']];
                                    $displayValue = get_field_display_value($subFieldObject['key'], $postID, $loopArgs);
                                    $singleValueDisplay[] = (!is_string($displayValue) || empty($displayValue)) ? '-' : $displayValue;
                                }
                                $flattenValue[] = implode(', ', $singleValueDisplay);
                            }
                        }
                        $value = empty($flattenValue) ? '' : implode('<br>', $flattenValue);
                    }
                }
                return $value;

            default :
                return 'Field type not found: ' . $fieldObject['type'];
        }

    return null;
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
function get_field_display_value_array(string $fieldName, $postID, array $args = []): array
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
        'value-display' => get_field_display_value($fieldName, $postID, $args),
        'display' => $displayType
    ];
}


/**
 * Get the global stored post id from the acf cache or global post variable.
 *
 * @return int Return post id.
 */
function acf_post_id(): int
{
    if (is_admin() && function_exists('acf_maybe_get_POST')) {
        return intval(acf_maybe_get_POST('post_id'));
    } else {
        global $post;
        return $post->ID;
    }
}