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
    global $oes_post, $oes_language;

    if (!empty($oes_post->is_frontpage)) {
        array_unshift($templates, 'front-page.php');
    } elseif (!empty($oes_post)) {
        $templates = insert_object_templates($templates, $oes_post);
    }

    $templates = insert_archive_templates($templates);

    if (!empty($oes_language) && $oes_language !== 'language0') {
        $templates = localize_templates($templates, $oes_language);
    }

    return $templates;
}

/**
 * @param array $templates
 * @param object $oes_post
 * @return array
 */
function insert_object_templates(array $templates, object $oes_post): array
{
    $schema = $oes_post->schema;
    $oesType = $oes_post->type;

    $insert = [];
    if ($schema !== 'Thing' && $oesType !== 'none') {
        $insert[] = $oesType . '-' . $schema;   // e.g. single-contributor-Person
    }
    if ($schema !== 'Thing') {
        $insert[] = 'single-' . $schema;        // e.g. single-Person
    }
    if ($oesType !== 'none') {
        $insert[] = $oesType;                   // e.g. single-contributor
    }

    if ($insert) {
        array_splice($templates, 2, 0, $insert);
    }
    return $templates;
}

/**
 * @param array $templates
 * @return array
 */
function insert_archive_templates(array $templates): array
{
    global $oes_archive_data, $oes_is_index, $oes_is_index_page;

    if (!empty($oes_is_index_page)) {
        array_unshift($templates, 'archive-index');
    } elseif (!empty($oes_is_index) && is_archive()) {
        array_splice($templates, 1, 0, ['archive-index']);
    } elseif (!empty($oes_archive_data) && !is_archive() && !is_search()) {
        $archiveTemplate = 'archive' . ($oes_is_index ? '-index' : '');
        if (!empty($templates) && $templates[0] !== '404.php') {
            array_splice($templates, 1, 0, [$archiveTemplate]);
        } else {
            array_unshift($templates, $archiveTemplate);
        }
    }
    return $templates;
}

/**
 * @param array $templates
 * @param string $lang
 * @return array
 */
function localize_templates(array $templates, string $lang): array
{
    if (empty($templates)) {
        return $templates;
    }

    $localized = [];
    foreach ($templates as $template) {
        if (str_ends_with($template, '.php')) {
            $base = str_replace('.php', '', $template);
            $localized[] = "{$base}-{$lang}.html";
            $localized[] = "{$base}-{$lang}.php";
        } else {
            $localized[] = "{$template}-{$lang}";
        }
        $localized[] = $template;
    }
    return $localized;
}