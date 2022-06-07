<?php


/** --------------------------------------------------------------------------------------------------------------------
 * Initialize style and scripts ----------------------------------------------------------------------------------------
 * -------------------------------------------------------------------------------------------------------------------*/
$oes = OES();
$oes->assets->add_style('oes-filter', '/includes/theme/filter/filter.css');
$oes->assets->add_script('oes-filter', '/includes/theme/filter/filter.js');


/** --------------------------------------------------------------------------------------------------------------------
 * Add shortcode -------------------------------------------------------------------------------------------------------
 * -------------------------------------------------------------------------------------------------------------------*/
add_shortcode('oes_filter', 'oes_filter_html');
add_shortcode('oes_post_type_filter', 'oes_post_type_filter_html');
add_shortcode('oes_active_filter', 'oes_active_filter_html');
add_shortcode('oes_archive_count', 'oes_archive_count_html');
add_shortcode('oes_index_filter', 'oes_index_filter_html');


/**
 * Get the HTML representation of archive filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_filter_html(array $args, string $content = ""): string
{
    /* get global OES filter */
    global $oes_filter;

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default';


    if (!empty($oes_filter['list'])) {

        $returnString .= '<ul class="oes-filter-list-container">';

        foreach ($oes_filter['list'] as $oesFilterKey => $oesFilterContainer)
            if (!empty($oesFilterContainer['items'])) {

                /* filter items list */
                $oesFilterItemsList = '<ul class="oes-filter-list collapse" id="oes-filter-component-' .
                    $oesFilterKey . '">';

                natcasesort($oesFilterContainer['items']);
                foreach ($oesFilterContainer['items'] ?? [] as $itemKey => $itemLabel)
                    $oesFilterItemsList .=
                        sprintf('<li class="oes-archive-filter-item"><a href="javascript:void(0)" data-filter="%s" ' .
                            'data-name="%s" data-type="%s"' .
                            ' class="oes-archive-filter-%s-%s oes-archive-filter" ' .
                            'onClick="oesApplyFilter(\'%s\', \'%s\')"><span>%s</span>' .
                            '<span class="oes-filter-item-count">%s</span></a></li>',
                            $itemKey,
                            $itemLabel,
                            $oesFilterKey,
                            $oesFilterKey,
                            $itemKey,
                            $itemKey,
                            $oesFilterKey,
                            $itemLabel,
                            '(' . (isset($oes_filter['json'][$oesFilterKey][$itemKey]) ?
                                sizeof($oes_filter['json'][$oesFilterKey][$itemKey]) :
                                0) . ')'
                        );
                $oesFilterItemsList .= '</ul>';

                $returnString .= ($type === 'accordion') ?
                    sprintf('<li><a href="#oes-filter-component-%s" data-toggle="collapse" ' .
                        'aria-expanded="false" class="oes-filter-component">%s</a>%s</li>',
                        $oesFilterKey,
                        $oesFilterContainer['label'] ?? 'Label missing',
                        $oesFilterItemsList
                    ) :
                    sprintf('<li><span class="oes-filter-component">%s</span>%s</li>',
                        $oesFilterContainer['label'] ?? 'Label missing',
                        $oesFilterItemsList
                    );
            }

        $returnString .= '</ul>';
    }

    return $returnString;
}


/**
 * Get the HTML representation of post type filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_post_type_filter_html(array $args, string $content = ""): string
{
    /* get global OES filter */
    global $oes_search;

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default';

    $filter = $oes_search['filter_array'];
    $filterList = '';
    if (!empty($filter['list']['objects']['items']) &&
        sizeof($filter['list']['objects']['items']) > 1)
        foreach ($filter['list']['objects']['items'] as $key => $label){
            $filterList .= sprintf('<li><a href="javascript:void(0);" ' .
                'onClick="oesFilterPostTypes(\'%s\')" ' .
                'class="oes-filter-post-type oes-filter-post-type-%s">%s</a></li>',
                $key, $key, $label);
        }

    if (!empty($filterList))
        $returnString .= '<div class="oes-subheader-archive">' .
            '<ul class="oes-post-type-list oes-horizontal-list">' . $filterList . '</ul>' .
        '</div>';

    return $returnString;
}


/**
 * Get the HTML representation of index filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the filter.
 */
function oes_index_filter_html(array $args, string $content = ""): string
{
    /* get global */
    global $oes, $oes_language, $oes_is_index, $post_type, $taxonomy;

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default';

    if ($oes_is_index && isset($oes->theme_index['objects']) && !empty($oes->theme_index['objects'])) {

        /* add navigation item to get to index page */
        $returnString .= sprintf('<li class="oes-index-archive-filter-all"><a href="%s" class="text-uppercase">%s</a></li>',
            get_site_url() . '/' . ($oes->theme_index['slug'] ?? 'index') . '/',
            $oes->theme_labels['archive__filter__all_button'][$oes_language] ?? 'ALL'
        );

        foreach ($oes->theme_index['objects'] as $object) {

            /* get name and link from post type or taxonomy */
            $link = $name = false;
            if ($postTypeObject = get_post_type_object($object)) {
                $name = ($oes->post_types[$object]['theme_labels']['archive__header'][$oes_language] ?:
                    ($oes->post_types[$object]['label'] ?: $postTypeObject->label));
                $link = get_post_type_archive_link($object);
            } elseif ($taxonomyObject = get_taxonomy($object)) {
                $name = ($oes->taxonomies[$object]['label_translations'][$oes_language] ?:
                    ($oes->taxonomies[$object]['label'] ?: $taxonomyObject->label));
                $link = (get_site_url() . '/' . $oes->taxonomies[$object]['rewrite']['slug'] . '/');
            }

            /* add navigation item */
            if ($name && $link)
                $returnString .= sprintf('<li><a href="%s" class="text-uppercase %s">%s</a></li>',
                    $link,
                    (($object === $post_type || $object === $taxonomy) ?  'active' : ''),
                    $name
                );
        }

    }

    return '<div class="oes-index-archive-filter-wrapper">' .
        '<ul class="oes-vertical-list">' . $returnString . '</ul></div>';
}



/**
 * Get the HTML representation of the active archive filter by shortcode.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the active filter.
 */
function oes_active_filter_html(array $args = [], string $content = ""): string
{
    /* get global OES filter */
    global $oes_filter, $oes_language;
    $consideredLanguage = $oes_language ?? '';

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default'; /* TODO @nextRelease: more representation options */

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
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the archive count.
 */
function oes_archive_count_html(array $args = [], string $content = ""): string
{
    /* get global OES filter */
    global $oes, $post_type, $taxonomy, $oes_is_index, $oes_language, $oes_archive_count, $oes_is_search;
    if($oes_language === 'all') $oes_language = 'language0';

    /* prepare return string */
    $returnString = '';
    $type = $args['type'] ?? 'default'; /* TODO @nextRelease: more representation options */

    /* add count */
    if ($oes_archive_count){

        if ($oes_is_search) {
            $labelSingular = $oes->theme_labels['search__result__count_singular'][$oes_language] ?? __('Result', 'oes');
            $labelPlural = $oes->theme_labels['search__result__count_plural'][$oes_language] ?? __('Result', 'oes');
        } elseif ($oes_is_index || !$post_type) {
            //TODO @2.0 Taxonomy label
            $labelSingular = $oes->theme_labels['archive__entry'][$oes_language] ?? __('Entry', 'oes');
            $labelPlural = $oes->theme_labels['archive__entries'][$oes_language] ?? __('Entries', 'oes');
        }
        else {
            $labelSingular = $oes->post_types[$post_type]['theme_labels']['archive__entry'][$oes_language] ??
                ($oes->theme_labels['archive__entry'][$oes_language] ?? __('Entry', 'oes'));
            $labelPlural = $oes->post_types[$post_type]['theme_labels']['archive__entries'][$oes_language] ??
                ($oes->theme_labels['archive__entries'][$oes_language] ?? __('Entries', 'oes'));
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