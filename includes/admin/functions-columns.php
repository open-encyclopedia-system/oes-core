<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Query;
use function OES\Versioning\get_parent_id;


/**
 * Add extra columns to the list display of posts or terms in the WP admin area.
 *
 * @param array $columns The columns for the post type or taxonomy being displayed.
 * @return array Modified columns to be displayed.
 */
function add_post_column(array $columns): array
{
    $oes = OES();
    global $post_type, $taxonomy;

    $screen = get_current_screen();
    $isTaxonomy = !empty($screen->taxonomy);
    $objectKey = $isTaxonomy ? $taxonomy : $post_type;
    $configType = $isTaxonomy ? 'taxonomies' : 'post_types';

    if (!isset($oes->{$configType}[$objectKey])) {
        return $columns;
    }

    $adminColumns = $oes->{$configType}[$objectKey]['admin_columns'] ?? [];
    $fieldOptions = $oes->{$configType}[$objectKey]['field_options'] ?? [];

    $newColumns = [];

    foreach ($adminColumns as $columnKey) {
        switch ($columnKey) {
            case 'init':
                // Ignore 'init' keys
                break;

            case 'cb':
            case 'title':
            case 'date':
            case 'name':
            case 'description':
            case 'slug':
            case 'posts':
                // Use existing column if defined
                if (isset($columns[$columnKey])) {
                    $newColumns[$columnKey] = $columns[$columnKey];
                }
                break;

            case 'id':
                $newColumns[$columnKey] = __('Term ID', 'oes');
                break;

            case 'date_modified':
                $newColumns[$columnKey] = __('Last Updated', 'oes');
                break;

            case 'parent':
                $newColumns[$columnKey] = __('Parent', 'oes');
                break;

            default:
                if ($isTaxonomy) {
                    // Taxonomy context: Use label from field options or fallback
                    $newColumns[$columnKey] = $fieldOptions[$columnKey]['label'] ?? $columnKey;
                } else {
                    // Post type context
                    if ($taxonomyObj = get_taxonomy($columnKey)) {
                        $newColumns[$columnKey] = $oes->taxonomies[$columnKey]['label'] ?? $taxonomyObj->label;
                    } elseif (str_starts_with($columnKey, 'taxonomy-')) {
                        $newColumns[$columnKey] = $columns[$columnKey] ?? $columnKey;
                    } else {
                        $newColumns[$columnKey] = $fieldOptions[$columnKey]['label'] ?? $columnKey;
                    }
                }
                break;
        }
    }

    return empty($newColumns) ? $columns : $newColumns;
}

/**
 * Display values for columns in the list display of post types in the WP admin area.
 *
 * @param string $column The column name.
 * @param string $post_id The post ID.
 * @return void
 */
function display_post_column_value(string $column, string $post_id): void
{
    if (str_starts_with($column, 'taxonomy-')) {
        return;
    }

    switch ($column) {
        case 'cb':
        case 'title':
        case 'date':
            return;

        case 'date_modified':
            $date = get_post_datetime($post_id);
            $dateMod = get_post_datetime($post_id, 'modified');

            if (!$date || !$dateMod) return;

            $timeString = sprintf(
                "Last Updated<br>%s at %s",
                date_i18n('Y/m/d', $dateMod->getTimestamp()),
                date_i18n('G:i', $dateMod->getTimestamp())
            );

            echo ($date->getTimestamp() === $dateMod->getTimestamp())
                ? '<span class="oes-grey-out">' . $timeString . '</span>'
                : $timeString;
            return;

        case 'parent':
            $parentID = get_parent_id($post_id);

            if ($parentID) {
                $parentPost = get_post($parentID);
                if ($parentPost) {
                    printf(
                        '<span class="oes-column-post"><a href="%s" class="oes-admin-link">%s</a></span>',
                        admin_url('post.php?post=' . $parentID . '&action=edit'),
                        esc_html($parentPost->post_title)
                    );
                }
            }
            return;

        default:

            // ACF field check
            $value = oes_get_field($column, $post_id);

            if (!empty($value)) {
                $fieldObject = oes_get_field_object($column);
                echo format_acf_field_value($fieldObject, $value, $column, $post_id);
                return;
            }

            // Taxonomy fallback
            if (get_taxonomy($column)) {
                $terms = get_the_terms($post_id, $column);

                if (!empty($terms) && !is_wp_error($terms)) {
                    $termLinks = array_map(function ($term) use ($column) {
                        return sprintf(
                            '<span class="oes-column-tag"><a href="%s">%s</a></span>',
                            esc_url(admin_url('edit.php?post_type=' . ($_GET['post_type'] ?? '') . '&' . $column . '=' . $term->slug)),
                            esc_html($term->name)
                        );
                    }, $terms);

                    echo implode('', $termLinks);
                }
            }
    }
}

/**
 * Display values for columns in the taxonomy list table in the WP admin area.
 *
 * @param string $ignore Not used (WP filter signature compatibility).
 * @param string $column The column name.
 * @param int $term_id The term ID.
 * @return void
 */
function display_taxonomy_column_value(string $ignore, string $column, int $term_id): void
{
    switch ($column) {
        case 'cb':
        case 'name':
        case 'description':
        case 'slug':
        case 'posts':
            return;

        case 'id':
            echo esc_html((string)$term_id);
            return;

        default:
            global $taxonomy;
            $fieldKey = $taxonomy . '_' . $term_id;

            $value = oes_get_field($column, $fieldKey);
            if (empty($value)) return;

            $fieldObject = oes_get_field_object($column);
            echo format_acf_field_value($fieldObject, $value, $column, $term_id, 'taxonomy');
            return;
    }
}

/**
 * Format ACF field values for admin column display (post or taxonomy context).
 *
 * @param array|null $fieldObject ACF field object.
 * @param mixed $value The field value.
 * @param string $column Column name.
 * @param int|string $objectID Post ID or term ID.
 * @param string $context Either 'post' or 'taxonomy'.
 * @return string
 */
function format_acf_field_value(array $fieldObject, $value, string $column, $objectID, string $context = 'post'): string
{
    if (!$fieldObject) {
        return is_scalar($value) ? esc_html((string)$value) : '';
    }

    switch ($fieldObject['type']) {
        case 'link':
            return is_array($value) ? esc_url($value['url'] ?? '') : esc_html($value);

        case 'radio':
        case 'select':
            $selected = oes_get_select_field_value($column, $objectID);
            return is_array($selected) ? esc_html(implode(', ', $selected)) : esc_html((string)$selected);

        case 'post_object':
            $post = get_post($value);
            return $post
                ? sprintf(
                    '<span class="oes-column-post"><a href="%s" class="oes-admin-link">%s</a></span>',
                    esc_url(admin_url('post.php?post=' . $post->ID . '&action=edit')),
                    esc_html($post->post_title)
                )
                : esc_html((string)$value);

        case 'relationship':
            if (!is_array($value)) {
                return get_post($value) ? oes_get_display_title($value) : esc_html((string)$value);
            }

            $uniquePosts = array_unique($value, SORT_REGULAR);
            $links = [];

            foreach ($uniquePosts as $relPost) {
                $postID = is_object($relPost) ? $relPost->ID : $relPost;
                $post = get_post($postID);

                if ($post) {
                    $links[] = sprintf(
                        '<span class="oes-column-post"><a href="%s" class="oes-admin-link">%s</a></span>',
                        esc_url(admin_url('post.php?post=' . $postID . '&action=edit')),
                        esc_html($post->post_title)
                    );
                }
            }

            return implode('', $links);

        case 'taxonomy':
            $links = [];

            $termIDs = is_array($value) ? $value : [$value];
            foreach ($termIDs as $termID) {
                $term = get_term($termID);
                if ($term && !is_wp_error($term)) {
                    $url = ($context == 'post')
                        ? ('edit.php?post_type=' . ($_GET['post_type'] ?? '') . '&' . $column . '=' . $termID)
                        : ('term.php?taxonomy=' . $term->taxonomy . '&tag_ID=' . $termID);
                    $links[] = sprintf(
                        '<span class="oes-column-taxonomy"><a href="%s">%s</a></span>',
                        esc_url(admin_url($url)),
                        esc_html($term->name)
                    );
                }
            }

            return implode(', ', $links);
    }

    return is_scalar($value) ? esc_html((string)$value) : '';
}

/**
 * Make columns sortable in the list display of post types in the wp admin area.
 *
 * @param array $columns The columns to be displayed.
 * @return array The modified sortable columns.
 */
function make_columns_sortable(array $columns): array
{
    global $post_type, $taxonomy;
    $oes = OES();

    $isTaxonomy = !empty(get_current_screen()->taxonomy);

    $config = $isTaxonomy
        ? $oes->taxonomies[$taxonomy] ?? null
        : $oes->post_types[$post_type] ?? null;

    if (!$config || empty($config['admin_columns'])) return $columns;

    foreach ($config['admin_columns'] as $fieldKey) {
        if ($fieldKey === 'init') {
            continue;
        }

        // Special case for post type
        if (!$isTaxonomy && $fieldKey === 'date_modified') {
            $columns[$fieldKey] = $fieldKey; // You can also use 'modified' if desired
            continue;
        }

        $fieldObject = oes_get_field_object($fieldKey);

        // Skip complex field types
        $skipTypes = ['taxonomy', 'relationship', 'link'];
        if ($fieldObject && in_array($fieldObject['type'], $skipTypes, true)) {
            continue;
        }

        // Mark the column as sortable
        $columns[$fieldKey] = $fieldKey;
    }

    return $columns;
}

/**
 * Add filter dropdowns to the post type admin list screen.
 *
 * @param string $post_type The current post type.
 * @return void
 */
function columns_filter_dropdown(string $post_type): void
{
    if (!is_admin()) return;

    $oes = OES();

    if (empty($oes->post_types[$post_type]['admin_columns'])) return;

    foreach ($oes->post_types[$post_type]['admin_columns'] as $columnKey) {
        if (in_array($columnKey, ['cb', 'title', 'init', 'date', 'date_modified', 'parent'])) {
            continue;
        }

        $fieldObject = $oes->post_types[$post_type]['field_options'][$columnKey] ?? null;
        if (!$fieldObject || in_array($fieldObject['type'], ['link', 'relationship', 'text', 'textarea'])) {
            continue;
        }

        if(str_starts_with($columnKey,'field_connected_parent')
            || str_starts_with($columnKey,'field_oes_versioning')){
            continue;
        }

        $selectedValue = $_GET[$columnKey] ?? '-1';
        $options = [];

        // First option: All
        $label = $fieldObject['label'] ?? $columnKey;
        $options[] = sprintf('<option value="-1">%s (All)</option>', esc_html($label));

        // Empty option
        $options[] = sprintf(
            '<option value="EMPTY"%s>(empty)</option>',
            selected($selectedValue, 'EMPTY', false)
        );

        // Add choices based on field type
        switch ($fieldObject['type']) {
            case 'radio':
            case 'select':
                $choices = $fieldObject['choices'] ?? [];
                foreach ($choices as $choiceKey => $value) {
                    $options[] = sprintf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($choiceKey),
                        selected($selectedValue, $choiceKey, false),
                        esc_html($value)
                    );
                }
                break;

            case 'taxonomy':
                $taxonomy = $fieldObject['taxonomy'] ?? '';
                $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
                if(!is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $options[] = sprintf(
                            '<option value="%d"%s>%s</option>',
                            $term->term_id,
                            selected($selectedValue, $term->term_id, false),
                            esc_html($term->name ?: '(empty)')
                        );
                    }
                }
                break;

            case 'date_picker':
            default:
                global $wpdb;

                $query = $wpdb->prepare(
                    "
                    SELECT DISTINCT pm.meta_value
                    FROM {$wpdb->postmeta} pm
                    LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                    WHERE pm.meta_key = %s
                    AND p.post_status = 'publish'
                    AND p.post_type = %s
                    ORDER BY pm.meta_value ASC
                    ",
                    $columnKey,
                    $post_type
                );
                $results = $wpdb->get_col($query);

                if (!empty($results)) {
                    natcasesort($results);

                    foreach ($results as $value) {
                        $displayValue = $value;

                        if ($fieldObject['type'] === 'date_picker' && is_numeric($value)) {
                            $format = $fieldObject['display_format'] ?? 'd.m.Y';
                            $displayValue = date($format, $value);
                        }

                        $options[] = sprintf(
                            '<option value="%s"%s>%s</option>',
                            esc_attr($value ?: 'EMPTY'),
                            selected($selectedValue, $value ?: 'EMPTY', false),
                            esc_html($displayValue ?: '(empty)')
                        );
                    }
                }
                break;
        }

        echo sprintf(
            '<select id="%s" name="%s">%s</select> ',
            esc_attr($columnKey),
            esc_attr($columnKey),
            implode('', $options)
        );
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
    // Only proceed for main query on WP admin edit screens
    if (!is_admin() || ! $query->is_main_query()) {
        return;
    }

    $oes = OES();

    // Get post type from query vars (fallback to empty string if not set)
    $post_type = $query->get('post_type') ?? '';

    // Bail if no post type or no config for this post type
    if (empty($post_type) || !isset($oes->post_types[$post_type]['admin_columns'])) {
        return;
    }

    $adminColumns = $oes->post_types[$post_type]['admin_columns'];
    $orderby = $query->get('orderby');

    $metaQuery = [];

    foreach ($adminColumns as $columnKey) {

        if ($columnKey === 'init') {
            continue;
        }

        $filterValue = $_GET[$columnKey] ?? null;

        if ($filterValue !== null && $filterValue !== '' && $filterValue !== '-1') {

            $fieldObject = oes_get_field_object($columnKey);

            if ($filterValue === "EMPTY") {
                $metaQuery[] = [
                    'key' => $columnKey,
                    'value' => '',
                    'compare' => '=',
                ];
            } elseif (
                $fieldObject
                && (
                    ($fieldObject['type'] === 'taxonomy' && ($fieldObject['field_type'] ?? '') === 'multi_select')
                    || $fieldObject['type'] === 'relationship'
                    || ($fieldObject['type'] === 'select' && ($fieldObject['multiple'] ?? false))
                )
            ) {
                $metaQuery[] = [
                    'key' => $columnKey,
                    'value' => '"' . $filterValue . '"',
                    'compare' => 'LIKE',
                ];
            } else {
                $metaQuery[] = [
                    'key' => $columnKey,
                    'value' => $filterValue,
                    'compare' => '=',
                ];
            }
        }
    }

    // Prepare orderby meta query if ordering by a valid admin column
    if ($orderby && in_array($orderby, $adminColumns, true)) {

        switch ($orderby) {

            case 'parent':
                $query->set('orderby', 'meta_value_num');
                $query->set('meta_key', 'field_oes_versioning_parent_post');
                $query->set('meta_type', 'CHAR');
                break;

            case 'cb':
            case 'title':
            case 'date':
            case 'date_modified':
                // Do nothing — handled by WP core
                break;

            default:
                $fieldObject = oes_get_field_object($orderby);
                if ($fieldObject && isset($fieldObject['type'])) {
                    switch ($fieldObject['type']) {
                        case 'taxonomy':
                            $orderType = ($fieldObject['field_type'] ?? '') === 'multi_select' ? 'NUMERIC' : 'CHAR';
                            $query->set('orderby', 'meta_value');
                            $query->set('meta_key', $orderby);
                            $query->set('meta_type', $orderType);
                            $query->set('order', 'ASC');
                            break;

                        case 'number':
                            $query->set('orderby', 'meta_value_num');
                            $query->set('meta_key', $orderby);
                            $query->set('meta_type', 'CHAR');
                            break;

                        default:
                            $query->set('orderby', 'meta_value');
                            $query->set('meta_key', $orderby);
                            $query->set('meta_type', 'CHAR');
                            break;
                    }
                }
                break;
        }
    }

    if (!empty($metaQuery)) {
        $query->set('meta_query', $metaQuery);
    }
}
