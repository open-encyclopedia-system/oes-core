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

    if (empty($oes_post) || $oes_post->is_frontpage) {
        return $content;
    }

    return $oes_post->get_html_main(['content' => $content]);
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

    if(!$oes_post || $oes_post->is_frontpage) {
        return $block_content;
    }

    $headingText = oes_get_text_from_html_heading($block_content);
    $level = $parsed_block['attrs']['level'] ?? 2;
    return "\n" .
        sprintf('<h%s class="%s" id="%s">%s</h%s>',
            $level,
            'oes-content-table-header ' . ($parsed_block['attrs']['className'] ?? ''),
            oes_replace_string_for_anchor(strip_tags($headingText)),
            $headingText,
            $level
        ) .
        "\n";
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
    global $oes_post, $oes_archive, $oes_term;

    $isLink = $args['is_link'] ?? false;
    $linkObject = '';
    $title = '';
    $isTaxonomy = false;

    if (is_page() || is_attachment()) {
        $title = $oes_post ? $oes_post->title : get_the_title();
    }
    elseif (is_single()) {

        if (!$oes_post) {
            $title = get_the_title();
        }
        else {

            global $oes, $oes_language;
            $title = ($oes->post_types[$oes_post->post_type]['label_translations'][$oes_language] ??
                ($oes->post_types[$oes_post->post_type]['theme_labels']['archive__header'][$oes_language] ??
                    $oes_post->post_type_label));

            if ($isLink) {
                $linkObject = $oes_post->post_type;
            }
        }

    } elseif (is_tax() && $oes_term) {

        global $oes, $oes_language;

        $isTaxonomy = true;
        $taxonomy = $oes_term->taxonomy;

        if ($taxonomyObject = get_taxonomy($taxonomy)) {
            $title = ($oes->taxonomies[$taxonomy]['label_translations'][$oes_language] ??
                ($oes->taxonomies[$taxonomy]['label_translations'][$oes_language] ??
                    ($oes->taxonomies[$taxonomy]['label'] ?: $taxonomyObject->label)));
        }

        if ($isLink) {
            $linkObject = $taxonomy;
        }
    } elseif ($oes_archive) {
        $title = $oes_archive['page_title'] ?? '';
    } elseif (is_404()) {
        $title = __('Error 404', 'oes');
    }
    else {
        $title = get_the_title();
    }

    if ($isLink && !empty($linkObject)) {
        $title = '<a href="' . oes_get_archive_link($linkObject, $isTaxonomy) . '">' . $title . '</a>';
    }
    else {
        $title = esc_html($title);
    }

    $title = apply_filters('oes/theme_page_title', $title);

    return '<span class="oes-page-title">' . $title . '</span>';
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
    if (empty($object)) {
        return '';
    }

    if ($taxonomy) {
        return (get_site_url() . '/' . (get_taxonomy($object)->rewrite['slug'] ?? $object) . '/');
    }

    return get_post_type_archive_link($object);
}

/**
 * Get the alphabet filter (list of all characters with filter functions).
 *
 * @param array $characters All starting characters of archive items.
 * @param bool $displayEmpty Include characters with no entries.
 *
 * @return array The alphabet list
 */
function oes_archive_get_alphabet_filter(array $characters, array $args = [], bool $displayEmpty = true): array
{
    if(isset($args['empty'])){
        $displayEmpty = $args['empty'];
    }

    $alphabet = apply_filters('oes/archive_alphabet_filter_list', array_merge(range('A', 'Z'), ['other']));

    $allLabel = oes_get_language_label_text($args);
    if(empty($allLabel)) {
        $allLabel = oes_get_label('archive__filter__all_button', 'All');
    }

    $alphabetArray[] = '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="all" ' .
        'onClick="oesFilter.applyAlphabet(this)">' . $allLabel . '</a>';

    foreach ($alphabet as $letter) {

        if ($letter == 'other') {
            $letterDisplay = '#';
        } else {
            $letterDisplay = $letter;
            $letter = strtoupper($letter);
        }

        if (in_array($letter, $characters)) {
            $alphabetArray[] = '<a href="javascript:void(0)" class="oes-filter-abc" data-filter="' .
                strtolower($letter) . '" onClick="oesFilter.applyAlphabet(this)">' . $letterDisplay . '</a>';
        } elseif ($displayEmpty) {
            $alphabetArray[] = '<span class="inactive">' . $letterDisplay . '</span>';
        }
    }
    return $alphabetArray;
}

/**
 * Returns the rendered HTML for an archive loop using a customizable class.
 *
 * @param array $args Optional. Arguments to pass to the archive loop renderer.
 * @return string The rendered archive loop HTML.
 */
function oes_get_archive_loop_html(array $args = []): string
{
    $class = oes_get_application_class_name('OES_Archive_Loop');
    $class = apply_filters('oes/theme_archive_loop_class', $class);

    if (!class_exists($class)) {
        return '';
    }

    $archiveLoop = new $class($args);
    return $archiveLoop->render();
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

    if (!$oes_post || !method_exists($oes_post, 'get_citation_html')) {
        return '';
    }

    if (!oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    $citationHTML = $oes_post->get_citation_html($args);
    return apply_filters('oes/the_content', do_shortcode($citationHTML));
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

    if (!$oes_post || !method_exists($oes_post, 'get_html_notes')) {
        return '';
    }

    if (!oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    return \OES\Popup\get_html_notes([
        'add-to-toc' => $args['include_toc'] ?? true,
        'header' => isset($args['labels']) ? (oes_language_label_html($args['labels'])) : ''
    ]);
}

/**
 * Get the html representation of the post abstract.
 *
 * @param array $args The additional arguments.
 * @return string Return the html representation of the post abstract.
 */
function oes_get_abstract_html(array $args = []): string
{
    global $oes_post;

    if (!$oes_post) {
        return '';
    }

    $abstractHTML = '';
    $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
    if (!empty($postTypeData['excerpt'])) {
        $abstractHTML = oes_get_field_display_value($postTypeData['excerpt'], $oes_post->object_ID);
    } elseif (method_exists($oes_post, 'get_abstract_html')) {
        $abstractHTML = $oes_post->get_abstract_html($args);
    }

    $header = isset($args['labels']) ? oes_language_label_html($args['labels']) : '';

    return $header . '<div class="oes-post-abstract">' . $abstractHTML . '</div>';
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

    if (!$oes_post) {
        return '';
    }

    $literatureHTML = '';
    $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
    if (!empty($postTypeData['literature'])) {

        foreach ($postTypeData['literature'] as $literatureField) {

            $postID = $oes_post->object_ID;
            if (str_starts_with($literatureField, 'parent__') && $oes_post->parent_ID) {
                $literatureField = substr($literatureField, 8);
                $postID = $oes_post->parent_ID;
            }

            $displayArgs = [
                'list-class' => 'oes-custom-ident oes-vertical-list',
                'permalink' => false,
            ];

            /**
             * Filter the literature field.
             *
             * @param string $literatureField Field key.
             * @param int|bool $postID Post ID.
             * @param array $displayArgs Display arguments.
             */
            $fieldValue = apply_filters(
                'oes/get_literature_field_display_value',
                null,
                $literatureField,
                $postID,
                $displayArgs
            );

            // Fallback if filter returned nothing
            if ($fieldValue === false || $fieldValue === null) {
                $fieldValue = oes_get_field_display_value(
                    $literatureField,
                    $postID,
                    $displayArgs
                );
            }

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
    } elseif (method_exists($oes_post, 'get_literature_html')) {
        $literatureHTML = $oes_post->get_literature_html($args);
    }

    return $literatureHTML;
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

    if (!$oes_post || !method_exists($oes_post, 'get_all_terms')) {
        return '';
    }

    if (!oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    $taxonomies = [];
    $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
    if (!empty($postTypeData['terms'])) {

        foreach ($postTypeData['terms'] as $taxonomy) {
            if (str_starts_with($taxonomy, 'taxonomy_')) {
                $taxonomies[] = substr($taxonomy, 10);
            }
            elseif (str_starts_with($taxonomy, 'parent_taxonomy_')) {
                $taxonomies[] = substr($taxonomy, 17);
            }
        }
    }

    $termsPerTaxonomy = [];
    foreach ($oes_post->get_all_terms($taxonomies) as $terms) {
        if (!empty($terms)) {
            $termsPerTaxonomy[] = implode('</li><li>', $terms);
        }
    }

    if(empty($termsPerTaxonomy)){
        return '';
    }

    $header = oes_get_language_label_text($args);
    $content = '<ul class="' .
        ($args['className'] ?? 'is-style-oes-default') .
        ' oes-post-term-list oes-field-value-list oes-horizontal-list"><li>' .
        implode('</li><li>', $termsPerTaxonomy) .
        '</li></ul>';

    if ($args['detail'] ?? false) {
        return '<div class="oes-post-terms-container">' .
            oes_get_details_block(
                $header,
                $content
            ) .
            '</div>';
    }

    return '<div class="oes-post-terms-container">' .
        (empty($header) ? '' : '<h5>' . $header . '</h5>') .
        $content .
        '</div>';
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
    if (empty($taxonomies)) {
        $taxonomies = get_post_type_object(get_post_type($postID))->taxonomies ?? [];
    }

    $termArray = [];
    foreach ($taxonomies as $taxonomy) {

        if(!taxonomy_exists($taxonomy)){
            continue;
        }

        $terms = get_the_terms($postID, $taxonomy);
        $termsPerTaxonomy = [];
        foreach ($terms ?: [] as $term) {

            $termName = oes_get_display_title(get_term($term->term_id));

            $termsPerTaxonomy[strip_tags($termName) . $term->term_id] = oes_get_html_anchor(
                '<span>' . (empty($termName) ? $term->name : $termName) . '</span>',
                get_term_link($term->term_id)
            );
        }

        ksort($termsPerTaxonomy);
        $termArray[$taxonomy] = $termsPerTaxonomy;
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

    if (!$oes_post) {
        return '';
    }

    if (!oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
    if (empty($postTypeData['featured_image']) || $postTypeData['featured_image'] === 'none') {
        return '';
    }

    $fieldKey = $postTypeData['featured_image'];
    $image = oes_get_field($fieldKey, $oes_post->object_ID);

    if (!$image) {
        return '';
    }

    if ($args['panel'] ?? true) {
        $imageHTML = oes_get_image_panel_content($image);
    } else {
        $imageHTML = '<figure class="wp-block-image size-full">
                    <img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '" class="wp-image-' . intval($image['id']) . '"/>
                </figure>';
    }

    $header = '';
    if (!empty($args['labels'])) {
        $text = oes_language_label_html($args['labels']);
        $tag = $args['tag'] ?? 'h5';
        $header = sprintf('<%s>%s</%s>', $tag, $text, $tag);
    }

    if ($args['detail'] ?? false) {
        return '<div class="oes-post-featured-image-container">' .
            oes_get_details_block(
                $header,
                $imageHTML
            ) .
            '</div>';
    }

    return '<div class="oes-post-featured-image-container">' .
        $header .
        $imageHTML .
        '</div>';
}

/**
 * Get the html representation of the display title.
 *
 * @param array $args {
 *     Optional. Arguments for building the title.
 *
 * @type string $htmlTag The HTML tag to wrap the title in. Default 'h1'.
 * @type bool $isLink Whether to wrap the title in a link to the post/term. Default false.
 * }
 * @return string Return the html representation of the display title, or an empty string if no title is found.
 */
function oes_get_display_title_html(array $args = []): string
{
    global $oes_post, $oes_term;

    $allowedTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'p'];
    $tag = $args['htmlTag'] ?? 'h1';
    $tag = in_array($tag, $allowedTags, true) ? $tag : 'h1';

    $isLink = $args['isLink'] ?? false;

    $title = null;
    $link = null;

    if ($oes_post) {
        $title = $oes_post->title ?? '';
        if ($isLink) {
            $link = get_permalink($oes_post->object_ID);
        }
    } elseif ($oes_term) {
        $title = $oes_term->title ?? '';
        if ($isLink) {
            $link = get_term_link($oes_term->object_ID);
        }
    }

    if (empty($title)) {
        return '';
    }

    $title = esc_html($title);

    if ($link && !is_wp_error($link)) {
        $title = sprintf('<a href="%s">%s</a>', esc_url($link), $title);
    }

    $classes = isset($args['additionalClasses']) ? explode(' ', $args['additionalClasses']) : [];
    if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
        $classes[] = 'wp-block-heading';
    }

    return sprintf('<%s class="oes-single-title %s">%s</%s>', $tag, implode(' ', $classes), $title, $tag);
}

/**
 * Get the html representation of a post link.
 *
 * @param array $args {
 *     Optional. Arguments for building the link.
 *
 * @type string $htmlTag The HTML tag to wrap the link in. Default 'div'.
 * @type array $labels Optional labels that overwrite the post title.
 * }
 * @return string Return the html representation of the display title, or an empty string if no title is found.
 */
function oes_get_post_link_html(array $args = []): string
{
    global $oes_post;

    if (!$oes_post) {
        return '';
    }

    $allowedTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'p', 'button'];
    $tag = $args['htmlTag'] ?? 'div';
    $tag = in_array($tag, $allowedTags, true) ? $tag : 'div';

    $title = isset($args['labels']) ? (oes_language_label_html($args['labels'])) : ($oes_post->title ?? '');
    $link = get_permalink($oes_post->object_ID);

    if (empty($title)) {
        return '';
    }

    $title = esc_html($title);

    if (!is_wp_error($link)) {
        $class = '';
        if ($tag === 'button') {
            $class = 'wp-block-button__link wp-element-button';
        }
        $title = sprintf('<a href="%s" class="%s">%s</a>', esc_url($link), $class, $title);
    }

    $classes = isset($args['additionalClasses']) ? explode(' ', $args['additionalClasses']) : [];
    if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
        $classes[] = 'wp-block-heading';
    } elseif ($tag == 'button') {
        $classes[] = 'wp-block-button';
        $tag = 'div';

    }

    return sprintf('<%s class="oes-post-link %s">%s</%s>', $tag, implode(' ', $classes), $title, $tag);
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

    if (!$oes_post || !method_exists($oes_post, 'get_author_info')) {
        return '';
    }

    $authorParam = OES()->post_types[$oes_post->post_type]['authors'] ?? '';
    if (!empty($authorParam)) {
        $args['authors'] = $authorParam;
    }

    return $oes_post->get_author_info($args);
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

    if (!$oes_post) {
        return '';
    }

    $vitaParam = OES()->post_types[$oes_post->post_type]['vita'] ?? '';

    if (empty($vitaParam)) {
        return '';
    }

    if($oes_language != 'language0') {
        return oes_get_field_display_value($vitaParam . '_' . $oes_language, $oes_post->object_ID);
    }
    else {
        return oes_get_field_display_value($vitaParam, $oes_post->object_ID);
    }
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

    if (!$oes_post) {
        return '';
    }

    $postTypeData = OES()->post_types[$oes_post->post_type] ?? [];
    foreach (['pub_date', 'edit_date'] as $param) {
        if (isset($args[$param]) && is_bool($args[$param])) {
            if ($args[$param] && !empty($postTypeData[$param])) {
                $args[$param] = $postTypeData[$param];
            }
            else {
                unset($args[$param]);
            }
        }
    }

    if (isset($args['pub_date']) || isset($args['edit_date'])) {
        $versionArgs = $args['version-parameter'] ?? [];

        if (isset($args['version']) && !$args['version']) {
            $versionArgs['skip-version'] = true;
        }

        $versionArgs['style'] = $args['className'] ?? '';

        return $oes_post->get_version_info(
            $args['pub_date'] ?? '',
            $args['edit_date'] ?? '',
            $versionArgs);
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

    if (!$oes_post || !method_exists($oes_post, 'get_translation_info')) {
        return '';
    }

    return $oes_post->get_translation_info($args);
}

/**
 * Get the html representation of the index connection.
 *
 * @param array $args Additional arguments.
 * @return string Return the html representation of the index connection.
 */
function oes_get_index_html(array $args = []): string
{
    global $oes_post, $oes_term;

    if (!$oes_post && !$oes_term) {
        return '';
    }

    if ($args['labels'] ?? false) {
        $args['display-header'] = oes_language_label_html($args['labels']);
    }

    $args['style'] = $args['className'] ?? '';

    if ($oes_post && (!empty($oes_post->part_of_index_pages) || is_attachment() || OES()->block_theme)) {
        return $oes_post->get_index_connections($args['post_type'] ?? '', $args['relationship'] ?? '', $args);
    }
    elseif ($oes_term && (!empty($oes_term->part_of_index_pages) || OES()->block_theme)) {
        return $oes_term->get_index_connections($args['post_type'] ?? '', $args['relationship'] ?? '', $args);
    }
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

    if (!$oes_post || !method_exists($oes_post, 'get_html_metadata_div') || !method_exists($oes_post, 'get_html_metadata_table_string')) {
        return '';
    }

    if (!oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    return $oes_post->get_html_metadata_div(
        $oes_post->get_html_metadata_table_string(),
        array_merge([
            'display-header' => isset($args['labels']) ? (oes_language_label_html($args['labels'])) : '',
            'add-to-toc' => $args['include_toc'] ?? false], $args)
    );
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

    if(!$oes_post || !oes_post_type_is_allowed($oes_post, $args)) {
        return '';
    }

    if (!is_page() || (oes_get_field('field_oes_page_include_toc', $oes_post->object_ID) ?? false)) {

        $className = $args['className'] ?? 'is-style-oes-default';
        $sticky = ($className === 'is-style-oes-sticky');

        $header = '';
        if ($args['labels'] ?? false) {
            $tag = $args['htmlTag'] ?? 'h2';
            $header = sprintf(
                '<%s class="oes-exclude-heading-from-toc oes-content-table-header" id="oes-toc-header">%s</%s>',
                $tag,
                (isset($args['labels']) ? oes_language_label_html($args['labels']) : ''),
                $tag
            );
        }

        if (!$sticky) {
            return $header . '<ul class="oes-table-of-contents oes-vertical-list"></ul>';
        }

        $toggle = '<div id="oes-toc-toggle-host"><button id="oes-toc-toggle" '
            . 'aria-expanded="false" aria-controls="oes-toc-wrapper" '
            . 'aria-label="Inhaltsverzeichnis öffnen"></button></div>';

        $wrapper = '<div id="oes-toc-wrapper">' . $header
            . '<ul class="oes-table-of-contents oes-vertical-list"></ul></div>';

        return $toggle . $wrapper;
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
    if (!isset($args['taxonomy'])) {
        return '';
    }

    global $oes_search, $oes_language;
    $searchTerm = $oes_search->search_term ?? false;
    $termsFound = [];

    if ($term = get_term_by('name', $searchTerm, $args['taxonomy'])) {
        $termsFound[$term->term_id] = sprintf('<a href="%s">%s</a>',
            get_term_link($term),
            $term->name
        );
    }
    if ($term = get_term_by('slug', $searchTerm, $args['taxonomy'])) {
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

/**
 * Get the html representation of a context link.
 *
 * @param array $args
 * @return string
 */
function oes_get_context_link_html(array $args = []): string
{
    global $oes_language;

    $label = $attributes['labels'][$oes_language] ?? __('Open', 'oes');
    $url = $attributes['link'] ?? '';
    $additional = json_decode($attributes['additional'] ?? '', true) ?? [];

    $params = [];

    foreach ($attributes['params'] ?? [] as $param) {

        $value = oes_resolve_context_param($param, $additional);

        if ($value !== null) {
            $params[$param] = $value;
        }
    }

    if (!empty($params)) {
        $url = add_query_arg($params, $url);
    }

    return sprintf('<a href="%s" class="wp-element-button">%s</a>',
    $url,
    $label);
}

/**
 * Resolve the context parameter.
 *
 * @param string $param
 * @param array $args
 * @return mixed|string|null
 */
function oes_resolve_context_param(string $param, array $args = []) {

    global $oes_post;

    if($param == 'translation_id'){

        $language = $args['language'] ?? false;

        if(!$language){
            return $oes_post->translations[0]['id'] ?? null;
        }

        foreach($oes_post->translations as $translation){
            if($translation['language'] == $language){
                return $translation['id'] ?? null;
            }
        }

        return null;
    }

    return match ($param) {

        'object_id' => $oes_post->object_ID ?? null,
        'return_url' => home_url($_SERVER['REQUEST_URI']),
        'split_id' => $oes_post->split_id ?? null,

        default => null
    };
}

/**
 * Determine whether the current OES post's post type passes an optional allow-list.
 *
 * @param object $oes_post The current OES post object (must have a `post_type` property).
 * @param array $args Arguments possibly containing a 'post_types' allow-list.
 * @return bool True if there is no restriction, or the post type is in the list.
 */
function oes_post_type_is_allowed(object $oes_post, array $args): bool
{
    return empty($args['post_types']) || in_array($oes_post->post_type, $args['post_types'], true);
}

/**
 * Build the optional section header used by several template helpers when a 'labels' argument is supplied.
 *
 * @param array $args Arguments possibly containing a 'labels' entry.
 * @return string The rendered header HTML, or an empty string if no labels were given.
 */
function oes_get_language_label_text(array $args, bool $param = false): string
{
    if($param){
        return isset($args['labels']) ? oes_language_label_html($args['labels']) : '';
    }

    return oes_language_label_html($args);
}