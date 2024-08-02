<?php

namespace OES\Navigation;


/**
 * Redirect templates according to object type.
 *
 * @param array $templates The template hierarchy.
 * @return array The modified template hierarchy.
 */
function redirect_page(array $templates): array
{
    global $oes_post, $oes_archive_data, $oes_is_index, $oes_is_index_page;
    if ($oes_post->is_frontpage ?? false)
        array_unshift($templates, 'front-page.php');
    elseif ($oes_post &&
        in_array($oes_post->schema_type, ['single-article', 'single-contributor', 'single-index']))
        array_splice( $templates, 2, 0, [$oes_post->schema_type]);
    elseif ($oes_is_index_page)
        array_unshift($templates, 'archive-index');
    elseif ($oes_archive_data && !is_archive() && !is_search()) {
        $template = 'archive' . ($oes_is_index ? '-index' : '');
        if (sizeof($templates) > 0 && $templates[0] !== '404.php')
            array_splice($templates, 1, 0, [$template]);
        else array_unshift($templates, $template);
    }

    /* add language dependent template */
    global $oes_language;
    if($oes_language !== 'language0' && sizeof($templates) > 0)
        array_unshift($templates, str_replace('.php', '-' . $oes_language . '.php', $templates[0]));

    return $templates;
}