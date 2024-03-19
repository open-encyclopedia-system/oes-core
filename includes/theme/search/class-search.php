<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Versioning\get_parent_id;

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

        /** @var array $considered_post_types The considered post types. */
        public array $considered_post_types = [];

        /** @var bool Indicating if the loop has been executed. */
        public bool $loop_execute = false;


        //Overwrite parent
        public function set_parameters(array $args = []): void
        {
            global $s;
            $this->search_term = $s;
        }


        //Overwrite parent
        public function set_label(): void
        {
            $this->label = oes_get_label('search__result__label', 'Search');
            $this->page_title = $this->label . ($this->search_term ? (': ' . $this->search_term) : '');
        }


        //Overwrite parent
        public function loop(): void
        {
            if (!$this->loop_execute) {

                /* check for specific post types */
                global $oes;
                $this->considered_post_types = $oes->search['redirect_archive'] ?? [];

                $this->loop_results();
                $this->loop_execute = true;
            }
        }


        // Overwrite parent
        public function loop_results(): void
        {
            if (have_posts())
                while (have_posts()) {
                    the_post();
                    $loopedPost = get_post(get_the_ID());

                    /* skip if not published or not considered post type */
                    if ('publish' == $loopedPost->post_status &&
                        (empty($this->considered_post_types) ||
                            in_array($loopedPost->post_type, $this->considered_post_types))) {

                        /* check if results are filtered by language */
                        if ($this->filtered_language === 'all') $this->prepared_ids[] = $loopedPost->ID;
                        else {

                            /* get post language */
                            $postLanguage = oes_get_post_language($loopedPost->ID) ?? false;
                            if (!$postLanguage && $parentID = get_parent_id($loopedPost->ID))
                                $postLanguage = oes_get_post_language($parentID) ?? false;

                            if (($postLanguage && $postLanguage === $this->filtered_language) ||
                                empty($postLanguage))
                                $this->prepared_ids[] = $loopedPost->ID;
                        }
                    }
                }
        }


        //Overwrite
        public function get_data_as_table(bool $archiveData = true, array $args = []): array
        {

            /* prepare table */
            global $oes, $oes_language;
            $tableArray = [];

            /* get results in current language */
            if (isset($this->prepared_posts['all']) ||
                isset($this->prepared_posts[$oes_language]))
                $tableArray = $this->get_data_as_table_single(
                    array_merge($this->prepared_posts[$oes_language] ?? [], $this->prepared_posts['all'] ?? []),
                    '',
                    $archiveData,
                    $args);

            /* get results in other languages */
            if (sizeof($oes->languages) > 1)
                foreach ($oes->languages as $languageKey => $languageData)
                    if (isset($this->prepared_posts[$languageKey]) &&
                        $languageKey !== $oes_language &&
                        $languageKey !== 'all') {
                        $otherLanguageTableArray = $this->get_data_as_table_single(
                            $this->prepared_posts[$languageKey],
                            $languageData['label'],
                            $archiveData,
                            $args);
                        if (!empty($otherLanguageTableArray))
                            $tableArray = array_merge($tableArray, $otherLanguageTableArray);
                    }

            return $tableArray;
        }


        /**
         * Get data for a specific language.
         *
         * @param array $data The table data.
         * @param string $languageLabel The language label.
         * @param bool $archiveData Include archive data (dropdown).
         * @param array $args Additional parameters (class inheritances)
         *
         * @return array Return an array with prepared data.
         */
        public function get_data_as_table_single(
            array  $data = [],
            string $languageLabel = '',
            bool   $archiveData = true,
            array  $args = []): array
        {

            /* prepare table */
            $tableArray = [];
            foreach ($data as $objectContainer) {

                $table = [];
                $postTypeLabel = '';
                krsort($objectContainer);
                foreach ($objectContainer as $occurrenceCount => $posts) {

                    /* sort array */
                    ksort($posts);

                    /* loop through single object */
                    foreach ($posts as $object)
                        if (!empty($object['occurrences'])) {

                            $title = ($object['title'] ?? '');
                            if (isset($object['occurrences']['title']['value'])) {
                                $title = $object['occurrences']['title']['value'];
                                unset($object['occurrences']['title']);
                            }

                            /* gather information */
                            $prepareRowData = [
                                'id' => $object['id'],
                                'title' => $title,
                                'permalink' => $object['permalink'] ?? false,
                                'language' => $object['language'] ?? 'language0',
                                'data' => $archiveData ? ($object['occurrences'] ?? []) : [],
                                'additional' => '<span class="oes-search-title-occurrences">' .
                                    $occurrenceCount . ' ' .
                                    _n(oes_get_label('search__result__occurrence', 'Occurrence'),
                                        oes_get_label('search__result__occurrences', 'Occurrences'),
                                        $occurrenceCount,
                                        'oes') .
                                    '</span>'
                            ];

                            /* modify prepared row */
                            $table[] = $this->modify_prepare_row($prepareRowData, $object);

                            /* prepare post type label */
                            if (empty($postTypeLabel)) $postTypeLabel = $object['type'];
                        }
                }

                /* add table to array */
                if (!empty($table))
                    $tableArray[] = [
                        'character' => $postTypeLabel,
                        'additional' => $languageLabel ?
                            '<span class="oes-additional-info">' . $languageLabel . '</span>' :
                            '',
                        'table' => $table
                    ];
            }

            return $tableArray;
        }


        /**
         * Get search results.
         *
         * @return void
         */
        public function get_results(): void
        {
            $results = oes_search_get_results();
            foreach ([
                         'characters',
                         'prepared_posts',
                         'count',
                         'filter',
                         'filter_array'] as $param)
                if (!empty($results[$param] ?? '')) $this->$param = $results[$param];

        }
    }
}