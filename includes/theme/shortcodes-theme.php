<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_shortcode('oes_theme_label', 'oes_theme_label_html');
add_shortcode('oes_table_of_contents', 'oes_table_of_contents_html');
add_shortcode('oes_breadcrumbs', 'oes_breadcrumbs_html');
add_shortcode('oes_post_terms', 'oes_post_terms_html');
add_shortcode('oes_reading_time', 'oes_reading_time_html');


/**
 * Get the HTML representation of theme label.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the theme label.
 */
function oes_theme_label_html(array $args): string
{
    $args = array_merge([
        'label' => false,
        'default' => false,
        'class' => false,
        'wrapper-class' => ''
    ], $args);

    global $oes, $oes_language;
    $label = '';
    $language = (empty($oes_language) || $oes_language === 'all') ? $oes->main_language : $oes_language;
    if ($args['label'] && isset($oes->theme_labels[$args['label']][$language]))
        $label = $oes->theme_labels[$args['label']][$language];
    if (empty($label) && isset($args['default']))
        $label = $args['default'];

    return $args['class'] ?
        sprintf('<div class="%s"><div class="%s">%s</div></div>',
            $args['wrapper-class'],
            $args['class'],
            $label
        ) :
        $label;
}


/**
 * Get the HTML representation of table of content of an OES object.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the table of contents.
 */
function oes_table_of_contents_html(array $args): string
{
    $args = array_merge(['class' => ''], $args);
    global $oes_post;
    if (!empty($oes_post) && !empty($oes_post->table_of_contents)) {
        $header = isset($args['header']) ?
            $oes_post->generate_table_of_contents_header(
                $args['header'],
                $args['level'] ?? 2,
                ['add-to-toc' => false]) :
            '';
        return '<div class="' . $args['class'] . '">' .
            $header .
            $oes_post->get_html_table_of_contents(['toc-header-exclude' => true]) .
            '</div>';
    }
    return '';
}


/**
 * Get the HTML representation of breadcrumbs of an OES object.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing breadcrumbs.
 */
function oes_breadcrumbs_html(array $args): string
{
    global $oes_post;
    $args = array_merge(['header' => ($args['header'] ?? false)], $args);
    if (!empty($oes_post)) return $oes_post->get_breadcrumbs_html($args);
    return '';
}


/**
 * Get the HTML representation of connected terms.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the connected terms.
 */
function oes_post_terms_html(array $args): string
{
    /* check for tags */
    global $oes_post;

    $args['taxonomies'] = (isset($args['taxonomies']) ?
        explode(',', $args['taxonomies']) :
        []);

    $args = array_merge(['class' => 'oes-sidebar-tags', 'header' => ($args['header'] ?? false)], $args);
    if (!empty($oes_post)) {
        $tagString = $oes_post->get_html_terms($args['taxonomies'], $args);
        return (!empty($tagString) ?
            ('<div class="' . $args['class'] . '">' . $tagString . '</div>') :
            '');
    }
    return '';
}


/**
 * Get the HTML representation of post reading time.
 *
 * @param array $args Shortcode attributes.
 *
 * @return string Return the html string representing the reading time.
 */
function oes_reading_time_html(array $args): string
{
    $args = array_merge([
        'label' => 'theme_label:general__reading_time',
        'post_id' => get_the_ID(),
        'wpm' => '300',
        'unit' => ' min',
        'class' => 'oes-reading-time',
        'wrapper-class' => 'oes-reading-time-wrapper',
        'tooltip' => true
    ], $args);

    global $oes, $oes_language;
    $language = (empty($oes_language) || $oes_language === 'all') ? $oes->main_language : $oes_language;

    /* get the content */
    $content = get_the_content($args['post_id']);

    /* add time for images */
    $imagesCount = substr_count(strtolower($content), '<img ');

    $content = wp_strip_all_tags($content);
    $wordsCount = count(preg_split('/\s+/', $content));

    $time = $args['wpm'] > 0 ? $wordsCount / $args['wpm'] : 0;

    $minPerImage = 1 / 10;
    if ($imagesCount > 0)
        $time += $imagesCount * $minPerImage;

    /* only display even minutes */
    $cleanTime = ($time < 1) ? '< 1' : ceil($time);

    $tooltip = '';
    if ($args['tooltip']) {
        $tooltip = '<div class="oes-tooltip">' .
            '<span class="oes-tooltip-icon"></span>' .
            '<span class="oes-tooltip-text">' .
            sprintf(__('The reading time is calculated with %s words per minute.', 'oes'), $args['wpm']) . '</span>' .
            '</div>';
    }

    return sprintf('<div class="%s"><div class="%s">%s</div>%s</div>',
        $args['wrapper-class'],
        $args['class'],
        ($oes->theme_labels[$args['label']][$language] ?? 'Reading Time: ') . $cleanTime . $args['unit'],
        $tooltip
    );
}