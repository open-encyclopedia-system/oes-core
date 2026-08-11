<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Navigation;


/**
 * Redirect templates according to object type.
 *
 * @param array $templates The template hierarchy.
 * @return array The modified template hierarchy.
 */
function redirect_page(array $templates): array
{
    global $oes_post, $oes_archive_data, $oes_is_index, $oes_is_index_page, $oes_language;

    if (!empty($oes_post->is_frontpage)) {
        array_unshift($templates, 'front-page.php');
    }
    elseif (!empty($oes_post) && in_array($oes_post->schema_type, [
            'single-article',
            'single-contributor',
            'single-index'
        ], true)) {
        array_splice($templates, 2, 0, [$oes_post->schema_type]);
    }
    elseif (!empty($oes_is_index_page)) {
        array_unshift($templates, 'archive-index');
    }
    elseif (!empty($oes_is_index) && is_archive()) {
        array_splice($templates, 1, 0, ['archive-index']);
    }
    elseif (!empty($oes_archive_data) && !is_archive() && !is_search()) {
        $archive_template = 'archive' . ($oes_is_index ? '-index' : '');

        if (!empty($templates) && $templates[0] !== '404.php') {
            array_splice($templates, 1, 0, [$archive_template]);
        } else {
            array_unshift($templates, $archive_template);
        }
    }

    if (!empty($oes_language) && $oes_language !== 'language0' && !empty($templates)) {
        $localized = [];

        foreach ($templates as $template) {

            // TODO include legacy underscore? article_language1.
            if (str_ends_with($template, '.php')) {
                $base = str_replace('.php', '', $template);
                $localized[] = "{$base}-{$oes_language}.html";
                $localized[] = "{$base}-{$oes_language}.php";
                $localized[] = "{$base}";
            } else {
                $localized[] = "{$template}-{$oes_language}";
            }

            $localized[] = $template;
        }

        $templates = $localized;
    }

    return $templates;
}
