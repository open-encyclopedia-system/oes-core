<?php

namespace OES\Admin\Columns;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_select_field_value;
use function OES\Versioning\get_parent_id;


/**
 * Add extra columns to the list display of posts in the wp admin area.
 *
 * @param array $columns The columns for the post type that is being displayed.
 * @return array Returns the columns to be displayed.
 */
function add_post_column(array $columns): array
{
    /* get global parameter for post type */
    $oes = OES();
    global $post_type, $taxonomy;

    /* prepare new columns */
    $newColumns = [];

    if (!empty(get_current_screen()->taxonomy)) {

        /* bail if no global configuration for this post type exists */
        if (!isset($oes->taxonomies[$taxonomy])) return $columns;

        /* check for other columns to be displayed */
        if (isset($oes->taxonomies[$taxonomy]['admin_columns']))
            foreach ($oes->taxonomies[$taxonomy]['admin_columns'] as $columnKey) {
                switch ($columnKey) {

                    case 'init':
                        break;

                    case 'cb' :
                    case 'name' :
                    case 'description' :
                    case 'slug' :
                    case 'posts' :
                        $newColumns[$columnKey] = $columns[$columnKey];
                        break;

                    case 'id' :
                        $newColumns[$columnKey] = __('Term ID', 'oes');
                        break;

                    default :
                        $newColumns[$columnKey] =
                            $oes->taxonomies[$taxonomy]['field_options'][$columnKey]['label'] ?? $columnKey;
                        break;
                }
            }
    } else {

        /* bail if no global configuration for this post type exists */
        if (!isset($oes->post_types[$post_type])) return $columns;

        /* check for other columns to be displayed */
        if (isset($oes->post_types[$post_type]['admin_columns']))
            foreach ($oes->post_types[$post_type]['admin_columns'] as $columnKey) {
                switch ($columnKey) {

                    case 'init':
                        break;

                    case 'cb' :
                    case 'title' :
                    case 'date' :
                        $newColumns[$columnKey] = $columns[$columnKey];
                        break;

                    case 'date_modified' :
                        $newColumns[$columnKey] = __('Last Updated', 'oes');
                        break;

                    case 'parent':
                        $newColumns[$columnKey] = __('Parent', 'oes');
                        break;

                    default :
                        /* check if taxonomy */
                        if ($taxonomy = get_taxonomy($columnKey))
                            $newColumns[$columnKey] = $oes->taxonomies[$columnKey]['label'] ?? $taxonomy->label;
                        elseif (oes_starts_with($columnKey, 'taxonomy-'))
                            $newColumns[$columnKey] = $columns[$columnKey];
                        else
                            $newColumns[$columnKey] =
                                $oes->post_types[$post_type]['field_options'][$columnKey]['label'] ?? $columnKey;
                        break;
                }
            }
    }

    return empty($newColumns) ? $columns : $newColumns;
}


/**
 * Display values for column in the list display of post types in the wp admin area.
 *
 * @param string $column The column name.
 * @param string $post_id The post ID.
 * @return void
 */
function display_post_column_value(string $column, string $post_id): void
{
    /* get column value depending on field type */
    if (!oes_starts_with($column, 'taxonomy-'))
        switch ($column) {

            case 'cb' :
            case 'title' :
            case 'date' :
                break;

            /* add last modified date */
            case 'date_modified':

                $date = get_post_datetime($post_id);
                $dateMod = get_post_datetime($post_id, 'modified');
                $timeString = sprintf("Last Updated<br>%s at %s",
                    strftime('%Y/%m/%e', $dateMod->getTimestamp()),
                    strftime('%I:%M %p', $dateMod->getTimestamp()),
                );
                echo $date->getTimestamp() == $dateMod->getTimestamp() ?
                    '<span class="oes-grey-out">' . $timeString . '</span>'
                    : $timeString;

                break;

            /* get link to parent post */
            case 'parent':

                /* get parent post id */
                $parentID = get_parent_id($post_id);

                if ($parentID) {
                    $parentPost = get_post($parentID);
                    printf('<span class="oes-column-post">' .
                        '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s</a></span>',
                        $parentID,
                        $parentPost->post_title);
                } else echo '';
                break;

            default:

                /* acf field */
                if ($value = oes_get_field($column, $post_id)) {

                    /* check type */
                    $fieldObject = oes_get_field_object($column);
                    if ($fieldObject && !empty($value)) {

                        switch ($fieldObject['type']) {

                            case 'radio' :
                            case 'select' :
                                $newValue = get_select_field_value($column, $post_id);
                                break;

                            case 'post_object':
                                $postObject = get_post($value);
                                $newValue = $postObject ?
                                    sprintf('<span class="oes-column-post">' .
                                        '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                        '</a></span>',
                                        $postObject->ID,
                                        $postObject->post_title) :
                                    $value;
                                break;

                            case 'relationship' :
                                if (is_array($value)) {
                                    $newValueTemp = [];
                                    $value = array_unique($value, SORT_REGULAR);
                                    foreach ($value as $post) {
                                        if (get_post($post)) {
                                            $postID = ($fieldObject['return_format'] == 'object') ? $post->ID : $post;
                                            $newValueTemp[] = sprintf('<span class="oes-column-post">' .
                                                '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                                '</a></span>',
                                                $postID,
                                                get_the_title($postID)
                                            );
                                        }
                                    }
                                    $newValue = implode('', $newValueTemp);
                                } else {
                                    $newValue = get_post($value) ? oes_get_display_title($value) : $value;
                                }
                                break;

                            case 'taxonomy' :
                                if (is_array($value)) {
                                    $newValueTemp = [];
                                    foreach ($value as $valueID) {
                                        $term = get_term($valueID);
                                        if ($term) $newValueTemp[] = sprintf('<span class="oes-column-taxonomy">' .
                                            '<a href="edit.php?post_type=%s&%s=%s">%s</a></span>',
                                            $_GET['post_type'] ?? '',
                                            $column,
                                            $valueID,
                                            $term->name
                                        );
                                    }
                                    $newValue = implode(', ', $newValueTemp);
                                } else {
                                    $newValue = get_term($value)->name ?? $value;
                                }
                                break;
                        }
                    }

                    echo $newValue ?? $value;
                } /* taxonomy */
                elseif (get_taxonomy($column)) {

                    $terms = get_the_terms($post_id, $column);

                    $termStringArray = [];
                    if ($terms) {
                        foreach ($terms as $term)
                            $termStringArray[] = sprintf(
                                '<span class="oes-column-tag"><a href="edit.php?post_type=%s&%s=%s">%s</a></span>',
                                $_GET['post_type'] ?? '',
                                $column,
                                $term->slug,
                                $term->name);
                    }
                    if (!empty($termStringArray)) echo implode('', $termStringArray);
                }
                break;
        }
}


/**
 * Display values for column in the list display of taxonomy in the wp admin area.
 *
 * @param string $ignore Custom column output.
 * @param string $column The column name.
 * @param int $term_id The term id.
 * @return void
 */
function display_taxonomy_column_value(string $ignore, string $column, int $term_id): void
{
    /* get column value depending on field type */
    switch ($column) {

        case 'cb' :
        case 'name' :
        case 'description' :
        case 'slug' :
        case 'posts' :
            break;

        case 'id' :
            echo $term_id;
            break;

        default:

            global $taxonomy;

            /* acf field */
            if ($value = oes_get_field($column, $taxonomy . '_' . $term_id)) {

                /* check type */
                $fieldObject = oes_get_field_object($column);
                if ($fieldObject && !empty($value)) {

                    switch ($fieldObject['type']) {

                        case 'radio' :
                        case 'select' :
                            $newValue = get_select_field_value($column, $term_id);
                            break;

                        case 'post_object':
                            $postObject = get_post($value);
                            $newValue = $postObject ?
                                sprintf('<span class="oes-column-post">' .
                                    '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                    '</a></span>',
                                    $postObject->ID,
                                    $postObject->post_title) :
                                $value;
                            break;

                        case 'relationship' :
                            if (is_array($value)) {
                                $newValueTemp = [];
                                foreach ($value as $post) {
                                    if (get_post($post)) {
                                        $postID = ($fieldObject['return_format'] == 'object') ? $post->ID : $post;
                                        $newValueTemp[] = sprintf('<span class="oes-column-post">' .
                                            '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                            '</a></span>',
                                            $postID,
                                            get_the_title($postID)
                                        );
                                    }
                                }
                                $newValue = implode('', $newValueTemp);
                            } else {
                                $newValue = get_post($value) ? oes_get_display_title($value) : $value;
                            }
                            break;

                        case 'taxonomy' :
                            if (is_array($value)) {
                                $newValueTemp = [];
                                foreach ($value as $valueID) {
                                    $term = get_term($valueID);
                                    if ($term) $newValueTemp[] = sprintf('<span class="oes-column-taxonomy">' .
                                        '<a href="edit.php?post_type=%s&%s=%s">%s</a></span>',
                                        $_GET['post_type'] ?? '',
                                        $column,
                                        $valueID,
                                        $term->name
                                    );
                                }
                                $newValue = implode(', ', $newValueTemp);
                            } else {
                                $newValue = get_term($value)->name ?? $value;
                            }
                            break;
                    }
                }

                echo $newValue ?? $value;
            }
            break;
    }
}


/**
 * Make columns sortable in the list display of post types in the wp admin area.
 *
 * @param array $columns The columns which are to be displayed.
 * @return array Return columns.
 */
function make_columns_sortable(array $columns): array
{
    /* get global parameter for post type */
    global $post_type, $taxonomy;
    $oes = OES();

    if (!empty(get_current_screen()->taxonomy)) {

        /* bail if no configuration for this post type exists */
        if (!isset($oes->taxonomies[$taxonomy])) return $columns;

        /* check for other columns to be displayed */
        if (isset($oes->taxonomies[$taxonomy]['admin_columns']))
            foreach ($oes->taxonomies[$taxonomy]['admin_columns'] as $fieldKey) {

                /* skip init */
                if ($fieldKey == 'init') continue;

                /* check if acf field */
                if ($fieldObject = oes_get_field_object($fieldKey))
                    switch ($fieldObject['type']) {
                        case 'taxonomy' :
                        case 'relationship' :
                            break;
                        default:
                            $columns[$fieldKey] = $fieldKey;
                            break;
                    }
                else $columns[$fieldKey] = $fieldKey;
            }
    }
    else{

        /* bail if no configuration for this post type exists */
        if (!isset($oes->post_types[$post_type])) return $columns;

        /* check for other columns to be displayed */
        if (isset($oes->post_types[$post_type]['admin_columns']))
            foreach ($oes->post_types[$post_type]['admin_columns'] as $fieldKey) {

                /* skip init */
                if ($fieldKey == 'init') continue;

                if ($fieldKey == 'date_modified') $columns[$fieldKey] = 'modified';

                /* check if acf field */
                if ($fieldObject = oes_get_field_object($fieldKey))
                    switch ($fieldObject['type']) {
                        case 'taxonomy' :
                        case 'relationship' :
                            break;
                        default:
                            $columns[$fieldKey] = $fieldKey;
                            break;
                    }
                else $columns[$fieldKey] = $fieldKey;
            }
    }

    return $columns;
}
