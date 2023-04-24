<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_shortcode('oes_filter', 'oes_filter_html');
add_shortcode('oes_alphabet_filter', 'oes_alphabet_filter_html');
add_shortcode('oes_post_type_filter', 'oes_post_type_filter_html');
add_shortcode('oes_active_filter', 'oes_active_filter_html');
add_shortcode('oes_archive_count', 'oes_archive_count_html');
add_shortcode('oes_index_filter', 'oes_index_filter_html');
add_shortcode('oes_search_filter', 'oes_search_term_filter_html');


/**
 * Get the HTML representation of the available filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing the filter.
 */
function oes_filter_html(array $args): string
{
    /* get global OES filter */
    global $oes_filter;

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default';


    if (!empty($oes_filter['list'])) {

        $listItemsHTML = [];
        foreach ($oes_filter['list'] as $oesFilterKey => $oesFilterContainer)
            if (!empty($oesFilterContainer['items'])) {

                /* filter items list */
                $oesFilterItemsList = '<ul class="oes-filter-list oes-vertical-list' . (($type === 'accordion') ? ' collapse' : '') . '" id="oes-filter-component-' .
                    $oesFilterKey . '">';

                natcasesort($oesFilterContainer['items']);
                $filterItems = $oesFilterContainer['items'];

                /**
                 * Filters the single items of the filter html list.
                 *
                 * @param array $filterItems The list items.
                 * @param string $oesFilterKey The filter key.
                 */
                if (has_filter('oes/filter_html_single_items_array'))
                    $filterItems = apply_filters('oes/filter_html_single_items_array', $filterItems, $oesFilterKey);


                foreach ($filterItems ?? [] as $itemKey => $itemLabel) {
                    $oesFilterItemsList .= oes_get_filter_item_html($itemKey, $itemLabel, $oesFilterKey);

                    $oesFilterListItemAfter = '';


                    /**
                     * Filters the single items of the filter html list after.
                     *
                     * @param string $itemKey The list item.
                     * @param string $oesFilterKey The filter key.
                     */
                    if (has_filter('oes/filter_html_single_item_after'))
                        $oesFilterListItemAfter = apply_filters('oes/filter_html_single_item_after', $itemKey, $oesFilterKey);

                    $oesFilterItemsList .= $oesFilterListItemAfter;

                }
                $oesFilterItemsList .= '</ul>';

                $listItemsHTML[$oesFilterKey] = ($type === 'accordion') ?
                    sprintf('<li><a href="#oes-filter-component-%s" data-toggle="collapse" ' .
                        'aria-expanded="false" class="oes-filter-component oes-toggle-down-after">%s</a>%s</li>',
                        $oesFilterKey,
                        $oesFilterContainer['label'] ?? 'Label missing',
                        $oesFilterItemsList
                    ) :
                    sprintf('<li><span class="oes-filter-component">%s</span>%s</li>',
                        $oesFilterContainer['label'] ?? 'Label missing',
                        $oesFilterItemsList
                    );
            }


        /**
         * Filters the items of the filter html list.
         *
         * @param array $listItemsHTML The list items.
         */
        if (has_filter('oes/filter_html_items_array'))
            $listItemsHTML = apply_filters('oes/filter_html_items_array', $listItemsHTML);


        $returnString = '<ul class="oes-filter-list-container oes-vertical-list">' . implode('', $listItemsHTML) . '</ul>';
    }

    return $returnString;
}


/**
 * Get the HTML representation of the alphabet filter by shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_alphabet_filter_html(): string
{
    /* get global OES filter */
    global $oes_archive_data;

    /* prepare return string */
    $returnString = '';

    /* optional alphabet subheader */
    if (((isset($oes_archive_data['archive']['filter']['alphabet']) &&
            $oes_archive_data['archive']['filter']['alphabet']) ||
        !empty($oes_additional_objects))) {
        $alphabetList = '';

        $alphabetArray = oes_archive_get_alphabet_filter($oes_archive_data['archive']['characters']);
        foreach ($alphabetArray as $item) $alphabetList .= '<li>' . $item['content'] . '</li>';
        $returnString .= '<div class="oes-subheader-alphabet">' .
            '<ul class="oes-alphabet-list oes-horizontal-list">' . $alphabetList . '</ul></div>';
    }

    return $returnString;
}


/**
 * Get the HTML representation of post type filter by shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_post_type_filter_html(): string
{
    /* get global OES filter */
    global $oes_search;

    /* prepare return string */
    $returnString = '';

    $filter = is_array($oes_search) ? $oes_search['filter_array'] : $oes_search->filter_array;
    $filterList = '';
    if (!empty($filter['list']['objects']['items']) &&
        sizeof($filter['list']['objects']['items']) > 1)
        foreach ($filter['list']['objects']['items'] as $key => $label) {
            $filterList .= sprintf('<li><a href="javascript:void(0);" ' .
                'onClick="oesFilterPostTypes(\'%s\')" ' .
                'class="oes-filter-post-type oes-filter-post-type-%s">%s</a></li>',
                $key,
                $key,
                $label);
        }

    if (!empty($filterList))
        $returnString .= '<div class="oes-subheader-archive">' .
            '<ul class="oes-post-type-list oes-vertical-list">' . $filterList . '</ul>' .
            '</div>';

    return $returnString;
}


/**
 * Get the HTML representation of index filter by shortcode.
 *
 *
 * @return string Return the html string representing the filter.
 */
function oes_index_filter_html(): string
{
    /* get global */
    global $oes, $oes_language, $oes_is_index, $post_type, $taxonomy;
    $consideredLanguage = ($oes_language === 'all') ? $oes->main_language : $oes_language;
    $languageSwitch = ($consideredLanguage !== $oes->main_language ?
        strtolower($oes->languages[$consideredLanguage]['abb']) . '/' :
        '');

        /* prepare return string */
    $returnString = '';

    if ($oes_is_index &&
        $oes->theme_index_pages[$oes_is_index]['slug'] !== 'hidden' &&
        isset($oes->theme_index_pages[$oes_is_index]['objects']) &&
        !empty($oes->theme_index_pages[$oes_is_index]['objects'])) {

        /* add navigation item to get to index page */
        $returnString .= sprintf('<li class="oes-index-archive-filter-all"><a href="%s" class="oes-index-filter-anchor">%s</a></li>',
            home_url('/') . $languageSwitch . ($oes->theme_index_pages[$oes_is_index]['slug'] ?? 'index') . '/',
            $oes->theme_labels['archive__filter__all_button'][$consideredLanguage] ?? 'ALL'
        );

        foreach ($oes->theme_index_pages[$oes_is_index]['objects'] as $object) {

            /* get name and link from post type or taxonomy */
            $link = $name = false;
            if ($postTypeObject = get_post_type_object($object)) {
                $name = ($oes->post_types[$object]['label_translations_plural'][$consideredLanguage] ??
                    ($oes->post_types[$object]['theme_labels']['archive__header'][$consideredLanguage] ??
                    ($oes->post_types[$object]['label'] ?: $postTypeObject->label)));
                $link = home_url('/') . $languageSwitch . (get_post_type_object($object)->rewrite['slug'] ?? $object) . '/';
            } elseif ($taxonomyObject = get_taxonomy($object)) {
                $name = ($oes->taxonomies[$object]['label_translations_plural'][$consideredLanguage] ??
                    ($oes->taxonomies[$object]['label_translations'][$consideredLanguage] ?:
                    ($oes->taxonomies[$object]['label'] ?: $taxonomyObject->label)));
                $link = home_url('/') . $languageSwitch . $oes->taxonomies[$object]['rewrite']['slug'] . '/';
            }

            /* add navigation item */
            if ($name && $link)
                $returnString .= sprintf('<li><a href="%s" class="oes-index-filter-anchor %s">%s</a></li>',
                    $link,
                    (($object === $post_type || $object === $taxonomy) ? 'active' : ''),
                    $name
                );
        }

    }

    $prepareReturn = '<div class="oes-index-archive-filter-wrapper">' .
        '<ul class="oes-vertical-list">' . $returnString . '</ul></div>';


    /**
     * Filters the filter string
     *
     * @param string $prepareReturn The filtered filter string.
     */
    if (has_filter('oes/index_filter_html'))
        $prepareReturn = apply_filters('oes/index_filter_html', $prepareReturn);


    return $prepareReturn;
}


/**
 * Get the HTML representation of the active archive filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing the active filter.
 */
function oes_active_filter_html(array $args = []): string
{
    /* get global OES filter */
    global $oes, $oes_filter, $oes_language;
    $consideredLanguage = ($oes_language === 'all') ? $oes->main_language : $oes_language;

    /* prepare return string */
    $returnString = '';
    if (!empty($oes_filter['list'])) {

        $returnString .=
            '<div class="oes-active-filter-container oes-active-filter-container-' . $consideredLanguage . '">' .
            '<div class="oes-active-filter-wrapper ' . ($args['classes'] ?? '') . '">' .
            '<ul class="oes-active-filter-container-list">';

        foreach ($oes_filter['list'] as $singleFilter => $ignore)
            $returnString .= '<li><ul class="oes-active-filter-' . $singleFilter .
                ' oes-active-filter"></ul></li>';

        $returnString .= '</ul></div></div>';
    }

    return $returnString;
}


/**
 * Get the HTML representation of archive count by shortcode.
 *
 * @return string Return the html string representing the archive count.
 */
function oes_archive_count_html(): string
{
    /* get global OES filter */
    global $oes, $post_type, $oes_is_index, $oes_language, $oes_archive_count, $oes_is_search;
    $consideredLanguage = ($oes_language === 'all') ? $oes->main_language : $oes_language;

    /* prepare return string */
    $returnString = '';

    /* add count */
    if ($oes_archive_count) {

        if ($oes_is_search) {
            $labelSingular = $oes->theme_labels['search__result__count_singular'][$consideredLanguage] ?? __('Result', 'oes');
            $labelPlural = $oes->theme_labels['search__result__count_plural'][$consideredLanguage] ?? __('Result', 'oes');
        } elseif ($oes_is_index || !$post_type) {
            //@oesDevelopment Different taxonomy label.
            $labelSingular = $oes->theme_labels['archive__entry'][$consideredLanguage] ?? __('Entry', 'oes');
            $labelPlural = $oes->theme_labels['archive__entries'][$consideredLanguage] ?? __('Entries', 'oes');
        } else {
            $labelSingular = $oes->post_types[$post_type]['theme_labels']['archive__entry'][$consideredLanguage] ??
                ($oes->theme_labels['archive__entry'][$consideredLanguage] ?? __('Entry', 'oes'));
            $labelPlural = $oes->post_types[$post_type]['theme_labels']['archive__entries'][$consideredLanguage] ??
                ($oes->theme_labels['archive__entries'][$consideredLanguage] ?? __('Entries', 'oes'));
        }

        $returnString .= sprintf('<div class="oes-subheader-count">' .
            '<span class="oes-archive-count-number">%s</span>' .
            '<span class="oes-archive-count-label-singular" %s>%s</span>' .
            '<span class="oes-archive-count-label-plural" %s>%s</span>' .
            '</div>',
            $oes_archive_count,
            (($oes_archive_count > 1) ? 'style="display:none"' : ''),
            $labelSingular,
            (($oes_archive_count < 2) ? 'style="display:none"' : ''),
            $labelPlural);
    }

    return $returnString;
}


/**
 * Get the HTML representation of search term filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing search term filter.
 */
function oes_search_term_filter_html(array $args = []): string
{
    global $oes_search;
    if (isset($oes_search['search_term']) && !empty($oes_search['search_term']))
        return '<ul class="oes-search-term-filter oes-active-filter"><li>' .
            '<a class="oes-active-filter-item" href="' . get_post_type_archive_link($args['post_type'] ?? 'pages') .
            '"><span>' . $oes_search['search_term'] . '</span></a></li></ul>';
    else return '';
}