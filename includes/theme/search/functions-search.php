<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field_object;
use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;


/**
 * Modify the WordPress search to scan also post metadata according to configurations.
 *
 * @return void
 */
function oes_theme_modify_search(): void
{
    oes_include('/includes/theme/search/hooks-search-modify.php');
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
    global $oes_language, $oes_archive_count;
    $search = new OES_Search($args, $oes_language);
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
 * @return array[] Returns search results.
 */
function oes_search_get_results(array $args = []): array
{
    /* get global OES instance parameter */
    global $oes;

    /* set default args */
    $args = array_merge([
        'prepared_ids' => [],
        'language' => 'language0',
        'filter' => [],
        'filter_array' => []
    ], $args);
    $searchTerm = $args['search_term'] ?? false;

    /* check for global parameter */
    if (empty($args['prepared_ids'])) {
        global $oes_search;
        if (isset($oes_search['prepared_ids'])) $args['prepared_ids'] = $oes_search['prepared_ids'];
    }

    /* do the loop */
    $characters = [];
    $postIDs = [];
    $preparedPosts = [];
    $count = 0;
    if (!empty($args['prepared_ids']) && $searchTerm)
        foreach ($args['prepared_ids'] as $preparedID) {

            /* skip if status is not 'publish' */
            $post = get_post($preparedID);
            if ('publish' == $post->post_status) {


                /* check if post is to be considered */
                $searchedFields = $oes->search['postmeta_fields'][$post->post_type] ?? false;

                /* skip if empty (no search field selected) */
                if (!empty($searchedFields)) {

                    /* get post data ---------------------------------------------------------------------------------*/
                    $titleDisplay = oes_get_display_title($preparedID, ['option' => 'title_archive_display']);

                    /* get first character of displayed title */
                    $titleForSorting = oes_get_display_title($preparedID, ['option' => 'title_sorting_display']);
                    $titleForSorting = oes_replace_umlaute($titleForSorting);
                    if (empty($titleForSorting)) $titleForSorting = $titleDisplay ?? $post->post_title;
                    $key = strtoupper(substr($titleForSorting, 0, 1));

                    /* check if non-alphabetic key */
                    if (!in_array($key, range('A', 'Z'))) $key = 'other';

                    /* prepare array with existing first characters of displayed posts */
                    if (!in_array($key, $characters)) $characters[] = $key;


                    /* get occurrences -------------------------------------------------------------------------------*/
                    $occurrences = 0;
                    $occurrencesArray = [];


                    /* title */
                    $occurrencesTitle = oes_get_highlighted_search($searchTerm, oes_get_display_title($preparedID));
                    if (!empty($occurrencesTitle)) {
                        $occurrencesContentTitle = '';
                        foreach ($occurrencesTitle as $singleOccurrence) {
                            $occurrencesContentTitle .= $singleOccurrence['unfiltered'];
                            $occurrences += $singleOccurrence['occurrences'];
                        }
                        $occurrencesArray['title'] = [
                            'th' => ['Title'],
                            'td' => [$occurrencesContentTitle]
                        ];
                    }


                    /* content */
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
                            'th' => ['Content'],
                            'td' => [$occurrencesContentString]
                        ];
                    }

                    /* search fields */
                    foreach ($searchedFields as $fieldKey)
                        if (!in_array($fieldKey, ['title', 'content'])) {
                            $field = oes_get_field_object($fieldKey, $post->ID);
                            switch ($field['type']) {

                                case 'text' :
                                case 'textarea' :
                                case 'wysiwyg' :
                                case 'url' :

                                    /* get search results */
                                    $searchResultsFields = oes_get_highlighted_search($searchTerm, $field['value']);

                                    /* prepare search result for display */
                                    if ($searchResultsFields) {

                                        /* multiple occurrences inside the field, list occurrences */
                                        if (count($searchResultsFields) > 1) {

                                            /* open list */
                                            $returnString = '<ul id="search-results">';

                                            /* loop through occurrences */
                                            foreach ($searchResultsFields as $item) {

                                                /* open item */
                                                $returnString .= '<li>';

                                                /* if not first or single occurrence, add '...' before result text */
                                                if (!in_array($item['position'], ['first', 'single']))
                                                    $returnString .= '<span class="oes-dot-dot-dot"></span>';

                                                /* add result text */
                                                $returnString .= $item['unfiltered'];

                                                /* if not last or single occurrence, add '...' before result text */
                                                if (!in_array($item['position'], ['last', 'single']))
                                                    $returnString .= '<span class="oes-dot-dot-dot"></span>';

                                                /* close item */
                                                $returnString .= '</li>';

                                                /* add to occurrences of post */
                                                $occurrences += $item['occurrences'];
                                            }

                                            /* close list */
                                            $returnString .= '</ul>';
                                        } /* single occurrences inside the field */
                                        else {
                                            $returnString = $searchResultsFields[0]['unfiltered'];
                                            $occurrences += $searchResultsFields[0]['occurrences'];
                                        }

                                        /* add information to table */
                                        $label =
                                            $oes->post_types[$post->post_type]['field_options'][$fieldKey]['label_translation_' . $args['language']] ?:
                                                $field['label'];
                                        $occurrencesArray[] = [
                                            'th' => [$label],
                                            'td' => [$returnString]
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


                    /* add information  ---------------------------------------------.--------------------------------*/
                    $version = false;
                    if (isset($oes->post_types[$post->post_type]['field_options']['field_oes_post_version']))
                        $version = get_version_field($preparedID) ?: false;
                    if (!empty($occurrences)) {

                        /* add post type to object type filter -------------------------------------------------------*/
                        $postTypeLabel = ($oes->post_types[$post->post_type]['theme_labels']['archive__header'][$args['language']] ??
                            get_post_type_object($post->post_type)->label);
                        $args['filter_array']['list']['objects']['items'][$post->post_type] = $postTypeLabel;
                        $args['filter_array']['json']['objects'][$post->post_type][] = $preparedID;

                        /* get post language */
                        $postLanguage = oes_get_post_language($preparedID) ?? false;
                        if (!$postLanguage && $parentID = get_parent_id($preparedID))
                            $postLanguage = oes_get_post_language($parentID) ?? false;

                        $preparedPosts[$occurrences][$titleForSorting . (10000 - $preparedID)] = [
                            'id' => $preparedID,
                            'title' => $titleDisplay,
                            'permalink' => get_permalink($preparedID),
                            'version' => $version,
                            'type' => $postTypeLabel,
                            'post_type' => $post->post_type,
                            'occurrences' => $occurrencesArray,
                            'occurrences-count' => $occurrences,
                            'language' => $postLanguage
                        ];
                        $postIDs[] = $preparedID;
                        $count++;
                    }
                }
            }
        }

    /* add ALL button */
    $consideredLanguage = (!isset($args['language']) || $args['language'] === 'all') ?
        $oes->main_language :
        $args['language'];
    if (!empty($args['filter_array']['list']['objects']['items']))
        $args['filter_array']['list']['objects']['items'] = array_merge(
            ['all' => $oes->theme_labels['archive__filter__all_button'][$consideredLanguage] ?? __('ALL', 'oes')],
            $args['filter_array']['list']['objects']['items']);

    return [
        'search_term' => $searchTerm,
        'characters' => $characters,
        'posts' => $preparedPosts,
        'post_ids' => $postIDs,
        'count' => $count,
        'filter' => $args['filter'],
        'filter_array' => $args['filter_array'],
        'language' => $args['language']
    ];
}