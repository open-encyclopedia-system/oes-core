<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Block;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Register custom block categories for OES.
 *
 * @param array $categories Existing block categories.
 * @return array Modified block categories.
 */
function register_categories(array $categories): array
{
    return array_merge($categories, [
        [
            'slug'  => 'oes-schema',
            'title' => __('OES Schema', 'oes'),
        ],
        [
            'slug'  => 'oes-filter',
            'title' => __('OES Filter', 'oes'),
        ],
    ]);
}

/**
 * Register all OES custom blocks.
 *
 * @return void
 */
function register(): void
{
    $blockDir = __DIR__;

    $blocks = [
        'archive-count',
        'archive-loop',
        'author-byline',
        'author-vita',
        'citation',
        'cite-as',
        'empty-result',
        'featured-image',
        'featured-post',
        'filter',
        'filter-active',
        'filter-alphabet',
        'filter-index',
        'index',
        'language-label',
        'language-switch',
        'literature',
        'metadata',
        'notes',
        'panel'         => ['render_callback' => '\OES\Block\render_panel_block'],
        'panel-image'   => ['render_callback' => '\OES\Block\render_image_panel_block'],
        'panel-gallery' => ['render_callback' => '\OES\Block\render_gallery_panel_block'],
        'print',
        'print-page-break',
        'search-panel',
        'search-terms',
        'share-link',
        'table-of-contents',
        'terms',
        'translation',
        'title',
        'title-page',
        'version',
    ];

    /**
     * Allow filters to modify the list of OES blocks before registration.
     *
     * @param array $blocks List of block slugs to register.
     */
    $blocks = apply_filters('oes/blocks_register', $blocks);

    if (empty($blocks)) {
        return;
    }

    foreach ($blocks as $block => $blockData) {

        $blockKey = is_string($blockData) ? $blockData : $block;
        $blockArgs = is_string($blockData) ? [] : $blockData;

        $blockPath = "{$blockDir}/{$blockKey}/build";
        if (is_dir($blockPath)) {
            register_block_type($blockPath, $blockArgs);
        }
    }
}

/**
 * Enqueue global OES block-related styles.
 *
 * @return void
 */
function assets(): void
{
    $baseURL = plugin_dir_url(__FILE__);

    wp_enqueue_style('oes-tables', $baseURL . 'tables.css');
    wp_enqueue_style('oes-lists', $baseURL . 'lists.css');
}

/**
 * Register additional custom block styles for core blocks.
 *
 * @return void
 */
function register_block_styles(): void
{
    $styles = [
        'oes-default' => __('OES Default', 'oes'),
        'oes-simple'  => __('OES Simple', 'oes'),
        'oes-list'    => __('OES List', 'oes'),
    ];

    foreach ($styles as $name => $label) {
        register_block_style('core/table', [
            'name'  => $name,
            'label' => $label,
        ]);
    }
}

/**
 * Server-side render callback for the Panel block.
 *
 * @param array $attributes Block attributes from the editor.
 * @param string $content Inner content of the block.
 * @return string Rendered HTML.
 */
function render_panel_block(array $attributes, string $content): string
{
    $title = $attributes['panel_title'] ?? '';
    $expanded = $attributes['panel_expanded'] ?? false;
    $class = $attributes['className'] ?? '';
    $anchor = $attributes['anchor'] ?? '';

    return '<div class="' . esc_attr($class) . '" id="' . esc_attr($anchor) . '">'
        . oes_get_panel_html($content, [
            'caption' => esc_html($title),
            'active'  => is_admin() ? true : $expanded,
        ])
        . '</div>';
}

/**
 * Server-side render callback for the Image Panel block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML or empty string if no image is set.
 */
function render_image_panel_block(array $attributes): string
{
    $image = $attributes['figure'] ?? null;
    $title = $attributes['figure_title'] ?? '';
    $addNumber = $attributes['figure_number'] ?? true;
    $expanded = $attributes['figure_expanded'] ?? true;
    $class = $attributes['className'] ?? '';
    $anchor = $attributes['anchor'] ?? '';

    if (!$image || !isset($image['url'])) {
        return ''; // No image = no output
    }

    $panelTitle = '';
    if ($title !== 'none' && $title !== '') {
        $panelTitle = esc_html($title);
        if ($addNumber) {
            $panelTitle = 'Figure: ' . $panelTitle;
        }
    }

    return sprintf(
        '<div class="%s" id="%s">%s</div>',
        esc_attr($class),
        esc_attr($anchor),
        oes_get_image_panel_html($image, [
            'caption'     => $panelTitle,
            'active'      => is_admin() ? true : $expanded,
            'add_number'  => $addNumber
        ])
    );
}

/**
 * Server-side render callback for the Gallery Panel block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML or empty string if no images are selected.
 */
function render_gallery_panel_block(array $attributes): string
{
    $title = $attributes['gallery_title'] ?? '';
    $number = $attributes['gallery_number'] ?? false;
    $expanded = $attributes['gallery_expanded'] ?? false;
    $figures = $attributes['images'] ?? [];
    $anchor = $attributes['anchor'] ?? '';
    $class = $attributes['className'] ?? '';

    if (empty($figures)) {
        return '';
    }

    return sprintf(
        '<div class="%s" id="%s">%s</div>',
        esc_attr($class),
        esc_attr($anchor),
        oes_get_gallery_panel_html($figures, [
            'caption'      => $title,
            'add_number'   => $number,
            'is_expanded'  => (bool) $expanded
        ])
    );
}