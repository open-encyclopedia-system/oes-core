<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_all_object_fields;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;
use function OES\Versioning\get_current_version_id;
use function OES\Versioning\get_version_post_type;
use function OES\Versioning\get_parent_id;

if (!class_exists('OES_Archive')) {


    /**
     * Class OES_Archive
     *
     * This class prepares an archive of post types for display in the frontend theme.
     */
    class OES_Archive
    {

        /** @var string $postType A string containing the post type. */
        public string $post_type = '';

        /** @var string $taxonomy A string containing the taxonomy. */
        public string $taxonomy = '';

        /** @var array $taxonomies A string containing the taxonomies that are considered for the post type archive. */
        public array $taxonomies = [];

        /** @var string $term The term slug. */
        public string $term = '';

        /** @var array $characters The characters for the alphabet filter (starting characters of item titles). */
        public array $characters = [];

        /** @var array $preparedPosts The prepared posts found during the loop. */
        public array $prepared_posts = [];

        /** @var array $preparedIDs The prepared ids found during the loop. */
        public array $prepared_ids = [];

        /** @var int $count Count the prepared posts. */
        public int $count = 0;

        /** @var bool $skip_post_object Skip OES Post construction. */
        public bool $skip_post_object = true;

        /** @var string $language The page language. */
        public string $language = '';

        /** @var string $label The page label (used e.g. for page title). */
        public string $label = '';

        /** @var array $filter The filter options (store for processing). */
        public array $filter = [];

        /** @var array $filter_array The filter (includes filter options and related posts for display). */
        public array $filter_array = [];

        /** @var bool $display_content Display the content of posts as part of the archive (e.g. for Glossary) */
        public bool $display_content = false;

        /** @var bool $title_is_link Display the titles of objects as link. */
        public bool $title_is_link = true;


        /**
         * OES_Archive constructor.
         *
         * @param array $args Parameters for the loop. Valid arguments are:
         *  post-type       : The post type.
         *  taxonomies      : The taxonomies.
         *  term            : A specific term.
         *  language        : Language filter.
         *  skip_post_object : Skip post type creation
         * @param string $language The page language.
         * @param bool $setParameters Set parameters.
         */
        public function __construct(array $args = [], string $language = 'language0', bool $setParameters = true)
        {
            if ($setParameters) $this->set_parameters($args, $args['language'] ?? $language);
            else {
                foreach ($args as $key => $value) if (property_exists($this, $key)) $this->{$key} = $value;
            }
            $this->loop();
        }


        /**
         * Set parameters
         *
         * @param array $args Parameters for the loop. Valid arguments are:
         *  post-type       : The post type.
         *  taxonomies      : The taxonomies.
         *  term            : A specific term.
         *  language        : Language filter.
         *  skip_post_object : Skip post type creation
         * @param string $language The page language.
         * @return void
         */
        public function set_parameters(array $args = [], string $language = 'language0'): void
        {
            /* Set post type */
            $postType = $args['post-type'] ?? '';
            if (empty($postType)) {
                global $post_type;
                if (!is_null($post_type)) $postType = $post_type;
            }
            $this->post_type = $postType;

            /* Set taxonomy */
            $thisTaxonomy = $args['taxonomy'] ?? '';
            if (empty($thisTaxonomy)) {
                global $taxonomy;
                if (!is_null($taxonomy)) $thisTaxonomy = $taxonomy;
            }
            $this->taxonomy = $thisTaxonomy;

            /* Set additional taxonomies */
            $this->taxonomies = $args['taxonomies'] ?? [];
            $this->term = $args['term'] ?? '';

            /* Set language */
            global $oes, $oes_language;
            $this->language = $args['language'] ?? ($oes_language ?? 'language0');

            /* Check if post type creation is skipped */
            $this->skip_post_object = $args['skip_post_object'] ?? true;

            /* Set filter for post type archives */
            $this->filter = $this->prepare_filter($args['post-type-for-filter'] ?? $this->post_type);

            /* Set label */
            $this->set_label();

            /* Check if archive is to be displayed as list (no single view, eg. glossary) */
            $this->display_content = (isset($oes->post_types[$this->post_type]['archive_on_single_page']) &&
                $oes->post_types[$this->post_type]['archive_on_single_page']);
        }


        /**
         * Set archive label.
         *
         * @return void
         */
        function set_label(): void
        {

            global $oes;
            $consideredLanguage = 'language0';
            if (!empty($this->post_type) || !empty($this->taxonomy)) {

                if (empty($this->label)) {

                    $consideredLanguage = $this->language === 'all' ? 'language0' : $this->language;
                    if (!empty($this->post_type)) {

                        if (!empty($consideredLanguage)) {
                            if (isset($oes->post_types[$this->post_type]['label_translations_plural'][$consideredLanguage]))
                                $this->label = $oes->post_types[$this->post_type]['label_translations_plural'][$consideredLanguage];
                            elseif (isset($oes->post_types[$this->post_type]['theme_labels']['archive__header'][$consideredLanguage]))
                                $this->label = $oes->post_types[$this->post_type]['theme_labels']['archive__header'][$consideredLanguage];
                        }

                        if (empty($this->label))
                            $this->label = $oes->post_types[$this->post_type]['label'] ?? 'Label missing';
                    } elseif (!empty($this->taxonomy)) {

                        if (!empty($consideredLanguage)) {
                            if (isset($oes->taxonomies[$this->taxonomy]['label_translations_plural'][$consideredLanguage]))
                                $this->label = $oes->taxonomies[$this->taxonomy]['label_translations_plural'][$consideredLanguage];
                            elseif (isset($oes->taxonomies[$this->taxonomy]['theme_labels']['archive__header'][$consideredLanguage]))
                                $this->label = $oes->taxonomies[$this->taxonomy]['theme_labels']['archive__header'][$consideredLanguage];
                        }

                        if (empty($this->label))
                            $this->label = $oes->taxonomies[$this->taxonomy]['label'] ?? 'Label missing';
                    }
                }
            }

            /* check if index page */
            global $oes_is_index;
            if ($oes_is_index && $oes->theme_index_pages[$oes_is_index]['slug'] !== 'hidden') {
                $this->label = sprintf('<div class="oes-index-archive-title"><span class="oes-index-archive-title-index">%s</span>%s</div>',
                    ($oes->theme_index_pages[$oes_is_index]['label'][$consideredLanguage] ?? __('Index', 'oes')),
                    (empty($this->label) ? '' : '<span class="oes-index-archive-title-post-type">' . $this->label . '</span>')
                );
            }

            /**
             * Filters the archive label.
             *
             * @param string $label The archive label.
             */
            if (has_filter('oes/theme_archive_label'))
                $this->label = apply_filters('oes/theme_archive_label', $this->label);

        }


        /**
         * Prepare results
         *
         * @return void
         */
        function loop(): void
        {
            $this->before_loop();
            $this->loop_results();
            $this->after_loop();
        }


        /**
         * Set additional objects for the archive.
         *
         * @param array $additionalObjects The additional objects. Valid values are post type and taxonomy names.
         * @param array $args Additional arguments.
         * @return void
         */
        public function set_additional_objects(array $additionalObjects = [], array $args = []): void
        {

            /* add filter for taxonomy archives */
            global $oes_is_index_page;
            if ($oes_is_index_page) {

                global $oes, $oes_is_index;
                if (!empty($oes->theme_index_pages[$oes_is_index]['archive_filter'])) {

                    if (isset($oes->theme_index_pages[$oes_is_index]['archive_filter']) &&
                        is_array($oes->theme_index_pages[$oes_is_index]['archive_filter'])) {
                        if (in_array('all', $oes->theme_index_pages[$oes_is_index]['archive_filter'])) {
                            $filterArray = [];
                            foreach ($oes->theme_index_pages[$oes_is_index]['objects'] as $objectType) {
                                if (taxonomy_exists($objectType) && !isset($filterArray['alphabet']))
                                    $filterArray['alphabet'] = true;
                                elseif (post_type_exists($objectType)) {
                                    $filterArray = array_merge($this->prepare_filter($objectType), $filterArray);
                                }
                            }
                            $this->filter = $filterArray;
                        } elseif (in_array('alphabet', $oes->theme_index_pages[$oes_is_index]['archive_filter'])) {
                            $this->filter = ['alphabet' => true];
                        }
                    }
                }
            } elseif (empty($this->post_type) &&
                empty($this->taxonomy) &&
                sizeof($additionalObjects) === 1 &&
                taxonomy_exists($additionalObjects[0])) {

                $filterArray = [];
                global $oes;
                if (isset($oes->taxonomies[$additionalObjects[0]]['archive_filter']) &&
                    !empty($oes->taxonomies[$additionalObjects[0]]['archive_filter']))
                    foreach ($oes->taxonomies[$additionalObjects[0]]['archive_filter'] as $filter) {

                        /* check for alphabet filter */
                        if ($filter === 'alphabet') $filterArray[$filter] = true;
                        //@oesDevelopment Implement more filter.
                    }

                $this->filter = $filterArray;
            }

            /* loop through objects and check for post type or taxonomy */
            if (!empty($additionalObjects))
                foreach ($additionalObjects as $object) {
                    if (post_type_exists($object)) {

                        if (sizeof($additionalObjects) == 1) $this->post_type = $object;

                        /* query posts */
                        $queryArgs = [
                            'post_type' => $object,
                            'post_status' => 'publish',
                            'posts_per_page' => -1
                        ];
                        if (isset($args['post__in'])) {
                            if (empty($args['post__in'])) continue;
                            $queryArgs['post__in'] = $args['post__in'];
                        }
                        if (isset($args['additional_query_args']))
                            $queryArgs = array_merge($queryArgs, $args['additional_query_args']);
                        $posts = new WP_Query($queryArgs);

                        /* loop through results */
                        if ($posts->have_posts())
                            while ($posts->have_posts()) {
                                $posts->the_post();
                                $post = get_post(get_the_ID());
                                $this->loop_results_post($post);
                            }
                    } elseif (taxonomy_exists($object)) {

                        if (sizeof($additionalObjects) == 1) $this->taxonomy = $object;

                        $args = array_merge(['hide_empty' => true], $args);

                        /* prepare query args */
                        $queryArgs = [
                            'taxonomy' => $object,
                            'hide_empty' => $args['hide_empty'] ?? true,
                            'childless' => $args['childless'] ?? true];
                        if (isset($args['parent'])) $queryArgs['parent'] = $args['parent'];

                        /* query terms */
                        $terms = get_terms($queryArgs);

                        /* loop through results */
                        if ($terms) foreach ($terms as $term) $this->loop_results_term($term);
                    }
                }


            /* set label */
            $this->label = '';
            $this->set_label();
        }


        /**
         * Optional additional action before loop.
         *
         * @return void
         */
        function before_loop(): void
        {
        }


        /**
         * Loop through results and prepare for display.
         *
         * @return void
         */
        function loop_results(): void
        {

            /* loop through all posts --------------------------------------------------------------------------------*/
            if (!empty($this->taxonomies)) {

                /* list terms of taxonomy */
                if (!$this->post_type) {
                    foreach ($this->taxonomies as $tax)
                        if ($terms = get_terms(['taxonomy' => $tax]))
                            foreach ($terms as $term) $this->loop_results_term($term);
                } /* list posts connected to taxonomy */
                else {

                    /* prepare query (check if term is given) */
                    if (empty($this->term)) $queryArgs = [
                        'post_type' => $this->post_type,
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    ];
                    else $queryArgs = [
                        'post_type' => $this->post_type,
                        'tax_query' => [[
                            'taxonomy' => $this->taxonomies[0], //@oesDevelopment multiple taxonomies?
                            'field' => 'slug',
                            'terms' => $this->term
                        ]]
                    ];
                    $posts = new WP_Query($queryArgs);

                    /* loop through results */
                    if ($posts->have_posts())
                        while ($posts->have_posts()) {
                            $posts->the_post();
                            $post = get_post(get_the_ID());
                            $this->loop_results_post($post);
                        }
                }
            } elseif (!empty($this->post_type))
                if (is_archive()) {
                    if (have_posts())
                        while (have_posts()) {
                            the_post();
                            $post = get_post(get_the_ID());
                            $this->loop_results_post($post);
                        }
                } else {
                    if (post_type_exists($this->post_type)) {

                        /* query posts */
                        $posts = new WP_Query([
                            'post_type' => $this->post_type,
                            'post_status' => 'publish',
                            'posts_per_page' => -1
                        ]);

                        /* loop through results */
                        if ($posts->have_posts())
                            while ($posts->have_posts()) {
                                $posts->the_post();
                                $post = get_post(get_the_ID());
                                $this->loop_results_post($post);
                            }
                    }
                }
        }


        /**
         * Optional additional action after loop.
         *
         * @return void
         */
        function after_loop(): void
        {
        }


        /**
         * Prepare post data to be displayed.
         *
         * @param WP_Post $post The post.
         * @return void
         */
        function loop_results_post(WP_Post $post): void
        {

            /* skip if status is not 'publish' */
            if ('publish' == $post->post_status) {

                /* prepare data */
                $parentID = false;
                $languageDependent = true;

                /* check for OES Post action */
                if (!$this->skip_post_object) {

                    /* skip if post is hidden */
                    $postInstance = class_exists($this->post_type) ? new $this->post_type($post->ID) :
                        new OES_Post($post->ID, $this->language);

                    /* check if post is hidden */
                    if (method_exists($postInstance, 'check_if_post_is_hidden'))
                        if ($postInstance->check_if_post_is_hidden()) return;
                } else {

                    /* check for current version */
                    $currentVersion = false;
                    $parentID = get_parent_id($post->ID);
                    if ($post->ID && $parentID)
                        $currentVersion = get_current_version_id($parentID) ?? false;

                    /* check for language */
                    $postLanguage = $this->get_object_language($post->ID, $parentID);
                    if (!$postLanguage) $languageDependent = false;
                    elseif (!empty($this->language) && $this->language !== 'all')
                        if ($this->language !== $postLanguage) return;

                    if ($currentVersion && $currentVersion != $post->ID) return;
                }

                $skipPost = false;

                /**
                 * Filter if post is to be skipped.
                 *
                 * @param string $post_type The post type.
                 * @param WP_Post $post The post.
                 * @param bool $skipPost The boolean if post is to be skipped.
                 */
                if (has_filter('oes/theme_archive-loop-' . $this->post_type))
                    $skipPost = apply_filters('oes/theme_archive-loop-' . $this->post_type, $post, $skipPost);
                if ($skipPost) return;

                /* get post data -------------------------------------------------------------------------------------*/
                $titleDisplay = oes_get_display_title(false, ['option' => 'title_archive_display', 'language' => $this->language]);

                /* get first character of displayed title */
                $titleForSorting = oes_get_display_title(false, ['option' => 'title_sorting_display', 'language' => $this->language]);
                $titleForSorting = oes_replace_umlaute($titleForSorting);
                $key = strtoupper(substr($titleForSorting, 0, 1));

                /* check if non-alphabetic key */
                if (!in_array($key, range('A', 'Z'))) $key = 'other';

                /* prepare array with existing first characters of displayed posts */
                if (!in_array($key, $this->characters)) $this->characters[] = $key;

                /* check for filter ----------------------------------------------------------------------------------*/
                if (!empty($this->filter))
                    foreach ($this->filter as $filter => $filterParams) {

                        /* skip alphabet filter */
                        if ($filter === 'alphabet') continue;

                        /* check if parent post is source */
                        $relevantPost = ($parentID && $filterParams['parent']) ? $parentID : $post->ID;

                        /* check if taxonomy */
                        if (isset($filterParams['type'])) {
                            if ($filterParams['type'] === 'taxonomy') {
                                $termList = get_the_terms($relevantPost, $filter);
                                if (!empty($termList)) foreach ($termList as $term)
                                    if (!isset($this->filter_array['json'][$filter][$term->term_id]) ||
                                        !in_array($post->ID, $this->filter_array['json'][$filter][$term->term_id])) {

                                        /* check for other languages */
                                        $termName = '';
                                        if (OES()->main_language !== $this->language)
                                            $termName = get_term_meta($term->term_id, 'name_' . $this->language, true);

                                        $this->filter_array['list'][$filter]['items'][$term->term_id] = empty($termName) ? $term->name : $termName;
                                        $this->filter_array['json'][$filter][$term->term_id][] = $post->ID;
                                    }
                            }/* check if taxonomy */
                            elseif ($filterParams['type'] === 'post_type') {

                                /* get relationship fields */
                                $relationshipFields = get_all_object_fields($post->post_type, ['relationship', 'post_object']);
                                if (!empty($relationshipFields))
                                    foreach ($relationshipFields as $relationshipFieldKey => $relationshipField)
                                        if (in_array($filter, $relationshipField['post_type'] ?? [])) {

                                            $fieldValue = oes_get_field($relationshipFieldKey, $relevantPost);
                                            if (!empty($fieldValue))
                                                foreach ($fieldValue as $singleFieldValue)
                                                    if (!isset($this->filter_array['json'][$filter][$singleFieldValue->ID]) ||
                                                        !in_array($post->ID, $this->filter_array['json'][$filter][$singleFieldValue->ID])) {
                                                        $this->filter_array['list'][$filter]['items'][$singleFieldValue->ID] =
                                                            oes_get_display_title($singleFieldValue->ID, ['language' => $this->language, 'option' => 'title_sorting']);
                                                        $this->filter_array['json'][$filter][$singleFieldValue->ID][] = $post->ID;
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
                                                    !in_array($post->ID, $this->filter_array['json'][$filter][$singlePostID])) {
                                                    $this->filter_array['list'][$filter]['items'][$singlePostID] =
                                                        oes_get_display_title($singlePostID, ['language' => $this->language, 'option' => 'title_sorting']);
                                                    $this->filter_array['json'][$filter][$singlePostID][] = $post->ID;
                                                }
                                            }
                                            break;

                                        case 'taxonomy' :
                                            $field = is_array($field) ? $field : [$field];
                                            foreach ($field as $termID)
                                                if (!isset($this->filter_array['json'][$filter][$termID]) ||
                                                    !in_array($post->ID, $this->filter_array['json'][$filter][$termID])) {
                                                    $this->filter_array['list'][$filter]['items'][$termID] =
                                                        oes_get_display_title(get_term($termID, get_field_object($filter)['taxonomy']),
                                                            ['language' => $this->language]);
                                                    $this->filter_array['json'][$filter][$termID][] = $post->ID;
                                                }

                                            break;

                                        case 'radio':
                                        case 'select':
                                            if (!isset($this->filter_array['json'][$filter][$field]) ||
                                                !in_array($post->ID, $this->filter_array['json'][$filter][$field])) {
                                                $this->filter_array['list'][$filter]['items'][$field] =
                                                    oes_get_field_object($filter)['choices'][$field] ?? $field;
                                                $this->filter_array['json'][$filter][$field][] = $post->ID;
                                            }
                                            break;

                                        case 'default' :
                                        default:
                                            if (!isset($this->filter_array['json'][$filter][$field]) ||
                                                !in_array($post->ID, $this->filter_array['json'][$filter][$field])) {

                                                /* make sure the key is javascript compatible */
                                                $cleanKey = hash('md5', $field);
                                                $this->filter_array['list'][$filter]['items'][$cleanKey] = $field;
                                                $this->filter_array['json'][$filter][$cleanKey][] = $post->ID;
                                            }
                                    }
                                }
                        }
                    }

                /* add information  ----------------------------------------------------------------------------------*/
                $this->prepared_posts[$key][strtolower($titleForSorting . $post->ID)][] = [
                    'postID' => $post->ID,
                    'title' => oes_get_display_title($post->ID, ['language' => $this->language]),
                    'titleForDisplay' => $titleDisplay,
                    'permalink' => get_permalink()
                ];
                $this->prepared_ids[] = $post->ID;
                $this->count++;


                if (has_action('oes/theme_archive_loop_results_post'))
                    do_action('oes/theme_archive_loop_results_post', $post->ID);
            }
        }


        /**
         * Prepare term to be displayed.
         *
         * @param WP_Term $term The term.
         * @return void
         */
        function loop_results_term(WP_Term $term): void
        {

            /* get term data -----------------------------------------------------------------------------------------*/

            /* get first character */
            $titleDisplay = oes_get_display_title($term, ['option' => 'title_archive_display', 'language' => $this->language]);

            /* get first character of displayed title */
            $titleForSorting = oes_get_display_title($term, ['option' => 'title_sorting_display', 'language' => $this->language]);
            $titleForSorting = oes_replace_umlaute($titleForSorting);

            /* get first character of displayed title */
            $key = strtoupper(substr($titleForSorting, 0, 1));

            /* check if non-alphabetic key */
            if (!in_array($key, range('A', 'Z'))) $key = 'other';

            /* prepare array with existing first characters of displayed posts */
            if (!in_array($key, $this->characters)) $this->characters[] = $key;

            /* add information  --------------------------------------------------------------------------------------*/
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
        function get_data_as_table(bool $archiveData = true, array $args = []): array
        {

            /* prepare table -----------------------------------------------------------------------------------------*/
            $tableArray = [];

            /* loop through array ------------------------------------------------------------------------------------*/
            ksort($this->prepared_posts);
            foreach ($this->prepared_posts as $firstCharacter => $objectContainer) {

                /* sort array */
                ksort($objectContainer);

                /* loop through single object */
                $table = [];
                foreach ($objectContainer as $objects)
                    foreach ($objects as $object) {

                        /* gather information ------------------------------------------------------------------------*/
                        $tableData = [];
                        $title = 'Title missing';
                        $permalink = $object['permalink'];
                        $hidePost = false;
                        $additionalInformation = '';

                        /* differentiate between post and term */
                        if ($postID = $object['postID'] ?? false) {

                            /* check if parent post type */
                            $postType = get_post($postID) ? get_post_type($postID) : false;
                            $versionPostType = get_version_post_type($postType);
                            $post = false;

                            if ($versionPostType) {

                                /* get current version */
                                $versionID = get_current_version_id($postID);
                                $title = oes_get_display_title($versionID, ['language' => $this->language]);
                                $permalink = get_permalink($versionID);

                                /* check for OES Post action */
                                if ($archiveData) {

                                    /* get post */
                                    $post = class_exists($versionPostType) ?
                                        new $versionPostType($versionID, $this->language) :
                                        new OES_Post($versionID, $this->language);
                                    $additionalInformation = $post->additional_archive_data;
                                }
                            } else {

                                /* prepare title */
                                $title = $object['titleForDisplay'] ?: $object['title'];

                                /* get post */
                                $postType = get_post($postID) ? get_post_type($postID) : false;

                                /* check for OES Post action */
                                if ($archiveData) {

                                    $post = $postType && class_exists($postType) ?
                                        new $postType($postID, $this->language) :
                                        new OES_Post($postID, $this->language);
                                    $additionalInformation = $post->additional_archive_data;
                                }
                            }

                            /* check if post is hidden */
                            if ($post && method_exists($post, 'check_if_post_is_hidden'))
                                $hidePost = $post->check_if_post_is_hidden();

                            /* get data to be displayed in dropdown table */
                            $tableData = $archiveData ? $post->get_archive_data() : [];

                        } elseif ($object['termID'] ?? false) {

                            /* prepare title */
                            $title = $object['titleForDisplay'] ?: $object['title'];
                        }

                        /* add information to table ------------------------------------------------------------------*/
                        if (!$hidePost) {

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

                /* add table to array --------------------------------------------------------------------------------*/
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
        function modify_prepare_row(array $row, array $object): array
        {
            return $row;
        }


        /**
         * Get language of current object during looping prepared posts.
         *
         * @param mixed $postID The post ID.
         * @param mixed $parentID The parent ID.
         * @return mixed The language or false.
         */
        function get_object_language($postID, $parentID)
        {
            return oes_get_post_language($parentID ?? $postID);
        }


        /**
         * Prepare the filter object.
         *
         * @param string $postType The post type.
         * @return array Return the filter object.
         */
        function prepare_filter(string $postType): array
        {

            /* get language */
            $consideredLanguage = (empty($this->language) || $this->language === 'all') ?
                'language0' :
                $this->language;

            global $oes;
            $filterArray = [];
            if (isset($oes->post_types[$postType]['archive_filter']) &&
                !empty($oes->post_types[$postType]['archive_filter']))
                foreach ($oes->post_types[$postType]['archive_filter'] as $filter) {

                    /* check for alphabet filter */
                    if ($filter === 'alphabet') $filterArray[$filter] = true;
                    else {

                        /* check for further information */
                        $filterArgs = ['parent' => false];
                        $filterKey = $filter;
                        if (oes_starts_with($filter, 'parent__')) {
                            $filterKey = substr($filter, 8);
                            $filterArgs['parent'] = true;
                        }

                        if (oes_starts_with($filterKey, 'taxonomy__')) {
                            $filterKey = substr($filterKey, 10);
                            if (taxonomy_exists($filterKey)) {
                                $filterArgs['type'] = 'taxonomy';
                                $this->filter_array['list'][$filterKey]['label'] =
                                    $oes->taxonomies[$filterKey]['label_translations_plural'][$consideredLanguage] ??
                                    get_taxonomy($filterKey)->label;
                            }
                        } elseif (oes_starts_with($filterKey, 'post_type__')) {
                            $filterKey = substr($filterKey, 11);
                            if (post_type_exists($filterKey)) {
                                $filterArgs['type'] = 'post_type';
                                $this->filter_array['list'][$filterKey]['label'] =
                                    ($oes->post_types[$filterKey]['label_translations_plural'][$consideredLanguage] ?? $filterKey);
                            }
                        } elseif (oes_starts_with($filterKey, 'parent_taxonomy__')) {
                            $filterKey = substr($filterKey, 17);
                            if (taxonomy_exists($filterKey)) {
                                $filterArgs['type'] = 'taxonomy';
                                $filterArgs['parent'] = true;
                                $this->filter_array['list'][$filterKey]['label'] =
                                    $oes->taxonomies[$filterKey]['label_translations_plural'][$consideredLanguage] ??
                                    get_taxonomy($filterKey)->label;
                            }
                        } elseif ($field = oes_get_field_object($filterKey)) {
                            $filterArgs['type'] = 'field';
                            $filterArgs['field-type'] = $field['type'] ?? 'Type missing';

                            /* get global configuration for this language */
                            $fieldConfiguration = [];
                            if (!empty($this->post_type)) {
                                if ($filterArgs['parent'] &&
                                    isset($oes->post_types[$this->post_type]['parent']) &&
                                    $oes->post_types[$this->post_type]['parent'])
                                    $fieldConfiguration = $oes->post_types[$oes->post_types[$this->post_type]['parent']]['field_options'][$filterKey] ?? false;
                                else
                                    $fieldConfiguration = $oes->post_types[$this->post_type]['field_options'][$filterKey] ?? false;
                            } elseif (!empty($this->taxonomy)) $fieldConfiguration = $oes->taxonomy[$this->taxonomy]['field_options'][$filterKey] ?? false;

                            $this->filter_array['list'][$filterKey]['label'] = $fieldConfiguration['label_translation_' . $consideredLanguage] ?? ($field['label'] ?? $filterKey);
                        }

                        $filterArray[$filterKey] = $filterArgs;
                    }
                }

            return $filterArray;
        }

    }
}