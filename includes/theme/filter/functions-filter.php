<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get the HTML representation of the available filter by shortcode.
 *
 * @param string|array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing the filter.
 */
function oes_filter_html($args): string
{
    /* loop through filter list */
    global $oes_archive_data;
    $listItems = [];
    foreach ($oes_archive_data['archive']['filter_array']['list'] ?? [] as $filterKey => $container)
        if (!empty($container['items']) &&
            ($filterKey !== 'objects' || sizeof($container['items']) > 1)) {


            /**
             * Filters the single items of the filter html list.
             *
             * @param array $filterItems The list items.
             * @param string $filterKey The filter key.
             */
            $filterItems = apply_filters('oes/filter_html_single_items_array', $container['items'], $filterKey);



            /**
             * Filters the sorting of filter items.
             *
             * @param array $filterItems The list items.
             * @param string $filterKey The filter key.
             */
            if (has_filter('oes/filter_html_single_items_array_sorting'))
                $filterListItems = apply_filters('oes/filter_html_single_items_array_sorting', $filterItems, $filterKey);
            else $filterListItems = oes_prepare_filter_items($filterItems, $filterKey);
            

            $filterList = '<ul class="oes-filter-list oes-vertical-list' .
                ((($args['type'] ?? 'default') === 'accordion') ? ' collapse' : '') . '" id="oes-filter-component-' .
                $filterKey . '">' . implode('', $filterListItems) . '</ul>';

            switch ($args['type'] ?? 'default') {

                case 'accordion':
                    $listItems[$filterKey] =
                        sprintf('<li id="%s"><a href="#oes-filter-component-%s" data-toggle="collapse" ' .
                            'aria-expanded="false" class="oes-filter-component oes-toggle-down-after">%s</a>%s</li>',
                            'trigger_' . $filterKey,
                            $filterKey,
                            $container['label'] ?? 'Label missing',
                            $filterList
                        );
                    break;

                case 'classic':
                    $listItems[$filterKey] =
                        sprintf('<li id="%s"><span class="oes-filter-component">%s</span>%s</li>',
                            'trigger_' . $filterKey,
                        $container['label'] ?? 'Label missing',
                        $filterList
                    );
                    break;

                case 'default':
                default:
                    $listItems[$filterKey] = '<li>' . oes_get_details_block(
                            '<span class="oes-filter-component">' .
                            ($container['label'] ?? 'Label missing') .
                            '</span>',
                            $filterList,
                            'trigger_' . $filterKey
                        ) . '</li>';
                    break;
            }
        }


    /**
     * Filters the items of the filter html list.
     *
     * @param array $listItems The list items.
     */
    $listItems = apply_filters('oes/filter_html_items_array', $listItems);


    /* add filter javascript variable */
    global $oes_archive_data;
    ?>
    <script type="text/javascript">
        let oes_filter = <?php echo json_encode($oes_archive_data['archive']['filter_array']['json'] ?? []);?>;
    </script><?php

    return '<ul class="oes-filter-list-container oes-vertical-list">' . implode('', $listItems) . '</ul>';
}


/**
 * Sort the filter items.
 * 
 * @param array $filterItems The filter items.
 * @param string $filterKey The filter key.
 * @param string $option The sorting option. Options are frequency, sorting_title. Default is the item label.
 * @return array Return the sorted filter items.
 */
function oes_prepare_filter_items(array $filterItems, string $filterKey, string $option = ''): array
{
    $filterListItems = [];
    foreach ($filterItems ?? [] as $itemKey => $itemLabel) {

        /* get sorting title. Options are frequency, sorting_title or item label. */
        switch ($option) {

            case 'frequency':
                global $oes_filter, $oes_archive_data;
                $sortingTitle = (isset($oes_filter['json'][$filterKey][$itemKey]) ?
                        ((10000 + $oes_archive_data['archive']['count']) -
                            sizeof($oes_filter['json'][$filterKey][$itemKey])) :
                        0) . oes_replace_umlaute($itemLabel) . $itemKey;
                break;

            case 'sorting_title':
                $sortingTitle = oes_replace_umlaute(oes_get_display_title($itemKey,
                        ['option' => 'title_sorting_display'])) . $itemKey;
                break;

            default:
                $sortingTitle = oes_replace_umlaute($itemLabel) . $itemKey;
                break;
        }

        $filterListItems[$sortingTitle] = oes_get_filter_item_html(
            $itemKey,
            $itemLabel,
            $filterKey);
    }
    ksort($filterListItems);
    return $filterListItems;
}


/**
 * Get the HTML representation of the alphabet filter by shortcode.
 *
 * @param string|array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing the filter.
 */
function oes_alphabet_filter_html($args): string
{
    global $oes_archive_data;
    if ($oes_archive_data['archive']['filter']['alphabet'] ?? false)
        return '<ul class="' . ($args['style'] ?? 'is-style-oes-default') . ' oes-alphabet-list oes-horizontal-list">' .
            '<li>' .
            implode('</li><li>', oes_archive_get_alphabet_filter($oes_archive_data['archive']['characters'])) .
            '</li>' .
            '</ul>';

    return '';
}


/**
 * Get the HTML representation of post type filter by shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_post_type_filter_html(): string
{
    /* check for filter */
    global $oes_search;
    $filter = is_array($oes_search) ? $oes_search['filter_array'] : $oes_search->filter_array;
    $listItems = [];
    if (!empty($filter['list']['objects']['items']) &&
        sizeof($filter['list']['objects']['items']) > 1) {
        asort($filter['list']['objects']['items']);
        foreach ($filter['list']['objects']['items'] as $key => $label)
            $listItems[] = sprintf('<a href="javascript:void(0);" ' .
                'onClick="oesFilter.applyPostTypes(\'%s\')" ' .
                'class="oes-filter-post-type oes-filter-post-type-%s">%s</a>',
                $key,
                $key,
                $label);
    }

    return empty($listItems) ?
        '' :
        '<div class="oes-subheader-archive">' .
        '<ul class="oes-post-type-list oes-vertical-list"><li>' .
        implode('</li><li>', $listItems) .
        '</li></ul>' .
        '</div>';
}


/**
 * Get the HTML representation of index filter by shortcode.
 *
 *
 * @return string Return the html string representing the filter.
 */
function oes_index_filter_html(): string
{
    /* loop through index elements */
    global $oes, $oes_language, $oes_is_index;
    $listItems = [];
    if ($oes_is_index &&
        $oes->theme_index_pages[$oes_is_index]['slug'] !== 'hidden' &&
        !empty($oes->theme_index_pages[$oes_is_index]['objects'] ?? [])) {

        /* add navigation item to get to index page */
        $listItems[] = oes_get_html_anchor(
            oes_get_label('archive__filter__all_button', 'All'),
            home_url(($oes->theme_index_pages[$oes_is_index]['slug'] ?? 'index') . '/'),
            false,
            'oes-index-archive-filter-all oes-index-filter-anchor');

        /* get links to index elements */
        foreach ($oes->theme_index_pages[$oes_is_index]['objects'] as $object) {

            /* get name and link from post type or taxonomy */
            $link = $name = false;
            if ($postTypeObject = get_post_type_object($object)) {
                $name = ($oes->post_types[$object]['label_translations_plural'][$oes_language] ??
                    ($oes->post_types[$object]['theme_labels']['archive__header'][$oes_language] ??
                        ($oes->post_types[$object]['label'] ?: $postTypeObject->label)));
                $link = home_url((get_post_type_object($object)->rewrite['slug'] ?? $object) . '/');
            } elseif ($taxonomyObject = get_taxonomy($object)) {
                $name = ($oes->taxonomies[$object]['label_translations_plural'][$oes_language] ??
                    ($oes->taxonomies[$object]['label_translations'][$oes_language] ?:
                        ($oes->taxonomies[$object]['label'] ?: $taxonomyObject->label)));
                $link = home_url((get_taxonomy($object)->rewrite['slug'] ?? $object) . '/');
            }

            /* add navigation item */
            if ($name && $link) $listItems[] = oes_get_html_anchor(
                $name,
                $link,
                false,
                'oes-index-filter-anchor');
        }
    }

    $list = '<div class="oes-index-archive-filter-wrapper">' .
        '<ul class="oes-vertical-list"><li>' . implode('</li><li>', $listItems) . '</li></ul>' .
        '</div>';


    /**
     * Filters the filter string
     *
     * @param string $list The filtered filter string.
     */
    return apply_filters('oes/index_filter_html', $list);
}


/**
 * Get the HTML representation of the active archive filter by shortcode.
 *
 * @param string|array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing the active filter.
 */
function oes_active_filter_html($args): string
{
    /* loop through filter */
    global $oes_archive_data;
    $listItems = [];
    foreach ($oes_archive_data['archive']['filter_array']['list'] ?? [] as $singleFilter => $ignore)
        $listItems[] = '<ul class="' . ($args['style'] ?? 'is-style-oes-default') .
            ' oes-active-filter-' . $singleFilter . ' oes-active-filter oes-field-value-list oes-horizontal-list">' .
            '</ul>';

    return empty($listItems) ?
        '' :
        ('<ul class="oes-active-filter-list oes-vertical-list">' .
            '<li>' . implode('</li><li>', $listItems) . '</li>' .
            '</ul>');
}


/**
 * Get the HTML representation of archive count by shortcode.
 *
 * @return string Return the html string representing the archive count.
 */
function oes_archive_count_html($args = []): string
{
    global $oes, $post_type, $oes_is_index, $oes_language, $oes_archive_count, $oes_is_search;

    /* return early if empty count */
    if (!$oes_archive_count) return '';

    /* check if labels are passed in function or to be derived from label according to page */
    if (isset($args[$oes_language])) {
        $labels = explode('%', $args[$oes_language]);
        $labelSingular = (sizeof($labels) > 1) ? $labels[1] : $labels[0];
        $labelPlural = (sizeof($labels) > 0) ? $labels[0] : $labelSingular;
    } elseif ($oes_is_search) {
        $labelSingular = oes_get_label('search__result__count_singular', 'Result');
        $labelPlural = oes_get_label('search__result__count_plural', 'Results');
    } elseif ($oes_is_index || !$post_type) {
        $labelSingular = oes_get_label('archive__entry', 'Entry');
        $labelPlural = oes_get_label('archive__entries', 'Entries');
    } else {
        $labelSingular = $oes->post_types[$post_type]['theme_labels']['archive__entry'][$oes_language] ??
            (oes_get_label('archive__entry', 'Entry'));
        $labelPlural = $oes->post_types[$post_type]['theme_labels']['archive__entries'][$oes_language] ??
            (oes_get_label('archive__entries', 'Entries'));
    }

    return sprintf('<div class="oes-subheader-count">' .
        '<span class="oes-archive-count-number">%s</span>&nbsp;' .
        '<span class="oes-archive-count-label-singular" %s>%s</span>' .
        '<span class="oes-archive-count-label-plural" %s>%s</span>' .
        '</div>',
        $oes_archive_count,
        (($oes_archive_count > 1) ? 'style="display:none"' : ''),
        $labelSingular,
        (($oes_archive_count < 2) ? 'style="display:none"' : ''),
        $labelPlural);
}


/**
 * Get the HTML representation of search term filter by shortcode.
 *
 * @param string|array $args Shortcode attributes (is mostly empty, unused).
 *
 * @return string Return the html string representing search term filter.
 */
function oes_search_term_filter_html($args): string
{
    global $oes_search;
    return empty($oes_search['search_term'] ?? '') ?
        '' :
        ('<ul class="oes-search-term-filter oes-active-filter"><li>' .
            '<a class="oes-active-filter-item" href="' .
            get_post_type_archive_link($args['post_type'] ?? 'pages') .
            '"><span>' . $oes_search['search_term'] . '</span></a>' .
            '</li></ul>');
}