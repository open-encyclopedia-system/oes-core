<?php

/**
 * Formulas can be used to transform data according to a mapping pattern.
 * @oesDevelopment Make formula available through shortcode.
 * @oesDevelopment Add 'overwrite' option.
 */

namespace OES\Formula;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;


/**
 * Calculate post arguments from formula (pattern).
 *
 * @param mixed $postID The considered post ID.
 * @return void
 */
function calculate_post_args_from_formula($postID): void
{

    /* check for post type */
    $currentPostType = get_post_type($postID);
    $oes = OES();
    $args = [];

    /* set new title */
    if ($patternTitle = ($oes->post_types[$currentPostType]['pattern_title'] ?? []))
        if ($newTitle = calculate_post_title_from_formula($patternTitle, $postID))
            if (!empty($newTitle)) $args['post_title'] = $newTitle;

    /* set new name (slug) */
    if ($patternName = ($oes->post_types[$currentPostType]['pattern_name'] ?? []))
        if ($newName = calculate_post_name_from_formula($patternName, $postID))
            if (!empty($newTitle)) $args['post_title'] = $newName;

    /* get new args and update */
    if ($args) wp_update_post(array_merge(['ID' => $postID], $args));
}


/**
 * Calculate post title from formula (pattern).
 *
 * @param array $pattern The title pattern.
 * @param mixed $postID The considered post ID.
 * @param bool $overwrite Indicating if title is to be overwritten even if title is not empty.
 * @param string $separator The separator between parts.
 * @return string Return the calculated post title or empty.
 */
function calculate_post_title_from_formula(
    array  $pattern,
           $postID,
    bool   $overwrite = false,
    string $separator = ''): string
{
    if ($parts = $pattern['parts'] ?? false) {

        /* check if update required, then set new argument */
        $currentTitle = get_the_title($postID);
        if (empty($currentTitle) || $overwrite) {
            $newTitle = calculate_value($parts, $postID, $separator);
            if (!empty($newTitle) && $newTitle != $currentTitle) return $newTitle;
        }
    }
    return '';
}


/**
 * Calculate post name from formula (pattern).
 *
 * @param array $pattern The name (slug) pattern.
 * @param mixed $postID The considered post ID.
 * @param bool $overwrite Indicating if title is to be overwritten even if title is not empty.
 * @param string $separator The separator between parts.
 * @return string Return the calculated post name or empty.
 */
function calculate_post_name_from_formula(
    array  $pattern,
           $postID,
    bool   $overwrite = false,
    string $separator = ''): string
{
    if ($parts = $pattern['parts'] ?? false) {

        /* check if update required, then set new argument */
        $currentName = get_post($postID)->post_name;
        if (empty($currentName) || $overwrite) {
            $newName = calculate_value($parts, $postID, $separator);
            if ($newName != $currentName) return $newName;
        }
    }
    return '';
}


/**
 * Calculate field value from formula (pattern).
 *
 * @param mixed $value The field value.
 * @param int|string $postID The post ID where the value is saved.
 * @param array $field The field array containing all settings.
 * @param bool $overwrite Indicating if title is to be overwritten even if title is not empty.
 * @return mixed
 */
function calculate_field_value_from_formula($value, $postID, array $field, bool $overwrite = false)
{

    /* return early if post not yet created */
    if (!$postID) return $value;

    /* check for new value */
    if ($pattern = (OES()->post_types[get_post_type($postID)]['field_options'][$field['key']]['pattern'] ?? []))
        if ($overwrite || (empty($value) || $value !== 'generate'))
            if ($newValue = calculate_value($pattern, $postID))
                if ($newValue !== $value) $value = $newValue;

    return $value;
}


/**
 * Create new string from array parameters.
 *
 * @param array $parts The parts containing field key and additional information.
 * @param int $postID The post ID.
 * @param string $separator The separator between parts.
 * @return string Return the parts as string.
 *
 * @oesDevelopment More options, add parent fields, make link optional, return $url instead of anchor...
 */
function calculate_value(array $parts, int $postID, string $separator = '', bool $sort = false): string
{
    /* get parts */
    $stringParts = [];
    foreach ($parts as $part) {

        /* @oesLegacy  rename pattern parameters */
        if (isset($part['default']) && !isset($part['string_value'])) $part['string_value'] = $part['default'];
        if (isset($part['key']) && !isset($part['field_key'])) $part['field_key'] = $part['key'];
        if (isset($part['fallback_field_key']) && !isset($part['fallback']))
            $part['fallback'] = $part['fallback_field_key'];

        /* check for prefix or suffix */
        $prefix = $part['prefix'] ?? '';
        $suffix = $part['suffix'] ?? '';

        /* check if acf field */
        $fieldKey = $part['field_key'] ?? false;
        if ($fieldKey) {

            $consideredID = $postID;
            if (str_starts_with($fieldKey, 'parent__')) {
                $fieldKey = substr($fieldKey, 8);
                $consideredID = get_parent_id($postID);
            }


            /**
             * Filters the considered ID.
             *
             * @param mixed $consideredID The considered ID.
             * @param string $fieldKey The field key.
             * @param string|int $postID The post ID.
             * @param array $part The part data.
             */
            $consideredID = apply_filters('oes/calculate_value_part_considered_id',
                $consideredID,
                $part['field_key'] ?? $fieldKey,
                $postID,
                $part);


            /* get field value and parameters */
            $fieldObject = oes_get_field_object($fieldKey);
            $fieldValue = oes_get_field($fieldKey, $consideredID);
            $value = '';

            /* check if value is empty and fallback field is set */
            if ($fieldKey == 'none' || $fieldKey == 'no_field_key') $value = $part['string_value'] ?? '';
            elseif (empty($fieldValue))
                if (isset($part['fallback']))
                    if ($fallbackFieldKey = $part['fallback']) {

                        if (str_starts_with($fallbackFieldKey, 'parent__')) {
                            $fallbackFieldKey = substr($fieldKey, 7);
                            $consideredID = get_parent_id($consideredID);
                        }

                        $fieldValue = oes_get_field($fallbackFieldKey, $consideredID);
                        $fieldObject = oes_get_field_object($fallbackFieldKey);
                    }

            /* get value according to field type */
            if (isset($fieldObject['type'])) {
                switch ($fieldObject['type']) {

                    case 'text':
                    case 'textarea':
                        $value = $fieldValue;
                        break;

                    case 'date_picker':
                        $value = empty($fieldValue) ? '' : oes_convert_date_to_formatted_string($fieldValue);
                        break;

                    case 'relationship' :
                    case 'post_object' :
                        $value = oes_get_field_display_value($fieldKey, $consideredID, [
                            'sort' => $sort,
                            'separator' => (empty($part['separator']) ? ', ' : $part['separator'])
                        ]);
                        break;

                    case 'select' :
                        $value = oes_get_field_display_value($fieldKey, $consideredID);
                        break;

                    case 'link' :
                        if ($fieldValue) {
                            $url = $fieldValue['url'] ?? 'Link missing';
                            $title = $fieldValue['title'] ?? $url;
                            $target = isset($fieldValue['target']);
                            $value = oes_get_html_anchor(
                                $title,
                                $url,
                                false,
                                false,
                                $target ? '_blank' : false);
                        } else $value = false;
                        break;

                    case 'url' :
                        $value = $fieldValue ?
                            oes_get_html_anchor(
                                $fieldValue,
                                $fieldValue,
                                false,
                                false,
                                '_blank') :
                            false;
                        break;

                    case 'taxonomy' :

                        /* get terms */
                        $tags = [];
                        if ($fieldValue) {
                            foreach (is_array($fieldValue) ? $fieldValue : [$fieldValue] as $tag)
                                if ($getTerm = get_term($tag)) $tags[] = $getTerm;
                            $value = oes_display_post_array_as_list($tags);
                        }
                        break;

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
                    case 'radio' :
                    case 'time_picker' :
                    case 'true_false' :
                    case 'wysiwyg' :
                        $value = $fieldValue ?? false;
                        break;

                    default:
                        continue 2;
                }
            }

            /* prepare pattern field value */
            if (isset($value) && !empty($value))
                $stringParts[] = $prefix . $value . $suffix;
            elseif ($part['required'] ?? false)
                $stringParts[] = $part['string_value'] ?? 'Value Missing (' . $fieldKey . ')';
        } elseif ($string = $part['string_value'] ?? false) {
            $stringParts[] = $prefix . $string . $suffix;
        }
    }

    return empty($stringParts) ? '' : implode($separator ?? '', $stringParts);
}