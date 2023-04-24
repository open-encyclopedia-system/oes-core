<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('posts_join', 'oes_search_posts_join');
add_filter('posts_where', 'oes_search_posts_where');
add_filter('posts_distinct', 'oes_search_posts_distinct');


/**
 * Add search in post meta table.
 *
 * @param string $join The sql join statement is passed by WordPress search.
 * @return string Returns modified join string.
 */
function oes_search_posts_join(string $join): string
{


    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) {

        global $oes, $wpdb;

        /* add post meta */
        if (isset($oes->search['postmeta_fields']) && !empty($oes->search['postmeta_fields']))
            $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON (' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id) ';
    }

    return $join;
}


/**
 * Extend search in post meta value.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string Returns modified where string.
 */
function oes_search_posts_where(string $where): string
{

    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) {

        /* get global search variable */
        global $wpdb, $oes;

        $prepareStatement = '';

        /* add post meta */
        if (isset($oes->search['postmeta_fields']) && !empty($oes->search['postmeta_fields'])) {

            /* get post meta fields to be searched */
            $filterFields = [];
            foreach ($oes->search['postmeta_fields'] as $fields)
                foreach ($fields as $field)
                    if (!in_array($field, ['title', 'content'])) $filterFields[] = '"' . $field . '"';

            /* include search in meta value, exclude search in post meta with meta keys starting with '_' */
            if (!empty($filterFields)) $prepareStatement .= " OR " .
                "((" . $wpdb->postmeta . ".meta_value LIKE $1) " .
                "AND (" . $wpdb->postmeta . ".meta_key NOT LIKE '" . '^_%' . "' ESCAPE '" . '^' . "') " .
                "AND " . $wpdb->postmeta . ".meta_key IN (" . implode(',', $filterFields) . "))";
        }

        /* hook search into existing search (title is arbitrary) */
        if (!empty($prepareStatement)) {
            $replacement = "(" . $wpdb->posts . ".post_title LIKE $1)" . $prepareStatement;
            $where = preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                $replacement,
                $where);
        }


    }
    return $where;
}


/**
 * Prevent duplicates in sql where statement.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string Returns modified where string.
 */
function oes_search_posts_distinct(string $where): string
{

    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) return "DISTINCT";
    return $where;
}