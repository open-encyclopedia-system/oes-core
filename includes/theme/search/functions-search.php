<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;


/**
 * Modify the WordPress search to scan also post metadata according to configurations.
 *
 * @return void
 */
function oes_theme_modify_search(): void
{
    add_filter('posts_join', 'oes_search_posts_join');
    add_filter('posts_where', 'oes_search_posts_where');
    add_filter('posts_distinct', 'oes_search_posts_distinct');
}


/**
 * Get result objects for OES_Search object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'post-id'  : The post id.
 * @return OES_Search Returns search results.
 */
function oes_get_search_data(array $args = []): OES_Search
{
    global $oes_archive_count;
    $search = new OES_Search($args);
    $oes_archive_count = $search->count;
    return $search;
}


/**
 * Get data for OES_Search object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'prepared_ids'  : The prepared ids.
 *  'language'      : The considered language.
 *  'filter'        : The filter.
 *  'filter_array'  : The filter data.
 * @param array $additionalArgs Further additional parameters. Valid parameters are:
 *  'sort_by_language'      : Sort the results by language.
 *  'sort_by_post_type'     : Sort by post type.
 *
 * @return array[] Returns search results.
 */
function oes_search_get_results(array $args = [], array $additionalArgs = []): array
{
    /* get global OES instance parameter */
    global $oes, $oes_language;

    /* check for global parameter */
    if (empty($args)) {
        global $oes_search;
        if ($oes_search) {
            $args['prepared_ids'] = $oes_search->prepared_ids;
            $args['search_term'] = $oes_search->search_term;
        }
    }

    /* set default args */
    $args = array_merge([
        'prepared_ids' => [],
        'filter' => [],
        'filter_array' => []
    ], $args);
    $sortByLanguage = $additionalArgs['sort_by_language'] ?? true;
    $sortByPostType = $additionalArgs['sort_by_post_type'] ?? true;

    $searchTerm = $args['search_term'] ?? false;

    /* set language */
    if (!$oes_language) $oes_language = $_POST['search_params']['oes_language'] ?? 'language0';

    /* do the loop */
    $characters = [];
    $postIDs = [];
    $preparedPosts = [];
    $count = 0;
    if (!empty($args['prepared_ids']) && $searchTerm)
        foreach ($args['prepared_ids'] as $preparedID) {

            /* skip if status is not 'publish' */
            $post = get_post($preparedID);
            if ('publish' == $post->post_status &&
                ($oes->search['postmeta_fields'][$post->post_type] ?? false) !== 'hidden') {

                /* check if post is to be considered */
                $searchedFields = $oes->search['postmeta_fields'][$post->post_type] ?? false;

                /* skip if empty (no search field selected) */
                if (!empty($searchedFields) && is_array($searchedFields)) {

                    /* get post data */
                    $titleDisplay = oes_get_display_title_archive($preparedID);

                    /* get first character of displayed title */
                    $titleForSorting = oes_get_display_title_sorting($preparedID);
                    if (empty($titleForSorting)) $titleForSorting = $titleDisplay ?? $post->post_title;
                    $key = strtoupper(substr($titleForSorting, 0, 1));
                    if (!in_array($key, range('A', 'Z'))) $key = 'other';
                    if (!in_array($key, $characters)) $characters[] = $key;

                    /* prepare occurrences */
                    $occurrences = 0;
                    $occurrencesArray = [];

                    /* occurrences in title */
                    $occurrencesTitle = oes_get_highlighted_search($searchTerm, oes_get_display_title($preparedID));
                    if (!empty($occurrencesTitle)) {
                        $occurrencesContentTitle = '';
                        foreach ($occurrencesTitle as $singleOccurrence) {
                            $occurrencesContentTitle .= $singleOccurrence['unfiltered'];
                            $occurrences += $singleOccurrence['occurrences'];
                        }
                        $occurrencesArray['title'] = [
                            'label' => __('Title', 'oes'),
                            'value' => $occurrencesContentTitle
                        ];
                    }

                    /* occurrences in content */
                    $occurrencesContent = oes_get_highlighted_search($searchTerm, $post->post_content);
                    $maxParagraphs = $oes->search['max_preview_paragraphs'] ?? 1;
                    $countParagraphs = 0;
                    if (!empty($occurrencesContent)) {
                        $occurrencesContentString = '';
                        foreach ($occurrencesContent as $singleOccurrence) {
                            if ($countParagraphs < $maxParagraphs)
                                $occurrencesContentString .= $singleOccurrence['paragraph'] .
                                    '<a href="' . get_permalink($preparedID) . '" class="oes-dot-dot-dot"></a>';
                            $occurrences += $singleOccurrence['occurrences'];
                            $countParagraphs++;
                        }
                        $occurrencesArray['content'] = [
                            'value' => $occurrencesContentString
                        ];
                    }

                    /* occurrences in fields */
                    $postType = $oes->post_types[$post->post_type] ?? [];
                    foreach ($searchedFields as $fieldKey)
                        if (!in_array($fieldKey, ['title', 'content'])) {
                            $field = oes_get_field_object($fieldKey, $post->ID);
                            switch ($field['type']) {

                                case 'text' :
                                case 'textarea' :
                                case 'wysiwyg' :
                                case 'url' :

                                    if ($results = oes_get_highlighted_search($searchTerm, $field['value'])) {

                                        $value = '';
                                        if (count($results) > 1) {

                                            /* multiple occurrences inside the field, list occurrences */
                                            $fieldOccurrences = [];
                                            foreach ($results as $item) {

                                                /* if not first or single occurrence, add '...' before result text,
                                                if not last or single occurrence, add '...' before result text */
                                                $fieldOccurrences[] =
                                                    (in_array($item['position'], ['first', 'single']) ?
                                                        '' :
                                                        '<span class="oes-dot-dot-dot"></span>') .
                                                    $item['unfiltered'] .
                                                    (in_array($item['position'], ['last', 'single']) ?
                                                        '' :
                                                        '<span class="oes-dot-dot-dot"></span>');

                                                $occurrences += $item['occurrences'];
                                            }

                                            if (!empty($fieldOccurrences))
                                                $value = '<ul id="search-results"><li>' .
                                                    implode('</li><li>', $fieldOccurrences) .
                                                    '</li></ul>';

                                        } else {
                                            $value = $results[0]['unfiltered'];
                                            $occurrences += $results[0]['occurrences'];
                                        }

                                        $occurrencesArray[] = [
                                            'label' =>
                                                $postType['field_options'][$fieldKey]['label_translation_' . $oes_language] ??
                                                $field['label'],
                                            'value' => $value
                                        ];
                                    }

                                    break;

                                case 'date_picker' :
                                case 'select' :
                                case 'relationship' :
                                    //@oesDevelopment Implement more search field types.
                                    break;

                            }
                        }


                    /* add information */
                    if (!empty($occurrences)) {

                        /* add post type to object type filter */
                        $postTypeLabel = ($postType['label_translations_plural'][$oes_language] ??
                            ($postType['label'] ??
                                (get_post_type_object($post->post_type)->labels->singular_name ?? 'Label missing')));
                        $args['filter_array']['list']['objects']['items'][$post->post_type] = $postTypeLabel;
                        $args['filter_array']['json']['objects'][$post->post_type][] = $preparedID;

                        /* get post language */
                        $postLanguage = oes_get_post_language($preparedID) ?? false;
                        if (!$postLanguage && $parentID = get_parent_id($preparedID))
                            $postLanguage = oes_get_post_language($parentID) ?? false;
                        if (!$postLanguage || $postLanguage === 'all')
                            $postLanguage = $oes_language;

                        $preparedPost = [
                            'id' => $preparedID,
                            'title' => $titleDisplay,
                            'permalink' => get_permalink($preparedID),
                            'version' => get_version_field($preparedID),
                            'type' => $postTypeLabel,
                            'post_type' => $post->post_type,
                            'occurrences' => $occurrencesArray,
                            'occurrences-count' => $occurrences,
                            'language' => $postLanguage
                        ];

                        if ($sortByLanguage && $sortByPostType)
                            $preparedPosts[$postLanguage][$post->post_type][$occurrences][$titleForSorting . (10000 - $preparedID)] = $preparedPost;
                        elseif ($sortByLanguage)
                            $preparedPosts[$postLanguage][$occurrences][$titleForSorting . (10000 - $preparedID)] = $preparedPost;
                        elseif ($sortByPostType)
                            $preparedPosts[$post->post_type][$occurrences][$titleForSorting . (10000 - $preparedID)] = $preparedPost;
                        else
                            $preparedPosts[$occurrences][$titleForSorting . (10000 - $preparedID)] = $preparedPost;

                        $postIDs[] = $preparedID;
                        $count++;
                    }
                }
            }
        }


    /* Prepare object filter */
    if (!empty($args['filter_array']['list']['objects']['items']))
        $args['filter_array']['list']['objects']['label'] = $oes->search['type_label'][$oes_language] ??
            __('Type', 'oes');

    return [
        'search_term' => $searchTerm,
        'characters' => $characters,
        'prepared_posts' => $preparedPosts,
        'post_ids' => $postIDs,
        'count' => $count,
        'filter' => $args['filter'],
        'filter_array' => $args['filter_array']
    ];
}


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
            $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON (' . $wpdb->posts . '.ID = ' .
                $wpdb->postmeta . '.post_id) ';
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
        if (isset($oes->search['postmeta_fields']) &&
            !empty($oes->search['postmeta_fields']) &&
            is_array($oes->search['postmeta_fields'])) {

            /* get post meta fields to be searched */
            $filterFields = [];
            foreach ($oes->search['postmeta_fields'] as $fields)
                if (is_array($fields))
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