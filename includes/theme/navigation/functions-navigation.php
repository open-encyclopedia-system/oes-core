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
    global $oes_post, $oes_archive_data, $oes_is_index;
    if ($oes_post->is_frontpage ?? false)
        array_unshift($templates, 'front-page.php');
    elseif ($oes_post &&
        in_array($oes_post->schema_type, ['single-article', 'single-contributor', 'single-index']))
        array_unshift($templates, $oes_post->schema_type);
    elseif ($oes_archive_data && $oes_is_index)
        array_unshift($templates, 'archive-index');

    return $templates;
}