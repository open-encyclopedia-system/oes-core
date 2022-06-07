<?php


use function OES\ACF\oes_get_field_object;
use function OES\Versioning\get_version_field;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Search')) {

    /**
     * Class OES_Search
     *
     * This class prepares to display search results in the frontend theme.
     */
    class OES_Search
    {

        /** @var array $characters The characters for the alphabet filter (starting characters of item titles). */
        public array $characters = [];

        /** @var array $prepared_ids The prepared items id found during the loop. */
        public array $prepared_ids = [];

        /** @var array $prepared_posts The prepared items found during the loop. */
        public array $prepared_posts = [];

        /** @var int $count Count the prepared items. */
        public int $count = 0;

        /** @var string $language The page language. */
        public string $language = 'language0';

        /** @var string $label The page label (used e.g. for page title). */
        public string $label = '';

        /** @var array $filter The filter options. */
        public array $filter = [];

        /** @var array $filter_array The filter (includes filter options and related posts for display). */
        public array $filter_array = [];


        /**
         * OES_Search constructor.
         *
         * @param string $language The page language.
         * @param array $args Additional parameters (Relevant for child classes).
         */
        public function __construct(string $language = 'language0', array $args = [])
        {
            /* get global OES instance parameter */
            $oes = OES();

            /* Set page language */
            $this->language = $language;

            /* Set page label */
            $this->label = $oes->theme_labels['search__result__label'][$language] ?? __('Search', 'oes');

            /* Set filters */
            $filterArray = [];
            if (isset($oes->search['archive_filter']) && !empty($oes->search['archive_filter'])) {

                /* check for object filter */
                if (in_array('objects', $oes->search['archive_filter'])) {
                    $filterArray['objects'] = 'objects';
                    $this->filter_array['list']['objects']['label'] = __('Type', 'oes');
                }

                /* check for language filter */
                if (isset($oes->search['archive_filter']['languages'])) {
                    $filterArray['languages'] = 'languages';
                    $this->filter_array['list']['languages']['label'] = __('languages', 'oes');
                }
            }
            $this->filter = $filterArray;

            $this->loop_results();
        }


        /**
         * Loop through search results.
         */
        function loop_results()
        {
            if (have_posts())
                while (have_posts()) {
                    the_post();
                    $this->prepared_ids[] = get_the_ID();
                }
        }
    }
}


/**
 * Modify the WordPress search to scan also post metadata according to configurations.
 */
function oes_theme_modify_search()
{
    add_filter('posts_join', 'oes_search_join');
    add_filter('posts_where', 'oes_search_where_statement');
    add_filter('posts_distinct', 'oes_search_distinct');
}


/**
 * Add search in post meta table.
 *
 * @param string $join The sql join statement is passed by WordPress search.
 * @return string Returns modified join string.
 */
function oes_search_join(string $join): string
{


    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) {

        global $oes, $wpdb;

        /* add post meta */
        if (isset($oes->search['postmeta_fields']) && !empty($oes->search['postmeta_fields']))
            $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON (' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id) ';

        /* add taxonomies */
        if (isset($oes->search['taxonomies']) && !empty($oes->search['taxonomies']))
            $join .=
                ' LEFT JOIN ' . $wpdb->term_relationships . ' ON (' . $wpdb->posts . '.ID = ' . $wpdb->term_relationships . '.object_id) ' .
                ' LEFT JOIN ' . $wpdb->term_taxonomy . ' ON (' . $wpdb->term_relationships . '.term_taxonomy_id = ' . $wpdb->term_taxonomy . '.term_taxonomy_id) ' .
                ' LEFT JOIN ' . $wpdb->terms . ' ON (' . $wpdb->term_taxonomy . '.term_id = ' . $wpdb->terms . '.term_id) ';
    }

    return $join;
}


/**
 * Extend search in post meta value.
 *
 * @param string $where The sql where statement is passed by WordPress search.
 * @return string|string[]|null Returns modified where string.
 */
function oes_search_where_statement(string $where): string
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
            if(!empty($filterFields)) $prepareStatement .= " OR " .
                "((" . $wpdb->postmeta . ".meta_value LIKE $1) " .
                "AND (" . $wpdb->postmeta . ".meta_key NOT LIKE '" . '^_%' . "' ESCAPE '" . '^' . "') " .
                "AND " . $wpdb->postmeta . ".meta_key IN (" . implode(',', $filterFields) . "))";
        }

        if (isset($oes->search['taxonomies']) && !empty($oes->search['taxonomies'])) {

            $searchedTaxonomies = [];
            foreach ($oes->search['taxonomies'] as $taxonomyKey => $taxonomyFields)
                foreach ($taxonomyFields as $field)
                    if ($field === 'title')
                        $searchedTaxonomies[] = '"' . $taxonomyKey . '"';

            if (!empty($searchedTaxonomies)) {
                /* include search in terms */
                $prepareStatement .= " OR " .
                    "(" . $wpdb->term_taxonomy . ".taxonomy IN (" .  implode(',', $searchedTaxonomies) .
                    ") AND (" . $wpdb->terms . ".name LIKE $1)) ";
            }
        }

        /* hook search into existing search (title is arbitrary) */
        if (!empty($prepareStatement)){
            $replacement =  "(" . $wpdb->posts . ".post_title LIKE $1)" . $prepareStatement;
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
function oes_search_distinct(string $where): string
{
    /* Modify search if call from frontend. */
    if (is_search() && !is_admin()) return "DISTINCT";
    return $where;
}


/**
 * Get result objects for OES_Search object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'post-id'  : The post id.
 * @return array[] Returns search results.
 */
function oes_get_search_data(array $args = []): array
{
    global $oes_language, $oes_archive_count;
    $search = new OES_Search($oes_language);
    $oes_archive_count = $search->count;
    return (array)$search;
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
    $preparedPosts = [];
    $count = 0;
    if (!empty($args['prepared_ids']) && $searchTerm)
        foreach ($args['prepared_ids'] as $preparedID) {

            /* skip if status is not 'publish' */
            $post = get_post($preparedID);
            if ($post->post_status && 'publish' == $post->post_status) {


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
                                    '<span class="oes-dot-dot-dot"></span>';
                            $occurrences += $singleOccurrence['occurrences'];
                            $countParagraphs++;
                        }
                        $occurrencesArray['content'] = [
                            'th' => ['Content'],
                            'td' => [$occurrencesContentString]
                        ];
                    }

                    /* search fields */
                    if ($searchedFields)
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
                                        //TODO @nextRelease: more search field types
                                        break;

                                }
                            }


                    /* add information  ---------------------------------------------.--------------------------------*/
                    $version = false;
                    if (isset($oes->post_types[$post->post_type]['field_options']['field_oes_post_version']))
                        $version = get_version_field($preparedID) ?: false;
                    if (!empty($occurrences)) {

                        /* add post type to object type filter -------------------------------------------------------*/
                        $postTypeLabel = ($args['filter']['objects'][$post->post_type] ??
                            get_post_type_object($post->post_type)->label);
                        $args['filter_array']['list']['objects']['items'][$post->post_type] = $postTypeLabel;
                        $args['filter_array']['json']['objects'][$post->post_type][] = $preparedID;

                        $preparedPosts[$occurrences][$titleForSorting . (10000 - $preparedID)] = [
                            'id' => $preparedID,
                            'title' => $titleDisplay,
                            'permalink' => get_permalink($preparedID),
                            'version' => $version,
                            'type' => $postTypeLabel,
                            'post_type' => $post->post_type,
                            'occurrences' => $occurrencesArray,
                            'occurrences-count' => $occurrences
                        ];
                        $count++;
                    }
                }
            }
        }

    /* add ALL button */
    if (!empty($args['filter_array']['list']['objects']['items']))
        $args['filter_array']['list']['objects']['items'] = array_merge(
            ['all' => $oes->theme_labels['archive__filter__all_button'][$args['language']] ?? __('ALL', 'oes')],
            $args['filter_array']['list']['objects']['items']);

    return [
        'characters' => $characters,
        'posts' => $preparedPosts,
        'count' => $count,
        'filter' => $args['filter'],
        'filter_array' => $args['filter_array'],
        'language' => $args['language']
    ];
}