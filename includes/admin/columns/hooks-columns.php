<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field_object;

add_action('oes/data_model_registered', 'oes_column_data_model_registered');
add_action('restrict_manage_posts', 'oes_column_restrict_manage_posts', 10, 2);
add_action('pre_get_posts', 'oes_column_pre_get_posts');

//@oesDevelopment Combine with function oes_column_pre_get_posts.
add_action('pre_get_posts', 'oes_column_pre_get_posts_2');


/**
 * Prepare filter for column display, sorting and filtering.
 * @return void
 */
function oes_column_data_model_registered(): void
{

    /* loop through post types and add action and filter for post type that have defined columns */
    $oes = OES();
    foreach ($oes->post_types as $postType => $postTypeConfiguration)
        if (isset($postTypeConfiguration['admin_columns'])) {
            add_filter('manage_' . $postType . '_posts_columns', '\OES\Admin\Columns\add_post_column');
            add_action('manage_' . $postType . '_posts_custom_column', '\OES\Admin\Columns\display_post_column_value', 10, 2);
            add_filter('manage_edit-' . $postType . '_sortable_columns', '\OES\Admin\Columns\make_columns_sortable');
        }

    /* loop through taxonomies and add action and filter for post type that have defined columns */
    foreach ($oes->taxonomies as $taxonomyKey => $taxonomyConfiguration)
        if (isset($taxonomyConfiguration['admin_columns'])) {
            add_filter('manage_edit-' . $taxonomyKey . '_columns', '\OES\Admin\Columns\add_post_column');
            add_filter('manage_' . $taxonomyKey . '_custom_column', '\OES\Admin\Columns\display_taxonomy_column_value', 10, 3);
            //@oesDevelopment Add feature for taxonomy, add_filter('manage_edit-' . $taxonomyKey . '_sortable_columns', 'OES\Admin\Columns\make_columns_sortable');
        }
}


/**
 * Add extra filter dropdown box to the list tables.
 *
 * @param string $post_type The post type that is being displayed.
 * @return void
 *
 */
function oes_column_restrict_manage_posts(string $post_type): void
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
function oes_column_pre_get_posts(WP_Query $query): void
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


/**
 * Hook into query after query variable is defined but not yet fired to add sorting columns.
 *
 * @param WP_Query $query The query.
 * @return void
 */
function oes_column_pre_get_posts_2(WP_Query $query): void
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