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


        /**
         * OES_Archive constructor.
         *
         * @param array $args Parameters for the loop. Valid arguments are:
         *  post-type       : The post type.
         *  taxonomies      : The taxonomies.
         *  term            : A specific term.
         *  language        : Language filter.
         * skip_post_object : Skip post type creation
         */
        public function __construct(array $args = [])
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
            global $oes_language;
            $this->language = $args['language'] ?? $oes_language;

            /* Check if post type creation is skipped */
            $this->skip_post_object = $args['skip_post_object'] ?? true;

            /* Set filter for post type archives */
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
                                $this->filter_array['list'][$filterKey]['label'] = get_taxonomy($filterKey)->label;
                            }
                        } elseif (oes_starts_with($filterKey, 'post_type__')) {
                            $filterKey = substr($filterKey, 11);
                            if (post_type_exists($filterKey)) {
                                $filterArgs['type'] = 'post_type';
                                $this->filter_array['list'][$filterKey]['label'] =
                                    (isset($oes->post_types[$filterKey]['label']) ?
                                        $oes->post_types[$filterKey]['label'] :
                                        $filterKey);
                            }
                        } elseif (oes_starts_with($filterKey, 'parent_taxonomy__')) {
                            $filterKey = substr($filterKey, 17);
                            if (taxonomy_exists($filterKey)) {
                                $filterArgs['type'] = 'taxonomy';
                                $filterArgs['parent'] = true;
                                $this->filter_array['list'][$filterKey]['label'] = get_taxonomy($filterKey)->label;
                            }
                        } elseif ($field = oes_get_field_object($filterKey)) {
                            $filterArgs['type'] = 'field';
                            $filterArgs['field-type'] = $field['type'] ?? 'Type missing';
                            $this->filter_array['list'][$filterKey]['label'] = $field['label'];
                        }

                        $filterArray[$filterKey] = $filterArgs;
                    }
                }

            $this->filter = $filterArray;

            /* Set label */
            if (empty($this->label)) {

                /* check if index page */
                global $oes_is_index;
                if ($oes_is_index)
                    $this->label = $oes->theme_index['label'] ?? __('Index', 'oes');
                else {
                    $consideredLanguage = $this->language === 'all' ? 'language0' : $this->language;
                    if (!empty($this->post_type))
                        $this->label = (!empty($consideredLanguage) &&
                            isset($oes->post_types[$postType]['theme_labels']['archive__header'][$consideredLanguage])) ?
                            $oes->post_types[$postType]['theme_labels']['archive__header'][$consideredLanguage] :
                            $oes->post_types[$postType]['label'] ?? 'Label missing';
                    elseif (!empty($this->taxonomy))

                        if (empty($consideredLanguage))
                            $this->label = $oes->taxonomies[$thisTaxonomy]['label'] ?? 'Label missing';
                        else
                            $this->label =
                                $oes->taxonomies[$thisTaxonomy]['theme_labels']['archive__header'][$consideredLanguage] ??
                                ($oes->taxonomies[$thisTaxonomy]['label_translations'][$consideredLanguage] ??
                                    ($oes->taxonomies[$thisTaxonomy]['label'] ?? 'Label missing'));
                }
            }

            /**
             * Filters the archive label.
             *
             * @param string $label The archive label.
             */
            if (has_filter('oes/theme_archive_label'))
                $this->label = apply_filters('oes/theme_archive_label', $this->label);


            /* Check if archive is to be displayed as list (no single view, eg. glossary) */
            $this->display_content = (isset($oes->post_types[$postType]['archive_on_single_page']) &&
                $oes->post_types[$postType]['archive_on_single_page']);

            $this->before_loop();

            /* do the loop */
            $this->loop_results();
        }


        /**
         * Set additional objects for the archive.
         *
         * @param array $additionalObjects The additional objects. Valid values are post type and taxonomy names.
         */
        public function set_additional_objects(array $additionalObjects = [], array $args = [])
        {

            /* loop through objects and check for post type or taxonomy */
            if (!empty($additionalObjects))
                foreach ($additionalObjects as $object) {
                    if (post_type_exists($object)) {

                        /* query posts */
                        $posts = new WP_Query([
                            'post_type' => $object,
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
                    } elseif (taxonomy_exists($object)) {

                        $args = array_merge(['hide_empty' => true], $args);

                        /* query terms */
                        $terms = get_terms(['taxonomy' => $object, 'hide_empty' => $args['hide_empty']]);

                        /* loop through results */
                        if ($terms) foreach ($terms as $term) $this->loop_results_term($term);
                    }
                }
        }


        /**
         * Optional additional action before loop.
         */
        function before_loop()
        {
        }


        /**
         * Loop through results and prepare for display.
         */
        function loop_results()
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
                            'taxonomy' => $this->taxonomies[0], //TODO @nextRelease : multiple taxonomies?
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
         * Prepare post data to be displayed.
         *
         * @param WP_Post $post The post.
         */
        function loop_results_post(WP_Post $post)
        {

            /* skip if status is not 'publish' */
            if ('publish' == $post->post_status) {

                /* prepare data */
                $parentID = false;

                /* check for OES Post action */
                if (!$this->skip_post_object) {

                    /* skip if post is hidden */
                    $postInstance = class_exists($this->post_type) ? new $this->post_type(get_the_ID()) :
                        new OES_Post(get_the_ID());

                    /* check if post is hidden */
                    if (method_exists($postInstance, 'check_if_post_is_hidden'))
                        if ($postInstance->check_if_post_is_hidden()) return;
                } else {

                    /* check for current version */
                    $currentVersion = false;
                    if ($post->ID && $parentID = get_parent_id($post->ID))
                        $currentVersion = get_current_version_id($parentID) ?? false;

                    /* check for language */
                    $postLanguage = oes_get_post_language($parentID ?? $post->ID);
                    if ($postLanguage && !empty($this->language) && $this->language !== 'all')
                        if ($this->language !== $postLanguage) return;

                    if ($currentVersion && $currentVersion != $post->ID) return;
                }


                /* get post data -------------------------------------------------------------------------------------*/
                $titleDisplay = oes_get_display_title(false, ['option' => 'title_archive_display']);

                /* get first character of displayed title */
                $titleForSorting = oes_get_display_title(false, ['option' => 'title_sorting_display']);
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
                        $relevantPost = ($parentID && $filterParams['parent']) ? $parentID : get_the_ID();

                        /* check if taxonomy */
                        if ($filterParams['type'] === 'taxonomy') {
                            $termList = get_the_terms($relevantPost, $filter);
                            if (!empty($termList)) foreach ($termList as $term) {
                                $this->filter_array['list'][$filter]['items'][$term->term_id] = $term->name;
                                $this->filter_array['json'][$filter][$term->term_id][] = get_the_ID();
                            }
                        }/* check if taxonomy */
                        elseif ($filterParams['type'] === 'post_type') {

                            /* get relationship fields */
                            $relationshipFields = get_all_object_fields($post->post_type, ['relationship', 'post_object']);
                            if (!empty($relationshipFields))
                                foreach ($relationshipFields as $relationshipFieldKey => $relationshipField)
                                    if (in_array($filter, $relationshipField['post_type'] ?? [])) {

                                        $fieldValue = \OES\ACF\oes_get_field($relationshipFieldKey, $relevantPost);
                                        if (!empty($fieldValue))
                                            foreach ($fieldValue as $singleFieldValue)
                                                if (!isset($this->filter_array['json'][$filter][$singleFieldValue->ID]) ||
                                                    !in_array(get_the_ID(), $this->filter_array['json'][$filter][$singleFieldValue->ID])) {
                                                    $this->filter_array['list'][$filter]['items'][$singleFieldValue->ID] =
                                                        oes_get_display_title($singleFieldValue->ID);
                                                    $this->filter_array['json'][$filter][$singleFieldValue->ID][] = get_the_ID();
                                                }
                                    }
                        } /* check if field */
                        elseif ($filterParams['type'] === 'field' && $field = oes_get_field($filter, $relevantPost))
                            if (!empty($field)) {

                                switch ($filterParams['field-type'] ?? 'default') {

                                    case 'relationship':
                                        foreach ($field as $singlePost) {
                                            $this->filter_array['list'][$filter]['items'][$singlePost->ID] =
                                                oes_get_display_title($singlePost->ID);
                                            $this->filter_array['json'][$filter][$singlePost->ID][] = get_the_ID();
                                        }
                                        break;

                                    case 'taxonomy' :
                                        foreach ($field as $termID) {
                                            $this->filter_array['list'][$filter]['items'][$termID] =
                                                oes_get_display_title(get_term($termID, get_field_object($filter)['taxonomy']));
                                            $this->filter_array['json'][$filter][$termID][] = get_the_ID();
                                        }
                                        break;

                                    case 'radio':
                                    case 'select':
                                        $this->filter_array['list'][$filter]['items'][$field] =
                                            oes_get_field_object($filter)['choices'][$field] ?? $field;
                                        $this->filter_array['json'][$filter][$field][] = get_the_ID();
                                        break;

                                    case 'default' :
                                    default:
                                        $this->filter_array['list'][$filter]['items'][$field] = $field;
                                        $this->filter_array['json'][$filter][$field][] = get_the_ID();
                                }
                            }
                    }

                /* add information  ----------------------------------------------------------------------------------*/
                $this->prepared_posts[$key][strtolower($titleForSorting . get_the_ID())][] = [
                    'postID' => get_the_ID(),
                    'title' => oes_get_display_title(get_the_ID()),
                    'titleForDisplay' => $titleDisplay,
                    'permalink' => get_permalink()
                ];
                $this->count++;
            }
        }


        /**
         * Prepare term to be displayed.
         *
         * @param WP_Term $term The term.
         */
        function loop_results_term(WP_Term $term)
        {

            /* get term data -----------------------------------------------------------------------------------------*/

            /* get first character */
            $titleDisplay = oes_get_display_title($term, ['option' => 'title_archive_display']);

            /* get first character of displayed title */
            $titleForSorting = oes_get_display_title($term, ['option' => 'title_sorting_display']);
            $titleForSorting = oes_replace_umlaute($titleForSorting);

            /* get first character of displayed title */
            $key = strtoupper(substr($titleForSorting, 0, 1));

            /* check if non-alphabetic key */
            if (!in_array($key, range('A', 'Z'))) $key = 'other';

            /* prepare array with existing first characters of displayed posts */
            if (!in_array($key, $this->characters)) $this->characters[] = $key;

            /* add information  --------------------------------------------------------------------------------------*/
            $this->prepared_posts[$key][$titleForSorting . $term->term_taxonomy_id][] = [
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
                                $title = oes_get_display_title($versionID);
                                $permalink = get_permalink($versionID);

                                /* check for OES Post action */
                                if ($archiveData) {

                                    /* get post */
                                    $post = class_exists($versionPostType) ?
                                        new $versionPostType($versionID) :
                                        new OES_Post($versionID);
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
                                        new $postType($postID) :
                                        new OES_Post($postID);
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

                            $table[] = $prepareRowData;
                        }
                    }

                /* add table to array --------------------------------------------------------------------------------*/
                if ($firstCharacter == 'other') $firstCharacter = '#';
                if (!empty($table)) $tableArray[] = ['character' => $firstCharacter, 'table' => $table];
            }

            return $tableArray;
        }
    }
}


/**
 * Get archive parameters and data for post type and additional objects.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'execute-loop'  : Execute the loop even if a cache exists.
 * @return array[] Return archive parameters and data for post type and additional objects.
 */
function oes_get_archive_data(array $args = []): array
{

    /* merge args */
    $args = array_merge([
        'execute-loop' => false
    ], $args);

    /* get global parameters */
    global $oes, $post_type, $oes_additional_objects, $oes_is_index;

    /* check if index */
    if (!$oes_is_index) $oes_is_index = in_array($post_type, $oes->theme_index['objects'] ?? []);

    /* check for cache */
    $cache = false;
    if ($post_type) $cache = get_option('oes_cache_' . $post_type);
    elseif (sizeof($oes_additional_objects) > 1) $cache = get_option('oes_cache_index');
    elseif (isset($oes_additional_objects[0])) $cache = get_option('oes_cache_' . $oes_additional_objects[0]);

    if ($cache && !$args['execute-loop']) $archive = unserialize($cache);
    else {

        /**
         * Filters if archive loop uses additional arguments.
         *
         * @param array $additionalArgs The additional arguments.
         */
        $additionalArgs = [];
        if (has_filter('oes/theme_archive_additional_args'))
            $additionalArgs = apply_filters('oes/theme_archive_additional_args', $additionalArgs);

        /* execute the loop */
        $oesArchive = new OES_Archive();
        if (!empty($oes_additional_objects)) $oesArchive->set_additional_objects($oes_additional_objects, $additionalArgs);
        $archive = [
            'archive' => (array)$oesArchive,
            'table-array' => $oesArchive->get_data_as_table()
        ];
    }

    /* prepare archive count */
    global $oes_filter, $oes_archive_count;
    $oes_filter = $archive['archive']['filter_array'];

    $oes_archive_count = (($archive['archive']['characters'] && sizeof($archive['archive']['characters']) > 0 &&
        $archive['archive']['count']) ?
        $archive['archive']['count'] :
        false);

    return $archive;
}


/**
 * Get the alphabet filter (list of all characters with filter functions).
 *
 * @param array $characters All starting characters of archive items.
 *
 * @return array The alphabet list
 */
function oes_archive_get_alphabet_filter(array $characters): array
{

    /* prepare alphabet array */
    $alphabetArray = [];
    $allAlphabet = array_merge(range('A', 'Z'), ['other']);

    /* first entry */
    $alphabetArray[] = [
        'style' => ' class="active-li"',
        'content' => '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="all" ' .
            'onClick="oesApplyAlphabetFilter(this)">' .
            __('ALL', 'oes') . '</a>'
    ];

    /* loop through entries */
    foreach ($allAlphabet as $firstCharacter) {

        /* check if last key */
        $styleText = '';

        /* check if not part of alphabet */
        if ($firstCharacter == 'other') $firstCharacterDisplay = '#';
        else {
            $firstCharacterDisplay = $firstCharacter;

            /* make sure it's uppercase */
            $firstCharacter = strtoupper($firstCharacter);
        }

        /* check if in list */
        if (in_array($firstCharacter, $characters)) {

            /* add character to list */
            $alphabetArray[] = [
                'style' => $styleText . ' class="active-li"',
                'content' => '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="' .
                    strtolower($firstCharacter) . '" onClick="oesApplyAlphabetFilter(this)">' .
                    $firstCharacterDisplay . '</a>'
            ];
        } else {

            /* add character to list */
            $alphabetArray[] = [
                'style' => $styleText . ' class="inactive"',
                'content' => '<span>' . $firstCharacterDisplay . '</span>'
            ];
        }
    }

    return $alphabetArray;
}