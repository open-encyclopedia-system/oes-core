<?php

namespace OES\Admin\Columns;


use WP_Query;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_select_field_value;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* prepare filter ----------------------------------------------------------------------------------------------------*/
add_action('oes/datamodel_registered', '\OES\Admin\Columns\initialize_filter');


/**
 * Prepare filter for column display, sorting and filtering.
 */
function initialize_filter()
{
    /* loop through post types and add action and filter for post type that have defined columns */
    $oes = OES();
    foreach ($oes->post_types as $postType => $postTypeConfiguration)
        if (isset($postTypeConfiguration['admin_columns'])) {
            add_filter('manage_' . $postType . '_posts_columns', 'OES\Admin\Columns\add_post_column');
            add_action('manage_' . $postType . '_posts_custom_column', 'OES\Admin\Columns\display_post_column_value', 10, 2);
            add_filter('manage_edit-' . $postType . '_sortable_columns', 'OES\Admin\Columns\make_columns_sortable');
        }

    /* loop through taxonomies and add action and filter for post type that have defined columns */
    foreach ($oes->taxonomies as $taxonomyKey => $taxonomyConfiguration)
        if (isset($taxonomyConfiguration['admin_columns'])) {
            add_filter('manage_edit-' . $taxonomyKey . '_columns', 'OES\Admin\Columns\add_post_column');
            add_filter('manage_' . $taxonomyKey . '_custom_column', 'OES\Admin\Columns\display_taxonomy_column_value', 10, 3);
            //TODO @nextRelease add_filter('manage_edit-' . $taxonomyKey . '_sortable_columns', 'OES\Admin\Columns\make_columns_sortable');
        }
}


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
                            $newColumns[$columnKey] = $oes->taxonomies[$taxonomy]['label'] ?? $taxonomy->label;
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
 */
function display_post_column_value(string $column, string $post_id)
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
                $parentID = \OES\Versioning\get_parent_id($post_id);

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
 */
function display_taxonomy_column_value(string $ignore, string $column, int $term_id)
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


/* sort columns ------------------------------------------------------------------------------------------------------*/
add_action('pre_get_posts', '\OES\Admin\Columns\sort_columns');


/**
 * Hook into query after query variable is defined but not yet fired to add sorting columns.
 *
 * @param WP_Query $query The query.
 */
function sort_columns(WP_Query $query)
{
    /* check if main query and part of wp admin */
    if (is_admin() && $query->is_main_query()) {

        /* get global parameter for post type */
        global $post_type;
        $oes = OES();

        /* get column to be sorted */
        $column = $query->get('orderby');

        /* order by column value */
        if (isset($oes->post_types[$post_type]['admin_columns'][$column]['type']))
            switch ($oes->post_types[$post_type]['admin_columns'][$column]['type']) {

                case 'parent' :
                    $query->set('orderby', 'meta_value_num');
                    $query->set('meta_key', 'field_oes_versioning_parent_post');
                    $query->set('meta_type', 'CHAR');
                    break;

                default :

                    /* get field type */
                    $fieldObject = oes_get_field_object($column);
                    if ($fieldObject['type']) {
                        switch ($fieldObject['type']) {
                            case 'taxonomy' :
                                $orderType = $fieldObject['field_type'] == 'multi_select' ? 'NUMERIC' : 'CHAR';
                                $query->set('orderby', 'meta_value');
                                $query->set('meta_key', $column);
                                $query->set('meta_type', $orderType);
                                $query->set('order', 'ASC');
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
}


/* sort columns ------------------------------------------------------------------------------------------------------*/
add_action('restrict_manage_posts', '\OES\Admin\Columns\add_column_filter', 10, 2);


/**
 * Add extra filter dropdown box to the list tables.
 *
 * @param string $post_type The post type that is being displayed.
 *
 */
function add_column_filter(string $post_type)
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

            /* get options -------------------------------------------------------------------------------------------*/
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
                                        date_format(date_create($result), $format)
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


/* sort columns ------------------------------------------------------------------------------------------------------*/
add_action('pre_get_posts', '\OES\Admin\Columns\apply_column_filter');


/**
 * Fires after the main query vars have been parsed. Apply the selected column filter.
 *
 * @param WP_Query $query
 */
function apply_column_filter(WP_Query $query)
{
    /* get global parameter for post type */
    global $pagenow, $post_type;
    $oes = OES();

    /* bail early if not main query or not part of wp admin or edit page */
    if (is_admin() && $query->is_main_query() && $pagenow === 'edit.php') {

        /* check for column filter */
        if (isset($oes->post_types[$post_type]['admin_columns'])) {

            /* loop through all columns and prepare meta query */
            $metaQuery = [];
            foreach ($oes->post_types[$post_type]['admin_columns'] as $columnKey) {

                /* skip init or query not set*/
                if ((!isset($_GET[$columnKey]) || $_GET[$columnKey] == '' || $_GET[$columnKey] == '-1') ||
                    $columnKey == 'init') continue;

                /* check for query type */
                $fieldObject = oes_get_field_object($columnKey);
                if (($fieldObject && $fieldObject['type'] == 'taxonomy' &&
                        $fieldObject['field_type'] == 'multi_select') ||
                    $fieldObject['type'] === 'relationship')
                    $metaQuery[] = [
                        'key' => $columnKey,
                        'value' => ($_GET[$columnKey] == "EMPTY") ? '' : '"' . $_GET[$columnKey] . '"',
                        'compare' => 'LIKE'
                    ];
                else
                    $metaQuery[] = [
                        'key' => $columnKey,
                        'value' => ($_GET[$columnKey] == "EMPTY") ? '' : $_GET[$columnKey],
                        'compare' => '=='
                    ];
            }

            /* add to query */
            if (!empty($metaQuery)) $query->set('meta_query', $metaQuery);
        }
    }
}