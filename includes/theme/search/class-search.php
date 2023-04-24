<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field_object;
use function OES\Versioning\get_parent_id;
use function OES\Versioning\get_version_field;

if (!class_exists('OES_Search') && class_exists('OES_Archive')) {

    /**
     * Class OES_Search
     *
     * This class prepares to display search results in the frontend theme.
     */
    class OES_Search extends OES_Archive
    {

        /** @var string $search_term The search term. */
        public string $search_term = '';

        /** @var string $search_term_id The search term id. */
        public string $search_term_id = '';

        /** @var array $considered_post_types The considered post types. */
        public array $considered_post_types = [];

        /** @var array $post_ids The prepared post IDs. */
        public array $post_ids = [];

        /** @var string $language_results The result post language. */
        public string $language_results = 'all';

        /** @var bool Indicating if the loop has been executed. */
        public bool $loop_execute = false;


        //Overwrite parent
        function set_parameters(array $args = [], string $language = 'language0'): void
        {

            /* set search term */
            global $s;
            $this->search_term = $s;

            /* get global OES instance parameter */
            $oes = OES();

            /* Set page language */
            $this->language = $language;

            /* Set page language */
            $this->language_results = $args['language_results'] ?? 'all';

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
        }


        //Overwrite parent
        function loop(): void
        {
            if (!$this->loop_execute) {

                /* check if specific post types */
                global $oes, $oes_redirect_archive;
                $oes_redirect_archive = $oes->search['redirect_archive'] ?? false;
                if ($oes_redirect_archive) $this->considered_post_types[] = $oes_redirect_archive;

                $this->loop_results();
                $this->loop_execute = true;
            }
        }


        /**
         * Loop through search results.
         */
        function loop_results(): void
        {
            if (have_posts())
                while (have_posts()) {
                    the_post();
                    $loopedPost = get_post(get_the_ID());

                    /* skip if not published or not considered post type */
                    if ('publish' == $loopedPost->post_status &&
                        (empty($this->considered_post_types) || in_array($loopedPost->post_type, $this->considered_post_types))) {

                        /* check if results are filtered by language */
                        if ($this->language_results === 'all') $this->prepared_ids[] = $loopedPost->ID;
                        else {

                            /* get post language */
                            $postLanguage = oes_get_post_language($loopedPost->ID) ?? false;
                            if (!$postLanguage && $parentID = get_parent_id($loopedPost->ID))
                                $postLanguage = oes_get_post_language($parentID) ?? false;

                            if (($postLanguage && $postLanguage === $this->language_results) || empty($postLanguage))
                                $this->prepared_ids[] = $loopedPost->ID;
                        }
                    }
                }
        }


        /**
         * Prepare results.
         *
         * @param array $args Additional parameters. Valid parameters are:
         *  'language'      : The considered language.
         *  'filter'        : The filter.
         *  'filter_array'  : The filter data.
         */
        function prepare_results(array $args = [])
        {
            /* get global OES instance parameter */
            $oes = OES();

            /* set default args */
            $args = array_merge(
                [
                    'language' => 'language0',
                    'filter' => [],
                    'filter_array' => []
                ],
                $_POST['search_params'], $args);


            /* do the loop */
            if (!empty($this->prepared_ids) && !empty($this->search_term))
                foreach ($this->prepared_ids as $preparedID)
                    $this->loop_post($preparedID);

            /* add ALL button */
            $consideredLanguage = (!isset($args['language']) || $args['language'] === 'all') ?
                $oes->main_language :
                $args['language'];
            if (!empty($args['filter_array']['list']['objects']['items']))
                $args['filter_array']['list']['objects']['items'] = array_merge(
                    ['all' => $oes->theme_labels['archive__filter__all_button'][$consideredLanguage] ?? __('ALL', 'oes')],
                    $args['filter_array']['list']['objects']['items']);

            $this->filter = $args['filter'];
            $this->filter_array = $args['filter_array'];
        }


        /**
         * Prepared post data.
         *
         * @param string $preparedID The post ID.
         */
        function loop_post(string $preparedID = '')
        {

            global $oes;

            /* skip if status is not 'publish' or not a considered post type */
            $post = get_post($preparedID);
            if ('publish' == $post->post_status &&
                (empty($this->considered_post_types)) || in_array($post->post_type, $this->considered_post_types)) {


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
                    if (!in_array($key, $this->characters)) $this->characters[] = $key;


                    /* get occurrences -------------------------------------------------------------------------------*/
                    $occurrences = 0;
                    $occurrencesArray = [];


                    /* title */
                    $occurrencesTitle = oes_get_highlighted_search($this->search_term, oes_get_display_title($preparedID));
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
                    $occurrencesContent = oes_get_highlighted_search($this->search_term, $post->post_content);
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
                                    $searchResultsFields = oes_get_highlighted_search($this->search_term, $field['value']);

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
                                            $oes->post_types[$post->post_type]['field_options'][$fieldKey]['label_translation_' . $this->language] ?:
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
                        $postTypeLabel = ($oes->post_types[$post->post_type]['theme_labels']['archive__header'][$this->language] ??
                            get_post_type_object($post->post_type)->label);
                        $args['filter_array']['list']['objects']['items'][$post->post_type] = $postTypeLabel;
                        $args['filter_array']['json']['objects'][$post->post_type][] = $preparedID;

                        /* get post language */
                        $postLanguage = oes_get_post_language($preparedID) ?? false;
                        if (!$postLanguage && $parentID = get_parent_id($preparedID))
                            $postLanguage = oes_get_post_language($parentID) ?? false;

                        $this->prepared_posts[$occurrences][$titleForSorting . (10000 - $preparedID)][] = [
                            'id' => $preparedID,
                            'postID' => $preparedID,
                            'title' => $titleDisplay,
                            'titleForDisplay' => $titleDisplay,
                            'permalink' => get_permalink($preparedID),
                            'version' => $version,
                            'type' => $postTypeLabel,
                            'post_type' => $post->post_type,
                            'occurrences' => $occurrencesArray,
                            'occurrences-count' => $occurrences,
                            'language' => $postLanguage
                        ];
                        $this->post_ids[] = $preparedID;
                        $this->count++;
                    }
                }
            }
        }


        //Overwrite parent
        function modify_prepare_row(array $row, array $object): array
        {
            $row['content'] = '';
            return $row;
        }
    }
}