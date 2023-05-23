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


        //Overwrite parent
        function modify_prepare_row(array $row, array $object): array
        {
            $row['content'] = '';
            return $row;
        }
    }
}