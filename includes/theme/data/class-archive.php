<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use OES\Versioning as Versioning;

if (!class_exists('OES_Archive')) {


    /**
     * Class OES_Archive
     *
     * This class prepares an archive of post types, taxonomy, index page or search page for display in the frontend.
     */
    class OES_Archive
    {

        /** @var array $characters The characters for the alphabet filter (starting characters of item titles). */
        public array $characters = [];

        /** @var array $preparedPosts The prepared posts found during the loop. */
        public array $prepared_posts = [];

        /** @var array $preparedIDs The prepared ids found during the loop. */
        public array $prepared_ids = [];

        /** @var int $count Count the prepared posts. */
        public int $count = 0;

        /** @var string $filtered_language The displayed objects must match the filtered language. */
        public string $filtered_language = '';

        /** @var string $label The page label (used e.g. for title). */
        public string $label = '';

        /** @var string $page_title The page title (used e.g. for tab title). */
        public string $page_title = '';

        /** @var array $filter The filter options (store for processing). */
        public array $filter = [];

        /** @var array $filter_array The filter (includes filter options for display). */
        public array $filter_array = [];

        /** @var bool $title_is_link Display the titles of objects as link. */
        public bool $title_is_link = true;

        /** @var string|bool $is_index The archive is part of the index. */
        public $is_index = false;

        /** @var bool $display_content Display the content of posts as part of the archive (e.g. for glossary) */
        public bool $display_content = false;

        /** @var array $query_parameters Additional parameters for WP_Query */
        public array $query_parameters = [];

        /**
         * OES_Archive constructor.
         *
         * @param array $args Parameters for the loop.
         */
        public function __construct(array $args = [])
        {
            /**
             * Filters if archive loop arguments.
             *
             * @param array $args The arguments.
             */
            $args = apply_filters('oes/theme_archive_args', $args);

            global $oes_language;
            $this->filtered_language = $args['language'] ?? ($oes_language ?? 'language0');

            $this->set_parameters($args);
            $this->set_additional_parameters($args);
            $this->set_query_parameters($args);
            $this->set_label();
            $this->filter = $this->prepare_filter($args);
            $this->loop();
        }

        /**
         * Set parameters
         *
         * @param array $args Parameters for the loop.
         * @return void
         */
        public function set_parameters(array $args = []): void
        {
        }

        /**
         * Set query parameters
         *
         * @param array $args Parameters for WP_Query.
         * @return void
         */
        public function set_query_parameters(array $args = []): void
        {
            $this->query_parameters = $args['query_parameters'] ?? [];
        }

        /**
         * Set additional parameters.
         *
         * @param array $args Additional parameters.
         * @return void
         */
        public function set_additional_parameters(array $args = []): void
        {
        }

        /**
         * Set archive label.
         *
         * @return void
         */
        public function set_label(): void
        {
            $label = $this->get_object_label();

            $this->label = $label;
            $this->page_title = $label;

            if (!$this->is_index_archive()) {
                return;
            }

            global $oes, $oes_is_index, $oes_language;

            $pageTitle = (
                $oes->theme_index_pages[$oes_is_index]['label'][$oes_language]
                ?? __('Index', 'oes')
            );

            $this->page_title = $this->build_page_title(
                $pageTitle,
                $label
            );

            $this->label = $this->build_archive_label(
                $pageTitle,
                $label
            );
        }

        /**
         * Check whether archive belongs to index.
         */
        protected function is_index_archive(): bool
        {
            global $oes, $oes_is_index;

            return (
                !empty($oes_is_index) &&
                ($oes->theme_index_pages[$oes_is_index]['slug'] ?? '')
                !== 'hidden'
            );
        }

        /**
         * Build page title.
         */
        protected function build_page_title(
            string $pageTitle,
            string $label
        ): string {

            return empty($label)
                ? $pageTitle
                : $pageTitle . ' | ' . $label;
        }

        /**
         * Build archive label HTML.
         */
        protected function build_archive_label(
            string $pageTitle,
            string $label
        ): string {

            $html = '<div class="oes-index-archive-title">';

            $html .= sprintf(
                '<span class="oes-index-archive-title-index">%s</span>',
                esc_html($pageTitle)
            );

            if (!empty($label)) {
                $html .= sprintf(
                    '<span class="oes-index-archive-title-post-type">%s</span>',
                    esc_html($label)
                );
            }

            $html .= '</div>';

            return $html;
        }

        /**
         * Prepare facet filter.
         *
         * @param array $args Parameters for the loop.
         * @return array Return the filter.
         */
        public function prepare_filter(array $args): array
        {
            return [];
        }

        /**
         * Get the object label, e.g. post type label.
         *
         * @return string
         */
        public function get_object_label(): string
        {
            return '';
        }

        /**
         * Prepare results
         *
         * @return void
         */
        public function loop(): void
        {
            $this->before_loop();
            $this->loop_objects();
            $this->after_loop();
        }

        /**
         * Optional additional action before loop.
         *
         * @return void
         */
        public function before_loop(): void
        {
        }

        /**
         * Loop through results and prepare for display.
         *
         * @return void
         */
        public function loop_objects(): void
        {
        }

        /**
         * Optional additional action after loop.
         *
         * @return void
         */
        public function after_loop(): void
        {
        }

        /**
         * Prepare post data to be displayed.
         *
         * @param WP_Post $post The post.
         * @return void
         */
        public function loop_results_post(WP_Post $post): void
        {
            if ('publish' != $post->post_status) {
                return;
            }

            $parentID = Versioning\get_parent_id($post->ID);

            if (!$this->is_current_version($post->ID, $parentID)) {
                return;
            }

            if (!$this->matches_language($post->ID, $parentID)) {
                return;
            }

            $titleDisplay = oes_get_display_title_archive($post);
            $titleForSorting = oes_get_display_title_sorting($post);

            $alphabetKey = $this->get_key_for_alphabet_filter($titleForSorting);
            $this->add_character($alphabetKey);

            if (!empty($this->filter)) {
                $this->add_filter($post->ID, $parentID);
            }

            $this->add_prepared_post(
                $alphabetKey,
                $titleForSorting,
                $titleDisplay,
                $post
            );

            $this->prepared_ids[] = $post->ID;

            $this->count++;

            /**
             * Additional processing.
             *
             * @param int $post - >ID The post ID.
             */
            do_action('oes/theme_archive_loop_results_post', $post->ID);
        }

        /**
         * Check whether this is the current version.
         */
        protected function is_current_version(int $postID, $parentID): bool
        {

            if (!$parentID) {
                return true;
            }

            return (
                (Versioning\get_current_version_id($parentID) ?? false)
                === $postID
            );
        }

        /**
         * Check language filter.
         */
        protected function matches_language(int $postID, $parentID): bool
        {

            if ($this->filtered_language === 'all' || empty($this->filtered_language)) {
                return true;
            }

            $postLanguage = oes_get_post_language($parentID ?: $postID);

            if (!$postLanguage || $postLanguage === 'all') {
                return true;
            }

            return $this->filtered_language === $postLanguage;
        }

        /**
         * Get first character from title, collecting for alphabet filter.
         *
         * @param string $title The post title.
         * @return string Return the alphabet key.
         */
        protected function get_key_for_alphabet_filter(string $title): string
        {
            $key = strtoupper(substr(remove_accents($title), 0, 1));
            if (!in_array($key, range('A', 'Z'))) {
                $key = 'other';
            }
            return $key;
        }

        /**
         * Add character to character list.
         */
        protected function add_character(string $key): void
        {
            if (!in_array($key, $this->characters, true)) {
                $this->characters[] = $key;
            }
        }

        /**
         * Add prepared post data.
         */
        protected function add_prepared_post(
            string $alphabetKey,
            string $titleForSorting,
            string $titleDisplay,
            WP_Post $post
        ): void {

            $sortingKey = strtolower(
                $titleForSorting . $post->ID
            );

            $this->prepared_posts[$alphabetKey][$sortingKey][] = [
                'postID'          => $post->ID,
                'title'           => oes_get_display_title($post->ID),
                'titleForDisplay' => $titleDisplay,
                'permalink'       => get_permalink($post)
            ];
        }

        /**
         * Add prepared post data.
         */
        protected function add_prepared_term(
            string $alphabetKey,
            string $titleForSorting,
            string $titleDisplay,
            WP_Term $term
        ): void {

            $sortingKey = strtolower(
                $titleForSorting . $term->term_taxonomy_id
            );

            $this->prepared_posts[$alphabetKey][$sortingKey][] = [
                'termID' => $term->term_id,
                'postID' => $term->term_id,
                'termTaxonomyID' => $term->term_taxonomy_id,
                'title' => $titleDisplay,
                'titleForDisplay' => $titleDisplay,
                'permalink' => get_term_link($term)
            ];
        }

        /**
         * Add filter.
         *
         * @param int $postID The post ID.
         * @param int|bool $parentID The parent ID.
         * @return void
         */
        public function add_filter(int $postID, $parentID = false): void
        {
            foreach ($this->filter as $filter => $params) {

                if ($this->check_if_skip_filter($filter, $params)) {
                    continue;
                }

                $relevantPost = $this->get_relevant_post($postID, $parentID, $params);

                switch ($params['type']) {

                    case 'taxonomy':
                        $this->handle_taxonomy_filter(
                            $filter,
                            $relevantPost,
                            $postID
                        );
                        break;

                    case 'post_type':
                        $this->handle_post_type_filter(
                            $filter,
                            $relevantPost,
                            $postID
                        );
                        break;

                    case 'field':
                        $this->handle_field_filter(
                            $filter,
                            $params,
                            $relevantPost,
                            $postID
                        );
                        break;
                }
            }
        }

        /**
         * Skip invalid filters.
         */
        protected function check_if_skip_filter(string $filter, $filterParams): bool
        {

            if ($filter === 'alphabet') {
                return true;
            }

            if (!is_array($filterParams) || empty($filterParams['type'] ?? null)) {
                return true;
            }

            return false;
        }

        /**
         * Get relevant post ID.
         */
        protected function get_relevant_post(int $postID, int $parentID, array $filterParams): int
        {
            return ($parentID && $filterParams['parent']) ? $parentID : $postID;
        }

        /**
         * Handle taxonomy filter.
         */
        protected function handle_taxonomy_filter(
            string $filter,
            int    $relevantPost,
            int    $postID
        ): void
        {

            $termList = get_the_terms($relevantPost, $filter);

            if (empty($termList)) {
                return;
            }

            foreach ($termList as $term) {

                $termName = '';

                if ($this->filtered_language !== 'language0') {
                    $termName = get_term_meta(
                        $term->term_id,
                        'name_' . $this->filtered_language,
                        true
                    );
                }

                $this->add_filter_item(
                    $filter,
                    $term->term_id,
                    $termName ?: $term->name,
                    $postID
                );
            }
        }

        /**
         * Handle post type filter.
         */
        protected function handle_post_type_filter(
            string $filter,
            int    $relevantPost,
            int    $postID
        ): void
        {

            $relationshipFields = oes_get_all_object_fields(
                get_post_type($postID),
                ['relationship', 'post_object']
            );

            if (empty($relationshipFields)) {
                return;
            }

            foreach ($relationshipFields as $relationshipFieldKey => $relationshipField) {

                if (!in_array($filter, $relationshipField['post_type'] ?? [])) {
                    continue;
                }

                $fieldValue = oes_get_field(
                    $relationshipFieldKey,
                    $relevantPost
                );

                if (empty($fieldValue)) {
                    continue;
                }

                foreach ($fieldValue as $singleFieldValue) {

                    $this->add_filter_item(
                        $filter,
                        $singleFieldValue->ID,
                        oes_get_display_title_archive($singleFieldValue->ID),
                        $postID
                    );
                }
            }
        }

        /**
         * Handle field filter.
         */
        protected function handle_field_filter(
            string $filter,
            array  $filterParams,
            int    $relevantPost,
            int    $postID
        ): void
        {

            $field = oes_get_field($filter, $relevantPost, false);

            if (empty($field)) {
                return;
            }

            switch ($filterParams['field-type'] ?? 'default') {

                case 'relationship':
                    $this->handle_relationship_field(
                        $filter,
                        $field,
                        $postID
                    );
                    break;

                case 'taxonomy':
                    $this->handle_taxonomy_field(
                        $filter,
                        $field,
                        $postID
                    );
                    break;

                case 'radio':
                case 'select':
                    $this->handle_select_field(
                        $filter,
                        $field,
                        $postID
                    );
                    break;

                case 'date_picker':
                    $this->handle_date_picker_field(
                        $filter,
                        $field,
                        $postID
                    );
                    break;

                default:
                    $this->handle_default_field(
                        $filter,
                        $field,
                        $postID
                    );
            }
        }

        /**
         * Handle relationship field.
         */
        protected function handle_relationship_field(
            string $filter,
                   $field,
            int    $postID
        ): void
        {

            foreach ($this->normalize_array($field) as $singlePost) {

                $singlePostID = $singlePost->ID ?? $singlePost;

                $this->add_filter_item(
                    $filter,
                    $singlePostID,
                    oes_get_display_title_archive($singlePostID),
                    $postID
                );
            }
        }

        /**
         * Handle taxonomy field.
         */
        protected function handle_taxonomy_field(
            string $filter,
                   $field,
            int    $postID
        ): void
        {

            $fieldObject = oes_get_field_object($filter);
            $taxonomy = $fieldObject['taxonomy'] ?? '';

            foreach ($this->normalize_array($field) as $termID) {

                $term = get_term($termID, $taxonomy);

                $this->add_filter_item(
                    $filter,
                    $termID,
                    oes_get_display_title($term),
                    $postID
                );
            }
        }

        /**
         * Handle select/radio field.
         */
        protected function handle_select_field(
            string $filter,
                   $field,
            int    $postID
        ): void
        {

            $fieldObject = oes_get_field_object($filter);
            $choices = $fieldObject['choices'] ?? [];

            foreach ($this->normalize_array($field) as $singleField) {

                $this->add_filter_item(
                    $filter,
                    $singleField,
                    $choices[$singleField] ?: $singleField,
                    $postID
                );
            }
        }

        /**
         * Handle date picker field.
         */
        protected function handle_date_picker_field(
            string $filter,
                   $field,
            int    $postID
        ): void
        {

            $year = date('Y', strtotime($field));;

            $this->add_filter_item(
                $filter,
                $year,
                $year,
                $postID
            );
        }

        /**
         * Handle default field.
         */
        protected function handle_default_field(
            string $filter,
                   $field,
            int    $postID
        ): void
        {

            $multipleValues = explode(';', (string)$field);

            foreach ($multipleValues as $singleValue) {

                $cleanValue = trim((string)$singleValue);

                if ($cleanValue === '') {
                    continue;
                }

                $cleanKey = (
                    is_numeric($cleanValue) &&
                    (string)(int)$cleanValue === $cleanValue
                )
                    ? (int)$cleanValue
                    : md5($cleanValue);

                $this->add_filter_item(
                    $filter,
                    $cleanKey,
                    $cleanValue,
                    $postID
                );
            }
        }

        /**
         * Normalize value to array.
         */
        protected function normalize_array($value): array
        {
            if (empty($value)) {
                return [];
            }

            return is_array($value)
                ? $value
                : [$value];
        }

        /**
         * Add filter item.
         */
        protected function add_filter_item(
            string $filter,
                   $key,
            string $label,
            int    $postID
        ): void
        {

            if (!isset($this->filter_array['json'][$filter][$key])) {
                $this->filter_array['json'][$filter][$key] = [];
            }

            if (!in_array(
                $postID,
                $this->filter_array['json'][$filter][$key],
                true
            )) {

                $this->filter_array['list'][$filter]['items'][$key] = $label;

                $this->filter_array['json'][$filter][$key][] = $postID;
            }
        }

        /**
         * Prepare term to be displayed.
         *
         * @param WP_Term $term The term.
         * @return void
         */
        public function loop_results_term(WP_Term $term): void
        {
            $titleDisplay = oes_get_display_title_archive($term);
            $titleForSorting = oes_get_display_title_sorting($term);

            $alphabetKey = $this->get_key_for_alphabet_filter($titleForSorting);
            $this->add_character($alphabetKey);

            $this->add_prepared_term(
                $alphabetKey,
                $titleForSorting,
                $titleDisplay,
                $term
            );

            $this->count++;
        }

        /**
         * Get archive data as table to be displayed.
         *
         * @param bool $archiveData Include archive data (dropdown).
         * @param array $args Additional parameters (for class inheritances)
         *
         * @return array Return an array with prepared data.
         */
        public function get_data_as_table(bool $archiveData = true, array $args = []): array
        {

            /* prepare table */
            $tableArray = [];

            $this->sort_prepared_posts();
            foreach ($this->prepared_posts as $firstCharacter => $objectContainer) {

                /* loop through single object */
                $table = [];
                ksort($objectContainer);
                foreach ($objectContainer as $objects)
                    foreach ($objects as $object) {

                        /* gather information */
                        $tableData = [];
                        $title = 'Title missing';
                        $permalink = $object['permalink'];
                        $hidePost = false;
                        $versionExists = true;
                        $isTerm = false;
                        $additionalInformation = '';

                        /* differentiate between post and term */
                        if ($postID = $object['termID'] ?? false) {
                            $title = $object['titleForDisplay'] ?: $object['title'];
                            $isTerm = true;

                            if ($archiveData) {
                                $termWP = get_term($postID);
                                $taxonomy = $termWP ? $termWP->taxonomy : false;
                                $term = $taxonomy && class_exists($taxonomy) ?
                                    new $taxonomy($postID) :
                                    new OES_Term($postID);
                                $tableData = $term->get_archive_data();
                            }
                        } elseif ($postID = $object['postID'] ?? false) {

                            /* check if parent post type */
                            $versionPost = false;
                            $postType = get_post($postID) ? get_post_type($postID) : false;
                            if ($versionPostType = Versioning\get_version_post_type($postType)) {

                                /* get current version */
                                if ($versionID = Versioning\get_current_version_id($postID)) {

                                    $title = oes_get_display_title($versionID);
                                    $permalink = get_permalink($versionID);

                                    /* check for OES Post action */
                                    if ($archiveData) {

                                        /* get post */
                                        $versionPost = class_exists($versionPostType) ?
                                            new $versionPostType($versionID, '', ['skip' => true]) :
                                            new OES_Post($versionID, '', ['skip' => true]);
                                        $additionalInformation = $versionPost->additional_archive_data;
                                    }
                                } else $versionExists = false;
                            } else {

                                $title = $object['titleForDisplay'] ?: $object['title'];

                                /* check for OES Post action */
                                if ($archiveData) {

                                    $versionPost = $postType && class_exists($postType) ?
                                        new $postType($postID, '', ['skip' => true]) :
                                        new OES_Post($postID, '', ['skip' => true]);
                                    $additionalInformation = $versionPost->additional_archive_data;
                                }
                            }

                            if ($versionExists) {

                                /* check if post is hidden */
                                if ($versionPost && method_exists($versionPost, 'check_if_post_is_hidden'))
                                    $hidePost = $versionPost->check_if_post_is_hidden();

                                /* get data to be displayed in dropdown table */
                                $tableData = $archiveData ? $versionPost->get_archive_data() : [];
                            }

                        }

                        /* add information to table */
                        if (!$hidePost && $versionExists) {

                            if ($isTerm) {
                                $postLanguage = 'all';
                            } else {
                                $postLanguage = oes_get_post_language($postID);
                                if (empty($postLanguage)) {
                                    $postLanguage = 'all';
                                }
                            }

                            $prepareRowData = $this->modify_prepared_row_data([
                                'id' => $postID,
                                'title' => $title,
                                'permalink' => $permalink,
                                'data' => $tableData,
                                'additional' => $additionalInformation,
                                'language' => $postLanguage
                            ]);

                            /* check if content should be added */
                            if ($this->display_content)
                                $prepareRowData['content'] = get_post($postID)->post_content ?? '';

                            /* modify prepared row */
                            $table[] = $this->modify_prepare_row($prepareRowData, $object);
                        }
                    }

                /* add table to array */
                if ($firstCharacter == 'other') $firstCharacter = '#';
                if (!empty($table)) $tableArray[] = ['character' => $firstCharacter, 'table' => $table];
            }

            return $tableArray;
        }


        /**
         * Modify the prepared row data.
         *
         * @param array $args
         * @return array
         */
        public function modify_prepared_row_data(array $args = []): array
        {
            return $args;
        }


        /**
         * Sort the prepared posts.
         *
         * @return void
         */
        public function sort_prepared_posts(): void
        {
            ksort($this->prepared_posts);
        }


        /**
         * Modify prepared data row.
         *
         * @param array $row The data row.
         * @param array $object The row object.
         * @return array Return modified data row.
         */
        public function modify_prepare_row(array $row, array $object): array
        {
            return $row;
        }
    }
}