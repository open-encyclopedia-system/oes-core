<?php

namespace OES;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_field_display_value;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\Versioning\get_parent_id;


/**
 * Create new string from array parameters.
 *
 * @param array $dtmParts The parts containing field key and additional information.
 * @param int $postID The post ID.
 * @param string $separator The separator between parts.
 * @return string Return the parts as string.
 *
 * @oesDevelopment More options, add parent fields, make link optional, return $url instead of anchor...
 */
function get_post_dtm_parts_from_array(array $dtmParts, int $postID, string $separator = '', bool $sort = false): string
{
    /* get parts */
    $stringParts = [];
    foreach ($dtmParts as $part) {

        /* check for prefix or suffix */
        $prefix = $part['prefix'] ?? '';
        $suffix = $part['suffix'] ?? '';

        /* check if acf field */
        $fieldKey = $part['key'] ?? false;
        if ($fieldKey) {

            if(oes_starts_with($fieldKey, 'parent__')){
                $fieldKey = substr($fieldKey, 8);
                $postID = get_parent_id($postID);
            }

            /* get field value and parameters */
            $fieldObject = oes_get_field_object($fieldKey);
            $acfValue = oes_get_field($fieldKey, $postID);
            $value = '';

            /* check if value is empty and fallback field is set */
            if ($fieldKey == 'no_field_key') $value = $part['default'];
            elseif (empty($acfValue))
                if (isset($part['fallback_field_key']))
                    if ($fallbackFieldKey = $part['fallback_field_key']) {

                        if(oes_starts_with($fallbackFieldKey, 'parent__')){
                            $fallbackFieldKey = substr($fieldKey, 7);
                            $postID = get_parent_id($postID);
                        }

                        $acfValue = oes_get_field($fallbackFieldKey, $postID);
                        $fieldObject = oes_get_field_object($fallbackFieldKey);
                    }

            /* get value according to field type */
            if (isset($fieldObject['type'])) {
                switch ($fieldObject['type']) {

                    case 'text':
                    case 'textarea':
                        $value = $acfValue;
                        break;

                    case 'date_picker':
                        $format = (empty($part['date_format']) ? 'j F Y' : $part['date_format']);
                        $value = $acfValue ?
                            date($format, strtotime(str_replace('/', '-', $acfValue))) : false;
                        break;

                    case 'relationship' :
                    case 'post_object' :
                        $value = get_field_display_value($fieldKey, $postID, [
                            'sort' => $sort,
                            'separator' => (empty($part['separator']) ? ', ' : $part['separator'])
                        ]);
                        break;

                    case 'select' :
                        $value = get_field_display_value($fieldKey, $postID);
                        break;

                    case 'link' :
                        if ($acfValue) {
                            $url = $acfValue['url'] ?? 'Link missing';
                            $title = $acfValue['title'] ?? $url;
                            $target = isset($acfValue['target']);
                            $value = oes_get_html_anchor(
                                $title,
                                $url,
                                false,
                                false,
                                $target ? '_blank' : false);
                        } else $value = false;
                        break;

                    case 'url' :
                        $value = $acfValue ?
                            oes_get_html_anchor(
                                $acfValue,
                                $acfValue,
                                false,
                                false,
                                '_blank') :
                            false;
                        break;

                    case 'taxonomy' :

                        /* get terms */
                        $tags = [];
                        if ($acfValue) {
                            foreach (is_array($acfValue) ? $acfValue : [$acfValue] as $tag)
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
                        $value = $acfValue ?? false;
                        break;

                    default:
                        continue 2;
                }
            }

            /* prepare pattern field value */
            if (isset($value) && !empty($value))
                $stringParts[] = $prefix . $value . $suffix;
            elseif ($part['required'] ?? false)
                $stringParts[] = $part['default'] ?? 'Value Missing (' . $fieldKey . ')';
        } elseif ($string = $part['string'] ?? false) {
            $stringParts[] = $prefix . $string . $suffix;
        }
    }

    return empty($stringParts) ? '' : implode($separator ?? '', $stringParts);
}


/**
 * Get fields for editorial tab.
 *
 * @return array Return editorial tab fields
 */
function get_editorial_tab(): array
{
    $oes = OES();
    $editorialTab = [
        [
            'name' => 'field_oes_tab_editorial',
            'label' => $oes->other_fields['field_oes_tab_editorial']['label'] ?? 'Editorial',
            'type' => 'tab',
            'key' => 'field_oes_tab_editorial',
            'placement' => 'left'
        ],
        [
            'name' => 'field_oes_status',
            'label' => $oes->other_fields['field_oes_status']['label'] ?? 'Status',
            'instructions' => $oes->other_fields['field_oes_status']['instructions'] ?? '',
            'type' => 'select',
            'key' => 'field_oes_status',
            'choices' => [
                'new' => 'New',
                'progress' => 'In Progress',
                'ready' => 'Ready for Publication',
                'published' => 'Published',
                'deleted' => 'To be Deleted',
                'admin_mode' => 'Admin Mode'
            ],
            'default_value' => 'new'
        ],
        [
            'name' => 'field_oes_comment',
            'label' => $oes->other_fields['field_oes_comment']['label'] ?? 'Comment',
            'instructions' => $oes->other_fields['field_oes_comment']['instructions'] ?? '',
            'type' => 'textarea',
            'key' => 'field_oes_comment'
        ]
    ];


    /**
     * Filters the fields for the editorial tab.
     *
     * @param array $editorialTab The fields for the editorial tab.
     */
    if (has_filter('oes/get_editorial_tab'))
        $editorialTab = apply_filters('oes/get_editorial_tab', $editorialTab);

    return $editorialTab;
}


/**
 * Get fields for versioning tab (for parent post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_parent(): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'current_version' => [
            'name' => 'field_oes_versioning_current_post',
            'label' => 'Current Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_current_post',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'versions' => [
            'name' => 'field_oes_versioning_posts',
            'label' => 'Versions',
            'type' => 'relationship',
            'key' => 'field_oes_versioning_posts',
            'filters' => ['search'],
            'post_type' => [],
            'return_format' => 'id',
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ],
        'connected_parents' => [
            'name' => 'field_connected_parent',
            'label' => 'Connected Parent',
            'type' => 'post_object',
            'key' => 'field_connected_parent',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}


/**
 * Get fields for versioning tab (for version post type).
 *
 * @return array Return versioning tab fields
 */
function get_versioning_tab_version(): array
{
    return [
        'oes_versioning_tab' => [
            'name' => 'oes_versioning_tab',
            'label' => 'Version Control',
            'type' => 'tab',
            'key' => 'oes_versioning_tab'
        ],
        'parent_post' => [
            'name' => 'field_oes_versioning_parent_post',
            'label' => 'Parent Post',
            'type' => 'post_object',
            'key' => 'field_oes_versioning_parent_post',
            'post_type' => [],
            'return_format' => 'id',
            'allow_null' => true,
            'wrapper' => [
                'class' => 'oes-acf-hidden-field'
            ]
        ]
    ];
}