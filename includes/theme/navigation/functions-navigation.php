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

        if (!empty($oes_language) && $oes_language !== 'language0' && !empty($templates)) {
            array_splice($templates, 2, 0, [$oes_post->schema_type . '_' . $oes_language]);
        }
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
        $localized_template = str_replace('.php', '-' . $oes_language . '.php', $templates[0]);
        array_unshift($templates, $localized_template);
    }

    return $templates;
}
