<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Set archive parameters and data for post type and additional objects.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'execute-loop'  : Execute the loop even if a cache exists.
 *
 * @return void
 */
function oes_set_archive_data(array $args = []): void
{

    /* merge args */
    $args = array_merge([
        'execute-loop' => false,
        'post-type' => false,
        'archive-class' => false,
        'language' => 'all'
    ], $args);

    /* get global parameters */
    global $oes, $post_type, $oes_additional_objects, $oes_is_index, $oes_post_type, $oes_index_objects;
    $oes_post_type = empty($post_type) ? $args['post-type'] : $post_type;

    /* check if index */
    if (!$oes_is_index && !empty($oes->theme_index_pages))
        foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
            if (in_array($post_type, $indexPage['objects'] ?? [])) {
                $oes_is_index = $indexPageKey;
                $oes_index_objects = $indexPage['objects'];
            }

    /* check for cache */
    $cache = false;
    if ($post_type) $cache = oes_get_cache($post_type);
    elseif ($args['post-type']) $cache = oes_get_cache($args['post-type']);
    elseif (sizeof($oes_additional_objects) > 1) $cache = get_option('oes_cache_index');
    elseif (isset($oes_additional_objects[0])) $cache = get_option('oes_cache_' . $oes_additional_objects[0]);

    if ($cache && !$args['execute-loop']) {
        $archive = [
            'archive' => unserialize($cache->cache_value_raw),
            'table-array' => unserialize($cache->cache_value_html)
        ];
    }
    else {

        /**
         * Filters if archive loop uses arguments.
         *
         * @param array $args The arguments.
         */
        if (has_filter('oes/theme_archive_args'))
            $args = apply_filters('oes/theme_archive_args', $args);

        /**
         * Filters if archive loop uses additional arguments.
         *
         * @param array $additionalArgs The additional arguments.
         */
        $additionalArgs = [];
        if (has_filter('oes/theme_archive_additional_args'))
            $additionalArgs = apply_filters('oes/theme_archive_additional_args', $additionalArgs);

        /* execute the loop */
        global $post_type, $oes_taxonomy;
        $archiveClass = $args['archive-class'] ?:
            ($post_type ? $post_type . '_Archive' : ($oes_taxonomy ? $oes_taxonomy . '_Archive': 'OES_Archive'));
        $oesArchive = class_exists($archiveClass) ?
            new $archiveClass($args) :
            new OES_Archive($args);

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

    global $oes_archive_data;
    $oes_archive_data = $archive;
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
    global $oes, $oes_language;
    $consideredLanguage = (!$oes_language || $oes_language === 'all') ? 'language0' : $oes_language;
    $allButton = $oes->theme_labels['archive__filter__all_button'][$consideredLanguage] ?? 'ALL';
    $alphabetArray[] = [
        'style' => ' class="active-li"',
        'content' => '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="all" ' .
            'onClick="oesApplyAlphabetFilter(this)">' .
            $allButton . '</a>'
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


/**
 * Add a field group to the page object containing the language field.
 *
 * @param array $fieldTypes
 * @return void
 */
function oes_add_fields_to_page(array $fieldTypes = []): void
{

    add_action('oes/data_model_registered', function () use ($fieldTypes) {

        $fields = [];
        if (empty($fieldTypes) ||
            in_array('language', $fieldTypes) ||
            in_array('translation', $fieldTypes)) {

            /* prepare languages */
            $languages = [];
            $oes = OES();
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language) $languages[$languageKey] = $language['label'];

            if (empty($fieldTypes) || in_array('language', $fieldTypes))
                $fields[] = [
                    'key' => 'field_oes_post_language',
                    'label' => 'Language',
                    'name' => 'field_oes_post_language',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => true,
                    'choices' => $languages
                ];

            if (empty($fieldTypes) || in_array('translation', $fieldTypes))
                $fields[] = [
                    'key' => 'field_oes_page_translations',
                    'label' => 'Translations',
                    'name' => 'field_oes_page_translations',
                    'type' => 'relationship',
                    'return_format' => 'id',
                    'post_type' => ['page'],
                    'filters' => ['search']
                ];
        }

        if (empty($fieldTypes) || in_array('toc', $fieldTypes))
            $fields[] = [
                'key' => 'field_oes_page_include_toc',
                'label' => 'Include Table of Content',
                'name' => 'field_oes_page_include_toc',
                'type' => 'true_false',
                'instructions' => '',
                'default_value' => true
            ];


        /**
         * Filter page fields before registration.
         *
         * @param array $fields The current fields.
         */
        if (has_filter('oes/data_model_register_page_fields'))
            $fields = apply_filters('oes/data_model_register_page_fields', $fields);


        if (!empty($fields) && function_exists('acf_add_local_field_group'))
            acf_add_local_field_group([
                'key' => 'group_oes_page',
                'title' => 'Page',
                'fields' => $fields,
                'location' => [[[
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page'
                ]]]
            ]);
    });
}


/**
 * Set post data for OES_Post object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'post-id'  : The post id.
 * @return void
 */
function oes_set_post_data(array $args = []): void
{

    /* get the post id */
    $postID = $args['post-id'] ?? get_the_ID();

    /* check if post type for index */
    global $post_type, $oes, $oes_language, $oes_post, $oes_is_index;
    $oes_is_index = in_array($post_type, $oes->theme_index['objects'] ?? []);

    /* get post object (prepare rendered content to derive table of content etc) */
    $cleanLanguage = $oes_language === 'all' ? 'language0' : $oes_language;
    $oes_post = class_exists($post_type) ?
        new $post_type($postID, $cleanLanguage) :
        new OES_Post($postID, $cleanLanguage);

}


/**
 * Set post data for OES_Post object "Page".
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'post-id'  : The post id.
 * @return void
 */
function oes_set_page_data(array $args = []): void
{
    /* get the post id */
    $postID = $args['post-id'] ?? get_the_ID();

    /* get post object (prepare rendered content to derive table of content etc) */
    global $oes_language, $oes_post;
    $cleanLanguage = $oes_language === 'all' ? 'language0' : $oes_language;
    $oes_post = new OES_Page($postID, $cleanLanguage);
}


/**
 * Set term data for OES Taxonomy object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'term-id'  : The term id.
 * @return void
 */
function oes_set_term_data(array $args = []): void
{
    /* get global parameters */
    global $taxonomy, $term, $oes_language, $oes_term;

    /* get the term id */
    $termID = $args['term-id'] ?? false;

    /* get term object */
    $cleanLanguage = $oes_language === 'all' ? 'language0' : $oes_language;
    if(!$termID || !get_term($termID)){
        $termID = get_term_by('slug', $term, $taxonomy)->term_id ?? false;
    }
    $oes_term = class_exists($taxonomy) ?
        new $taxonomy($termID, $cleanLanguage) :
        new OES_Taxonomy($termID, $cleanLanguage);
}