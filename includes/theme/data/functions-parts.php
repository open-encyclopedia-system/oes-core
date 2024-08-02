<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Modify the content while rendering with OES specific content features.
 *
 * @param string $content The content about to be displayed.
 * @return string The modified content.
 */
function oes_the_content(string $content): string
{
    global $oes_post;
    return (!empty($oes_post) && !$oes_post->is_frontpage) ?
        $oes_post->get_html_main(['content' => $content]) :
        $content;
}


/**
 * Filter the heading block content by adding classes and id according to OES Post table of contents configurations.
 *
 * @param string $block_content The block content about to be appended.
 * @param array $parsed_block The full block.
 * @return string Returns modified block content.
 */
function oes_render_block_core_heading(string $block_content, array $parsed_block): string
{
    global $oes_post;
    if ($oes_post instanceof OES_Object && !$oes_post->is_frontpage) {

        /* generate new header by adding class and id */
        $headingText = oes_get_text_from_html_heading($block_content);
        $level = $parsed_block['attrs']['level'] ?? 2;
        $block_content = "\n" .
            sprintf('<h%s class="%s" id="%s">%s</h%s>',
                $level,
                'oes-content-table-header ' . ($parsed_block['attrs']['className'] ?? ''),
                oes_replace_string_for_anchor(strip_tags($headingText ?? '')),
                $headingText ?? '',
                $level
            ) .
            "\n";
    }
    return $block_content;
}


/**
 * Get the page title.
 *
 * @param array $args Additional arguments. Valid parameters are:
 *  'is_link'   :   The title is link to e.g. archive.
 *  'className' :   Additional classes, identifying if uppercase.
 *
 * @return string Return the page title.
 */
function oes_get_page_title(array $args = []): string
{
    global $oes_post, $oes_archive_data, $oes_term;

    /* get page title according to page type */
    $isLink = $args['is_link'] ?? false;
    $linkObject = '';
    $title = '';
    $isTaxonomy = false;
    if (is_page() || is_attachment()) $title = $oes_post ? $oes_post->title : get_the_title();
    elseif (is_single()) {

        if (!$oes_post) $title = get_the_title();
        else {

            /* get archive name as title */
            global $oes, $oes_language;
            $title = ($oes->post_types[$oes_post->post_type]['label_translations'][$oes_language] ??
                ($oes->post_types[$oes_post->post_type]['theme_labels']['archive__header'][$oes_language] ??
                    $oes_post->post_type_label));

            /* prepare archive link */
            if ($isLink) $linkObject = $oes_post->post_type;
        }

    } elseif (is_tax() && $oes_term) {

        /* get taxonomy archive as title  */
        global $oes, $oes_language;
        $taxonomy = $oes_term->taxonomy;
        if ($taxonomyObject = get_taxonomy($taxonomy))
            $title = ($oes->taxonomies[$taxonomy]['label_translations'][$oes_language] ??
                ($oes->taxonomies[$taxonomy]['label_translations'][$oes_language] ??
                    ($oes->taxonomies[$taxonomy]['label'] ?: $taxonomyObject->label)));

        /* prepare archive link */
        if ($isLink) $linkObject = $taxonomy;
        $isTaxonomy = true;

    } elseif ($oes_archive_data) {

        /* get title from archive object */
        $title = $oes_archive_data['archive']['page_title'] ?? '';
    } elseif (is_404()) $title = __('Error 404', 'oes');
    else $title = get_the_title();

    /* add link */
    if ($isLink && !empty($linkObject))
        $title = '<a href="' . oes_get_archive_link($linkObject, $isTaxonomy) . '">' . $title . '</a>';

    /* add style */
    $additionalClass = '';
    if (($args['className'] ?? 'default') == 'is-style-uppercase') $additionalClass .= 'oes-uppercase ';


    /**
     * Filters the page title.
     *
     * @param string $title The page title.
     */
    $title = apply_filters('oes/theme_page_title', $title);


    return '<span class="' . $additionalClass . 'oes-page-title">' . $title . '</span>';
}


/**
 * Get archive link of object.
 *
 * @param string $object The object key.
 * @param bool $taxonomy Identify if object is taxonomy.
 *
 * @return string Return the archive link.
 */
function oes_get_archive_link(string $object = '', bool $taxonomy = false): string
{
    if (empty($object)) return '';
    if ($taxonomy) return (get_site_url() . '/' . (get_taxonomy($object)->rewrite['slug'] ?? $object) . '/');
    else return get_post_type_archive_link($object);

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
    /* first entry */
    $alphabetArray[] = '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="all" ' .
        'onClick="oesFilter.applyAlphabet(this)">' .
        oes_get_label('archive__filter__all_button', 'All') . '</a>';


    /**
     * Filters the alphabet array.
     *
     * @param array $list The alphabet array.
     */
    $alphabet = apply_filters('oes/archive_alphabet_filter_list', array_merge(range('A', 'Z'), ['other']));


    /* loop through alphabet */
    foreach ($alphabet as $letter) {

        /* check if not part of alphabet */
        if ($letter == 'other') $letterDisplay = '#';
        else {
            $letterDisplay = $letter;
            $letter = strtoupper($letter);
        }

        /* add link if in list */
        if (in_array($letter, $characters))
            $alphabetArray[] = '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="' .
                strtolower($letter) . '" onClick="oesFilter.applyAlphabet(this)">' . $letterDisplay . '</a>';
        else $alphabetArray[] = '<span class="inactive">' . $letterDisplay . '</span>';
    }
    return $alphabetArray;
}


/**
 * Prepare the archive by evaluating archive loop.
 *
 * @param array $args Additional arguments.
 * @return string The HTML representation of the archive list.
 */
function oes_get_archive_loop_html(array $args = []): string
{

    /* check if list already displayed (used by other plugins such as OES Timeline, OES Map) */
    global $oes_archive_displayed;
    $oes_archive_displayed = false;


    /**
     * Display archive options via action hook.
     */
    do_action('oes/theme_archive_list');


    $html = '';
    if (!$oes_archive_displayed) {

        global $post_type, $oes_archive_data, $oes_archive_alphabet_initial, $oes_archive_skipped_posts, $oes_language,
               $oes_is_search;
        if (!$oes_archive_skipped_posts) $oes_archive_skipped_posts = [];
        $consideredPostType = $oes_archive_data['archive']['post_type'] ?? $post_type;

        /* return early if no data found */
        $tableArray = $oes_archive_data['table-array'] ?? [];
        if (empty($tableArray)) return '';


        /**
         * Add optional html before list.
         */
        $html = apply_filters('oes/theme_archive_list_before', $html, $tableArray, $args);


        /* loop through table data */
        foreach ($tableArray as $group) {

            $thisCharacter = $group['character'] === "#" ?
                'other' :
                strtolower($group['character']);

            /* optional: add group header, e.g. alphabet character */
            $groupHeader = '';
            if (($oes_archive_alphabet_initial || ($args['alphabet'] ?? false)) &&
                isset($group['character']) &&
                ($group['character'] !== 'none'))
                $groupHeader .= '<div class="oes-alphabet-initial">' .
                    $group['character'] .
                    ($group['additional'] ?? '') .
                    '</div>';


            /**
             * Filters the group header.
             *
             * @param string $groupHeader The group header.
             * @param array $group The group data.
             */
            $groupHeader = apply_filters('oes/theme_archive_group_header', $groupHeader, $group);


            /* loop through entries */
            $containerString = '';
            foreach ($group['table'] as $row)
                if (isset($row['id']) &&
                    !in_array($row['id'], $oes_archive_skipped_posts)) {

                    /* check for language */
                    if (!empty($args['language']) && $args['language'] !== 'all' &&
                        !empty($row['language']) && $row['language'] !== 'all') {

                        switch ($args['language']) {
                            case 'current':
                                $language = $oes_language;
                                break;

                            case 'opposite';
                                $language = ($oes_language == 'language0') ? 'language1' : 'language0';
                                break;

                            default:
                                $language = $args['language'];
                                break;
                        }
                        if ($language !== $row['language']) continue;
                    }

                    /* check if title is link */
                    $title = (isset($oes_archive_data['archive']) &&
                        (($oes_archive_data['archive']['display_content'] ?? false) ||
                            (!$oes_archive_data['archive']['title_is_link'] ?? false))) ?
                        sprintf('<span class="oes-archive-title" id="%s">%s</span>',
                            $post_type . '-' . $row['id'],
                            $row['title']
                        ) :
                        sprintf('<a href="%s" class="oes-archive-title">%s</a>',
                            $row['permalink'],
                            $row['title']
                        );

                    /* check for archive preview */
                    $previewTable = false;
                    if (!($args['exclude-preview'] ?? false))
                        if (($args['archive_data'] ?? true) && !empty($row['data']))
                            foreach ($row['data'] as $dataRow)
                                if (isset($dataRow['value']) &&
                                    (!empty($dataRow['value']) &&
                                        (is_string($dataRow['value']) && strlen(trim($dataRow['value'])) != 0))) {
                                    if (!empty($dataRow['label'] ?? ''))
                                        $previewTable .= sprintf('<tr><th>%s</th><td>%s</td></tr>',
                                            $dataRow['label'],
                                            do_shortcode($dataRow['value']));
                                    else
                                        $previewTable .= '<tr><th colspan="2">' . do_shortcode($dataRow['value']) . '</th></tr>';
                                }

                    /* Prepare read more button */
                    $readMore = $oes_is_search ?
                        sprintf('<tr><td colspan="2"><div class="wp-block-buttons"><div class="wp-block-button">' .
                            '<a href="%s" class="wp-block-button__link wp-element-button">%s</a>' .
                            '</div></div></td></tr>',
                            $row['permalink'],
                            oes_get_label('button__read_more', 'Read More', $oes_language)) :
                        '';

                    /* display row with preview */
                    $displayRow = '';


                    /**
                     * Filter the display of a row (single result).
                     *
                     * @param array $row The single result.
                     * @param string $title The single result title.
                     * @param array $args Additional arguments.
                     * @param string $previewTable The preview table string.
                     * @param string $readMore The read more button string.
                     */
                    if (is_search() && has_filter('oes/archive_loop_display_row_search'))
                        $displayRow = apply_filters('oes/archive_loop_display_row_search',
                            $row,
                            $title,
                            $args,
                            $previewTable,
                            $readMore);

                    /**
                     * Filter the display of a row per post type.
                     *
                     * @param array $row The single result.
                     * @param string $title The single result title.
                     * @param array $args Additional arguments.
                     * @param string $previewTable The preview table string.
                     * @param string $readMore The read more button string.
                     */
                    elseif (!is_search() && has_filter('oes/archive_loop_display_row-' . $consideredPostType))
                        $displayRow = apply_filters('oes/archive_loop_display_row-' . $consideredPostType,
                            $row,
                            $title,
                            $args,
                            $previewTable,
                            $readMore);

                    /**
                     * Filter the display of a row.
                     *
                     * @param array $row The single result.
                     * @param string $title The single result title.
                     * @param array $args Additional arguments.
                     * @param string $previewTable The preview table string.
                     * @param string $readMore The read more button string.
                     */
                    elseif (!is_search() && has_filter('oes/archive_loop_display_row'))
                        $displayRow = apply_filters('oes/archive_loop_display_row',
                            $row,
                            $title,
                            $args,
                            $previewTable,
                            $readMore);
                    elseif ($previewTable)
                        $displayRow = sprintf(
                            '<div class="wp-block-group oes-post-filter-wrapper oes-post-%s oes-post-filter-%s" data-post="%s">' .
                            '<div class="wp-block-group">' .
                            '<details class="wp-block-details">' .
                            '<summary>%s</summary>' .
                            '<div class="oes-archive-table-wrapper wp-block-group collapse" id="row%s">' .
                            '<div class="oes-details-wrapper-before"></div>' .
                            '<table class="%s">%s' .
                            '</table>' .
                            '<div class="oes-details-wrapper-after"></div>' .
                            '</div>' .
                            '</details>' .
                            '</div>' .
                            '</div>',
                            oes_get_post_language($row['id']) ?: 'all',
                            $row['id'],
                            $row['id'],
                            $title . (is_string($row['additional']) ? $row['additional'] : '') . ($row['content'] ?? ''),
                            $row['id'],
                            ($args['className'] ?? 'is-style-oes-default') . ' oes-archive-table',
                            $previewTable . $readMore);
                    elseif (!isset($args['skip-empty']) || !$args['skip-empty'])
                        $displayRow = sprintf(
                            '<div class="oes-post-filter-wrapper oes-post-%s oes-post-filter-%s" data-post="%s">%s</div>',
                            oes_get_post_language($row['id']) ?: 'all',
                            $row['id'],
                            $row['id'],
                            $title .
                            (empty($row['additional']) || !is_string($row['additional']) ?
                                '' :
                                $row['additional']) .
                            ($row['content'] ?? '')
                        );

                    $containerString .= $displayRow;
                }

            if (!empty($containerString))
                $html .= sprintf(
                    '<div class="oes-archive-wrapper oes-alphabet-filter-%s" data-alphabet="%s">%s' .
                    '<div class="oes-alphabet-container">%s</div>' .
                    '</div>',
                    $thisCharacter,
                    $thisCharacter,
                    $groupHeader,
                    $containerString);
        }


        /**
         * Add optional html after list.
         */
        $html = apply_filters('oes/theme_archive_list_after', $html, $tableArray, $args);

    }

    return $html;
}


/**
 * Get the html representation of the citation.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the citation.
 */
function oes_get_citation_html(array $args = []): string
{
    global $oes_post;
    if (is_single() &&
        $oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types']))) {
        $citationHTML = $oes_post->get_citation_html($args);
        return apply_filters('oes/the_content', do_shortcode($citationHTML));
    }
    return '';
}


/**
 * Get the html representation of the note list.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the note list.
 */
function oes_get_notes_html(array $args = []): string
{
    global $oes_post;
    if (is_single() &&
        $oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types']))) {
        return \OES\Popup\get_html_notes([
            'add-to-toc' => $args['include_toc'] ?? true,
            'header' => isset($args['labels']) ? (oes_language_label_html($args['labels'])) : ''
        ]);
    }
    return '';
}


/**
 * Get the html representation of the literature list.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the literature list.
 */
function oes_get_literature_html(array $args = []): string
{
    global $oes_post, $oes_language;
    if (is_single() && $oes_post) {

        $literatureHTML = '';
        $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
        if (!empty($postTypeData['literature'])) {

            foreach ($postTypeData['literature'] as $literatureField) {

                $postID = $oes_post->object_ID;
                if (oes_starts_with($literatureField, 'parent__') && $oes_post->parent_ID) {
                    $literatureField = substr($literatureField, 8);
                    $postID = $oes_post->parent_ID;
                }
                $fieldValue = oes_get_field_display_value($literatureField,
                    $postID,
                    [
                        'list-class' => 'oes-custom-ident oes-vertical-list',
                        'permalink' => false
                    ]);

                if (!empty($fieldValue)) {
                    $literatureHTML .= $oes_post->generate_table_of_contents_header(
                            $oes_post->fields[$literatureField]['further_options']['label_translation_' . $oes_language] ??
                            '',
                            $args['level'] ?? 2,
                            [
                                'add-to-toc' => $args['add-to-toc'] ?? true,
                                'position' => $args['position'] ?? 2
                            ]) . '<div class="oes-literature-wrapper">' . $fieldValue . '</div>';
                }
            }
        } elseif (method_exists($oes_post, 'get_literature_html'))
            $literatureHTML = $oes_post->get_literature_html($args);
        return $literatureHTML;
    }
    return '';
}


/**
 * Get the html representation of the connected terms.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the connected terms.
 */
function oes_get_terms_html(array $args = []): string
{
    global $oes_post;
    if (is_single() &&
        $oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types']))) {

        /* get taxonomies from schema */
        $taxonomies = [];
        $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
        if (!empty($postTypeData['terms'])) {

            foreach ($postTypeData['terms'] as $taxonomy) {
                if (oes_starts_with($taxonomy, 'taxonomy_')) $taxonomies[] = substr($taxonomy, 10);
                elseif (oes_starts_with($taxonomy, 'parent_taxonomy_')) $taxonomies[] = substr($taxonomy, 17);
            }
        }

        /* collect terms */
        $termsPerTaxonomy = [];
        foreach ($oes_post->get_all_terms($taxonomies) as $terms)
            if (!empty($terms))
                $termsPerTaxonomy[] = implode('</li><li>', $terms);

        if (!empty($termsPerTaxonomy)) {

            /* prepare header */
            $header = $args['labels'] ? oes_language_label_html($args['labels']) : '';
            $content = '<ul class="' .
                ($args['className'] ?? 'is-style-oes-default') .
                ' oes-post-term-list oes-field-value-list oes-horizontal-list"><li>' .
                implode('</li><li>', $termsPerTaxonomy) .
                '</li></ul>';
            if ($args['detail']) return '<div class="oes-post-terms-container">' .
                oes_get_details_block(
                    $header,
                    $content
                ) .
                '</div>';

            return '<div class="oes-post-terms-container">' .
                (empty($header) ? '' : '<h5>' . $header . '</h5>') .
                $content .
                '</div>';
        }
    }
    return '';
}


/**
 * Get all terms connected to this post.
 *
 * @param int $postID The post ID.
 * @param array $taxonomies Filter for specific taxonomies. Default is all taxonomies connected to post type.
 * @return array Return array of terms.
 */
function oes_get_terms(int $postID, array $taxonomies = []): array
{
    /* get taxonomies */
    if (empty($taxonomies)) $taxonomies = get_post_type_object(get_post_type($postID))->taxonomies ?? [];

    /* loop through taxonomies */
    $termArray = [];
    foreach ($taxonomies as $taxonomy)
        if (taxonomy_exists($taxonomy)) {

            global $oes_language;
            $terms = get_the_terms($postID, $taxonomy);
            foreach ($terms ?: [] as $term) {

                /* check for term name in other languages */
                $termName = '';
                if ($oes_language !== 'language0')
                    $termName = get_term_meta($term->term_id, 'name_' . $oes_language, true);

                $termArray[$taxonomy][] = oes_get_html_anchor(
                    '<span>' . (empty($termName) ? $term->name : $termName) . '</span>',
                    get_term_link($term->term_id)
                );
            }
        }
    return $termArray;
}


/**
 * Get the html representation of the featured image.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the featured image.
 */
function oes_get_featured_image_html(array $args = []): string
{
    global $oes_post;
    if (is_single() &&
        $oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types']))) {

        /* get taxonomies from schema */
        $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
        if (empty($postTypeData['featured_image']) || $postTypeData['featured_image'] === 'none') return '';
        $fieldKey = $postTypeData['featured_image'];

        /* get image */
        $image = oes_get_field($fieldKey, $oes_post->object_ID);

        if (!$image) return '';
        $imageHTML = oes_get_image_panel_content($image);

        /* prepare header */
        $header = isset($args['labels']) ? oes_language_label_html($args['labels']) : '';
        if ($args['detail']) return '<div class="oes-post-terms-container">' .
            oes_get_details_block(
                $header,
                $imageHTML
            ) .
            '</div>';

        return '<div class="oes-post-terms-container">' .
            (empty($header) ? '' : '<h5>' . $header . '</h5>') .
            $imageHTML .
            '</div>';
    }
    return '';
}


/**
 * Get the html representation of the display title.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the display title.
 */
function oes_get_display_title_html(array $args = []): string
{
    global $oes_post, $oes_term;
    if ($oes_post) return '<h1 class="oes-single-title">' . $oes_post->title . '</h1>';
    elseif ($oes_term) return '<h1 class="oes-single-title">' . $oes_term->title . '</h1>';
    return '';
}


/**
 * Get the html representation of the author byline.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the author byline.
 */
function oes_get_author_byline_html(array $args = []): string
{
    global $oes_post;
    if (is_single() && $oes_post) {
        $authorParam = \OES\Schema\get_post_type_params($oes_post->post_type, 'authors');
        if (!empty($authorParam)) $args['authors'] = $authorParam;
        return $oes_post->get_author_info($args);
    }
    return '';
}


/**
 * Get the html representation of the author vita.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the author vita.
 */
function oes_get_author_vita_html(array $args = []): string
{
    global $oes_post, $oes_language;
    if (is_single() && $oes_post) {
        $vitaParam = \OES\Schema\get_post_type_params($oes_post->post_type, 'vita');
        if (!empty($vitaParam)) {
            if (isset($oes_post->fields[$vitaParam . '_' . $oes_language]))
                return $oes_post->fields[$vitaParam . '_' . $oes_language]['value-display'] ?? '';
            else return $oes_post->fields[$vitaParam]['value-display'] ?? '';
        }
    }
    return '';
}


/**
 * Get the html representation of the version information.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the version information.
 */
function oes_get_version_info_html(array $args = []): string
{
    global $oes_post;
    if (is_single() && $oes_post) {

        $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
        foreach (['pub_date', 'edit_date'] as $param)
            if (isset($args[$param]) && is_bool($args[$param])) {
                if ($args[$param] && !empty($postTypeData[$param])) $args[$param] = $postTypeData[$param];
                else unset($args[$param]);
            }

        if (isset($args['pub_date']) || isset($args['edit_date'])) {
            $versionArgs = $args['version-parameter'] ?? [];
            if (isset($args['version']) && !$args['version']) $versionArgs['skip-version'] = true;
            $versionArgs['style'] = $args['className'] ?? '';
            return $oes_post->get_version_info(
                $args['pub_date'] ?? '',
                $args['edit_date'] ?? '',
                $versionArgs);
        }
    }
    return '';
}


/**
 * Get the html representation of the translation link.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the translation link.
 */
function oes_get_translation_link_html(array $args = []): string
{
    global $oes_post;
    if (is_single() && $oes_post) return $oes_post->get_translation_info($args);
    return '';
}


/**
 * Get the html representation of the index connection.
 *
 * @param array $args Additional arguments.
 * @return string Return the html representation of the index connection.
 */
function oes_get_index_html(array $args = []): string
{
    if (is_front_page() || is_page()) return '';

    /* prepare args */
    if ($args['labels'] ?? false) $args['display-header'] = oes_language_label_html($args['labels']);
    $args['style'] = $args['className'] ?? '';

    global $oes_post, $oes_term;
    if (is_single() && $oes_post && (!empty($oes_post->part_of_index_pages) || is_attachment() || OES()->block_theme))
        return $oes_post->get_index_connections($args['post_type'] ?? '', $args['relationship'] ?? '', $args);
    elseif (is_tax() && $oes_term && (!empty($oes_term->part_of_index_pages) || OES()->block_theme))
        return $oes_term->get_index_connections($args['post_type'] ?? '', $args['relationship'] ?? '', $args);
    return '';
}


/**
 * Get the html representation of the metadata table.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the metadata table.
 */
function oes_get_metadata_html(array $args = []): string
{
    global $oes_post;
    if (is_single() &&
        $oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types'])))
        return $oes_post->get_html_metadata_div(
            $oes_post->get_html_metadata_table_string(),
            array_merge([
                'display-header' => isset($args['labels']) ? (oes_language_label_html($args['labels'])) : '',
                'add-to-toc' => $args['include_toc'] ?? false], $args)
        );
    return '';
}


/**
 * Get the html representation of the empty table of contents that will be filled by js.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the empty table of contents.
 */
function oes_get_prepared_table_of_contents_html(array $args = []): string
{
    global $oes_post;
    if ($oes_post &&
        (empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types'])) &&
        (!is_page() || (oes_get_field('field_oes_page_include_toc', $oes_post->object_ID) ?? false))) {

        $header = '';
        if ($args['labels'] ?? false)
            $header = '<h2 class="oes-exclude-heading-from-toc oes-content-table-header" id="oes-toc-header">' .
                (isset($args['labels']) ? (oes_language_label_html($args['labels'])) : '') . '</h2>';
        return $header . '<ul class="oes-table-of-contents oes-vertical-list"></ul>';
    }
    return '';
}


/**
 * Get the html representation of terms matching the search criteria.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of terms matching the search criteria.
 */
function oes_get_search_terms_html(array $args = []): string
{
    /* return early if missing taxonomy */
    if (!isset($args['taxonomy'])) return '';

    /* check for terms */
    global $oes_search, $oes_language;
    $searchTerm = $oes_search->search_term ?? false;
    $termsFound = [];
    if (get_taxonomy($args['taxonomy'])) {

        /* check if name or slug*/
        if ($term = get_term_by('name', $searchTerm, $args['taxonomy']))
            $termsFound[$term->term_id] = sprintf('<a href="%s">%s</a>',
                get_term_link($term),
                $term->name
            );
        if ($term = get_term_by('slug', $searchTerm, $args['taxonomy']))
            $termsFound[$term->term_id] = sprintf('<a href="%s">%s</a>',
                get_term_link($term),
                $term->name
            );
    }

    return empty($termsFound) ?
        '' :
        ('<span class="oes-see-also-tag">' . ($args['labels'][$oes_language] ?? '') . '</span>' .
            implode(', ', $termsFound));
}