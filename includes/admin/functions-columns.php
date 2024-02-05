<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Query;
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
                    date_i18n('Y/m/d', $dateMod->getTimestamp()),
                    date_i18n('G:i', $dateMod->getTimestamp()),
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
                        '<a href="%s" class="oes-admin-link">%s</a></span>',
                        admin_url('post.php?post=' . $parentID . '&action=edit'),
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
                                $newValue = oes_get_select_field_value($column, $post_id);
                                if (is_array($newValue)) $newValue = implode(', ', $newValue);
                                break;

                            case 'post_object':
                                $postObject = get_post($value);
                                $newValue = $postObject ?
                                    sprintf('<span class="oes-column-post">' .
                                        '<a href="%s" class="oes-admin-link">%s</a></span>',
                                        admin_url('post.php?post=' . $postObject->ID . '&action=edit'),
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
                                                '<a href="%s" class="oes-admin-link">%s</a></span>',
                                                admin_url('post.php?post=' . $postID . '&action=edit'),
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
                                            '<a href="%s">%s</a></span>',
                                            admin_url('edit.php?post_type=' . ($_GET['post_type'] ?? '') . '&' .
                                                $column  . '=' . $valueID),
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
                                '<span class="oes-column-tag"><a href="%s">%s</a></span>',
                                admin_url('edit.php?post_type=' . ($_GET['post_type'] ?? '') . '&' .
                                    $column  . '=' . $term->slug),
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
                            $newValue = oes_get_select_field_value($column, $term_id);
                            if (is_array($newValue)) $newValue = implode(', ', $newValue);
                            break;

                        case 'post_object':
                            $postObject = get_post($value);
                            $newValue = $postObject ?
                                sprintf('<span class="oes-column-post">' .
                                    '<a href="%s" class="oes-admin-link">%s</a></span>',
                                    admin_url('post.php?post=' . $postObject->ID . '&action=edit'),
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
                                            '<a href="%s" class="oes-admin-link">%s</a></span>',
                                            admin_url('post.php?post=' . $postID . '&action=edit'),
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
                                        '<a href="%s">%s</a></span>',
                                        admin_url('edit.php?post_type=' . ($_GET['post_type'] ?? '') . '&' .
                                            $column  . '=' . $valueID),
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
    } else {

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


/**
 * Add extra filter dropdown box to the list tables.
 *
 * @param string $post_type The post type that is being displayed.
 * @return void
 *
 */
function columns_filter_dropdown(string $post_type): void
{
    /* get global parameter for post type */
    $oes = OES();

    /* bail early if not part of wp admin or no configuration for this post type exists*/
    if (!is_admin() || !isset($oes->post_types[$post_type])) return;

    /* add filter for each column for post type */
    if (isset($oes->post_types[$post_type]['admin_columns'])) {
        foreach ($oes->post_types[$post_type]['admin_columns'] as $columnKey) {

            /* skip if filter option for 'cb' and 'title' */
            if (in_array($columnKey, ['cb', 'title', 'init', 'date', 'date_modified', 'parent'])) continue;

            /* get field object */
            $fieldObject = $oes->post_types[$post_type]['field_options'][$columnKey] ?? false;

            /* check for selected value, default is '-1' */
            $selectedName = (isset($_GET[$columnKey]) && $_GET[$columnKey] != '') ? $_GET[$columnKey] : -1;

            /* get options */
            $options = [];

            /* add title as first option  */
            $options[] = '<option value="-1">' .
                (isset($fieldObject['label']) ? $fieldObject['label'] . ' (All)' : 'All') . '</option>';

            /* option depend on field type */
            if ($fieldObject) {
                if ($fieldObject['type'] === 'radio' || $fieldObject['type'] === 'select') {

                    /* add empty choice */
                    $options[] = '<option value="EMPTY"' .
                        ((empty($choiceKey) && 'EMPTY' == $selectedName) ? ' selected' : '') .
                        '><span style="font-style: italic">(empty)</span></option>';

                    /* loop through field choices and add to dropdown */
                    $choices = oes_get_field_object($columnKey)['choices'];
                    foreach ($choices as $choiceKey => $value)
                        $options[] = sprintf('<option value="%s" %s>%s</option>',
                            esc_attr($choiceKey),
                            ($choiceKey == $selectedName) ? ' selected' : '',
                            $value
                        );
                } elseif ($fieldObject['type'] === 'taxonomy') {

                    /* add empty choice */
                    $options[] = '<option value="EMPTY"' .
                        (('EMPTY' == $selectedName) ? ' selected' : '') .
                        '><span style="font-style: italic">(empty)</span></option>';

                    /* loop through terns and add to dropdown */
                    $terms = get_terms(['taxonomy' => oes_get_field_object($columnKey)['taxonomy'],
                        'hide_empty' => false]);
                    foreach ($terms as $term)
                        $options[] = sprintf('<option value="%s" %s>%s</option>',
                            $term->term_id,
                            ($term->term_id == $selectedName ||
                                (empty($term->name) && $selectedName == 'EMPTY')) ? ' selected' : '',
                            empty($term->name) ?
                                '<span style="font-style: italic">(empty)</span>' : $term->name
                        );
                } else {

                    /* get possible values via database query */
                    global $wpdb;
                    $select = 'SELECT DISTINCT pm.meta_value FROM ' . $wpdb->postmeta . ' pm' .
                        ' LEFT JOIN ' . $wpdb->posts . ' p ON p.ID = pm.post_id' .
                        ' WHERE pm.meta_key = "' . $columnKey . '" ' .
                        ' AND p.post_status = "publish"' .
                        ' AND p.post_type = "%s"' .
                        ' ORDER BY "' . $columnKey . '"';
                    $query = $wpdb->prepare($select, $post_type);
                    $results = $wpdb->get_col($query);

                    /* skip if no options found */
                    if (!empty($results)) {

                        natcasesort($results);
                        if ($fieldObject['type'] === 'date_picker') {

                            $format = oes_get_field_object($columnKey)['display_format'] ?? 'd.m.Y';
                            foreach ($results as $result)
                                $options[] = sprintf('<option value="%s" %s>%s</option>',
                                    empty($result) ? 'EMPTY' : esc_attr($result),
                                    ($result == $selectedName || (empty($result) && 'EMPTY' == $selectedName)) ?
                                        ' selected' : '',
                                    empty($result) ? '<span style="font-style: italic">(empty)</span>' :
                                        date($format, $result)
                                );
                        } elseif ($fieldObject['type'] === 'relationship') {

                            /* prepare results */
                            $prepareResults = [];
                            foreach ($results as $result)
                                if (!empty($result)) {
                                    if ($ids = unserialize($result))
                                        foreach ($ids as $id)
                                            if (!isset($prepareResults[$id]))
                                                $prepareResults[$id] = get_the_title($id);
                                }

                            asort($prepareResults);
                            foreach ($prepareResults as $id => $title)
                                $options[] = sprintf('<option value="%s" %s>%s</option>',
                                    $id,
                                    ($id == $selectedName || (empty($id) && 'EMPTY' == $selectedName)) ?
                                        ' selected' : '',
                                    empty($id) ? '<span style="font-style: italic">(empty)</span>' : $title
                                );
                        } else {

                            /* sort results alphabetically and loop through results */
                            foreach ($results as $result)
                                $options[] = sprintf('<option value="%s" %s>%s</option>',
                                    empty($result) ? 'EMPTY' : esc_attr($result),
                                    ($result == $selectedName || (empty($result) && 'EMPTY' == $selectedName)) ?
                                        ' selected' : '',
                                    empty($result) ? '<span style="font-style: italic">(empty)</span>' : $result
                                );
                        }
                    }
                }
            }

            /* create html dropdown box */
            echo '<select id="' . $columnKey . '" name="' . $columnKey . '">' .
                join('', $options) . '</select>';

        }
    }
}


/**
 * Fires after the main query vars have been parsed. Apply the selected column filter.
 *
 * @param WP_Query $query
 * @return void
 */
function columns_pre_get_posts(WP_Query $query): void
{

    /* bail early if not main query or not part of wp admin or edit page */
    if (is_admin() && $query->is_main_query()) {

        /* get global parameter for post type */
        global $post_type;
        $oes = OES();

        /* check for column filter */
        if (isset($oes->post_types[$post_type]['admin_columns'])) {


            /* get column to be sorted */
            $column = $query->get('orderby');

            /* loop through all columns and prepare meta query */
            $metaQuery = [];
            foreach ($oes->post_types[$post_type]['admin_columns'] as $columnKey) {

                /* prepare filter query, skip init or query not set*/
                if ((!empty($_GET[$columnKey] ?? '') && $_GET[$columnKey] !== '-1') && $columnKey !== 'init') {

                    /* check for query type */
                    $fieldObject = oes_get_field_object($columnKey);
                    if ($_GET[$columnKey] == "EMPTY")
                        $metaQuery[] = [
                            'key' => $columnKey,
                            'value' => '',
                            'compare' => '=='
                        ];
                    elseif (($fieldObject && $fieldObject['type'] == 'taxonomy' &&
                            $fieldObject['field_type'] == 'multi_select') ||
                        $fieldObject['type'] === 'relationship' ||
                        ($fieldObject['type'] === 'select' && ($fieldObject['multiple'] ?? false))) {
                        $metaQuery[] = [
                            'key' => $columnKey,
                            'value' => '"' . $_GET[$columnKey] . '"',
                            'compare' => 'LIKE'
                        ];
                    } else
                        $metaQuery[] = [
                            'key' => $columnKey,
                            'value' => $_GET[$columnKey],
                            'compare' => '=='
                        ];
                }


                /* prepare sorting filter */
                if (in_array($column, $oes->post_types[$post_type]['admin_columns']))
                    switch ($column) {

                        case 'parent' :
                            $query->set('orderby', 'meta_value_num');
                            $query->set('meta_key', 'field_oes_versioning_parent_post');
                            $query->set('meta_type', 'CHAR');
                            break;

                        case 'cb' :
                        case 'title' :
                        case 'date' :
                        case 'date_modified' :
                            break;

                        default :

                            /* get field type */
                            $fieldObject = oes_get_field_object($column);
                            if ($fieldObject && isset($fieldObject['type'])) {
                                switch ($fieldObject['type']) {
                                    case 'taxonomy' :
                                        $orderType = $fieldObject['field_type'] == 'multi_select' ? 'NUMERIC' : 'CHAR';
                                        $query->set('orderby', 'meta_value');
                                        $query->set('meta_key', $column);
                                        $query->set('meta_type', $orderType);
                                        $query->set('order', 'ASC');
                                        break;

                                    case 'number' :
                                        $query->set('orderby', 'meta_value_num');
                                        $query->set('meta_key', $column);
                                        $query->set('meta_type', 'CHAR');
                                        break;

                                    default:
                                        $query->set('orderby', 'meta_value');
                                        $query->set('meta_key', $column);
                                        $query->set('meta_type', 'CHAR');
                                        break;
                                }
                            }
                            break;
                    }
            }

            /* add to query */
            if (!empty($metaQuery)) $query->set('meta_query', $metaQuery);
        }
    }
}