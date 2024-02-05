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

        /** @var bool $display_content Display the content of posts as part of the archive (e.g. for glossary) */
        public bool $display_content = false;


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

            /* set considered language */
            global $oes_language;
            $this->filtered_language = $args['language'] ?? ($oes_language ?? 'language0');

            $this->set_parameters($args);
            $this->set_additional_parameters($args);
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
            $this->label = $this->get_object_label();

            /* check if archive is part of index */
            global $oes, $oes_is_index, $oes_language;
            if ($oes_is_index && $oes->theme_index_pages[$oes_is_index]['slug'] !== 'hidden') {
                $pageTitle = $oes->theme_index_pages[$oes_is_index]['label'][$oes_language] ?? __('Index', 'oes');
                $this->page_title = trim($pageTitle . (empty($this->label) ? '' : ' | ' . $this->label));
                $this->label = '<div class="oes-index-archive-title">' .
                    '<span class="oes-index-archive-title-index">' . $pageTitle . '</span>' .
                    (empty($this->label) ?
                        '' :
                        '<span class="oes-index-archive-title-post-type">' . $this->label . '</span>') .
                    '</div>';
            } else $this->page_title = $this->label;
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
            /* skip if status is not 'publish' */
            if ('publish' == $post->post_status) {

                /* check for current version and return early if more current version exists */
                $parentID = Versioning\get_parent_id($post->ID);
                if ($parentID &&
                    ((Versioning\get_current_version_id($parentID) ?? false) != $post->ID)) return;

                /* check for language and return early if it does not match criteria */
                if($this->filtered_language !== 'all' && !empty($this->filtered_language)) {
                    $postLanguage = oes_get_post_language($parentID ?? $post->ID);
                    if ($postLanguage &&
                        $postLanguage !== 'all' &&
                        $this->filtered_language !== $postLanguage) return;
                }

                /* prepare title */
                $titleDisplay = oes_get_display_title_archive();

                /* get first character of displayed title for character array */
                $titleForSorting = oes_get_display_title_sorting();
                $key = strtoupper(substr($titleForSorting, 0, 1));
                if (!in_array($key, range('A', 'Z'))) $key = 'other';
                if (!in_array($key, $this->characters)) $this->characters[] = $key;

                /* check for filter */
                if (!empty($this->filter)) $this->add_filter($post->ID, $parentID);

                /* add information */
                $this->prepared_posts[$key][strtolower($titleForSorting . $post->ID)][] = [
                    'postID' => $post->ID,
                    'title' => oes_get_display_title($post->ID),
                    'titleForDisplay' => $titleDisplay,
                    'permalink' => get_permalink()
                ];
                $this->prepared_ids[] = $post->ID;
                $this->count++;


                /**
                 * Additional processing.
                 *
                 * @param int $post->ID The post ID.
                 */
                do_action('oes/theme_archive_loop_results_post', $post->ID);
            }
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

            /* loop through the filter */
            foreach ($this->filter as $filter => $filterParams) {

                /* skip alphabet filter */
                if ($filter === 'alphabet') continue;

                /* check if parent post is source */
                $relevantPost = ($parentID && $filterParams['parent']) ? $parentID : $postID;

                /* check if taxonomy */
                if (isset($filterParams['type'])) {
                    if ($filterParams['type'] === 'taxonomy') {
                        $termList = get_the_terms($relevantPost, $filter);
                        if (!empty($termList)) foreach ($termList as $term)
                            if (!isset($this->filter_array['json'][$filter][$term->term_id]) ||
                                !in_array($postID, $this->filter_array['json'][$filter][$term->term_id])) {

                                /* check for other languages */
                                $termName = '';
                                if ($this->filtered_language !== 'language0')
                                    $termName = get_term_meta(
                                        $term->term_id,
                                        'name_' . $this->filtered_language,
                                        true);

                                $this->filter_array['list'][$filter]['items'][$term->term_id] = empty($termName) ?
                                    $term->name :
                                    $termName;
                                $this->filter_array['json'][$filter][$term->term_id][] = $postID;
                            }
                    }/* check if taxonomy */
                    elseif ($filterParams['type'] === 'post_type') {

                        /* get relationship fields */
                        $relationshipFields = oes_get_all_object_fields(
                            get_post_type($postID),
                            ['relationship', 'post_object']);
                        if (!empty($relationshipFields))
                            foreach ($relationshipFields as $relationshipFieldKey => $relationshipField)
                                if (in_array($filter, $relationshipField['post_type'] ?? [])) {

                                    $fieldValue = oes_get_field($relationshipFieldKey, $relevantPost);
                                    if (!empty($fieldValue))
                                        foreach ($fieldValue as $singleFieldValue)
                                            if (!isset($this->filter_array['json'][$filter][$singleFieldValue->ID]) ||
                                                !in_array($postID,
                                                    $this->filter_array['json'][$filter][$singleFieldValue->ID])) {
                                                $this->filter_array['list'][$filter]['items'][$singleFieldValue->ID] =
                                                    oes_get_display_title_archive($singleFieldValue->ID);
                                                $this->filter_array['json'][$filter][$singleFieldValue->ID][] = $postID;
                                            }
                                }
                    } /* check if field */
                    elseif ($filterParams['type'] === 'field' && $field = oes_get_field($filter, $relevantPost))
                        if (!empty($field)) {

                            switch ($filterParams['field-type'] ?? 'default') {

                                case 'relationship':
                                    foreach ($field as $singlePost) {
                                        $singlePostID = $singlePost->ID ?? $singlePost;
                                        if (!isset($this->filter_array['json'][$filter][$singlePostID]) ||
                                            !in_array($postID, $this->filter_array['json'][$filter][$singlePostID])) {
                                            $this->filter_array['list'][$filter]['items'][$singlePostID] =
                                                oes_get_display_title_archive($singlePostID);
                                            $this->filter_array['json'][$filter][$singlePostID][] = $postID;
                                        }
                                    }
                                    break;

                                case 'taxonomy' :
                                    $field = is_array($field) ? $field : [$field];
                                    foreach ($field as $termID)
                                        if (!isset($this->filter_array['json'][$filter][$termID]) ||
                                            !in_array($postID, $this->filter_array['json'][$filter][$termID])) {
                                            $this->filter_array['list'][$filter]['items'][$termID] =
                                                oes_get_display_title(
                                                    get_term($termID, get_field_object($filter)['taxonomy']));
                                            $this->filter_array['json'][$filter][$termID][] = $postID;
                                        }

                                    break;

                                case 'radio':
                                case 'select':
                                    if (!isset($this->filter_array['json'][$filter][$field]) ||
                                        !in_array($postID, $this->filter_array['json'][$filter][$field])) {
                                        $this->filter_array['list'][$filter]['items'][$field] =
                                            oes_get_field_object($filter)['choices'][$field] ?? $field;
                                        $this->filter_array['json'][$filter][$field][] = $postID;
                                    }
                                    break;

                                case 'default' :
                                default:
                                    if (!isset($this->filter_array['json'][$filter][$field]) ||
                                        !in_array($postID, $this->filter_array['json'][$filter][$field])) {

                                        /* make sure the key is javascript compatible */
                                        $cleanKey = hash('md5', $field);
                                        $this->filter_array['list'][$filter]['items'][$cleanKey] = $field;
                                        $this->filter_array['json'][$filter][$cleanKey][] = $postID;
                                    }
                            }
                        }
                }
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

            /* get first character */
            $titleDisplay = oes_get_display_title_archive($term);

            /* get first character of displayed title for character array */
            $titleForSorting = oes_get_display_title_sorting($term);
            $key = strtoupper(substr($titleForSorting, 0, 1));
            if (!in_array($key, range('A', 'Z'))) $key = 'other';
            if (!in_array($key, $this->characters)) $this->characters[] = $key;

            /* add information */
            $this->prepared_posts[$key][strtolower($titleForSorting . $term->term_taxonomy_id)][] = [
                'termID' => $term->term_id,
                'termTaxonomyID' => $term->term_taxonomy_id,
                'title' => $titleDisplay,
                'titleForDisplay' => $titleDisplay,
                'permalink' => get_term_link($term)
            ];
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

            ksort($this->prepared_posts);
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
                        $additionalInformation = '';

                        /* differentiate between post and term */
                        if ($postID = $object['postID'] ?? false) {

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
                                            new $versionPostType($versionID) :
                                            new OES_Post($versionID);
                                        $additionalInformation = $versionPost->additional_archive_data;
                                    }
                                } else $versionExists = false;
                            } else {

                                $title = $object['titleForDisplay'] ?: $object['title'];

                                /* check for OES Post action */
                                if ($archiveData) {

                                    $postType = get_post($postID) ? get_post_type($postID) : false;
                                    $versionPost = $postType && class_exists($postType) ?
                                        new $postType($postID) :
                                        new OES_Post($postID);
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

                        } elseif ($object['termID'] ?? false) {
                            $title = $object['titleForDisplay'] ?: $object['title'];
                        }

                        /* add information to table */
                        if (!$hidePost && $versionExists) {

                            $prepareRowData = [
                                'id' => $postID,
                                'title' => $title,
                                'permalink' => $permalink,
                                'data' => $tableData,
                                'additional' => $additionalInformation
                            ];

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