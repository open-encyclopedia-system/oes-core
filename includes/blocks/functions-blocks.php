<?php

namespace OES\Block;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Register new category for OES Blocks.
 */
function category($categories): array
{
    $categories[] = [
        'slug' => 'oes-schema',
        'title' => __('OES Schema', 'oes')
    ];
    $categories[] = [
        'slug' => 'oes-filter',
        'title' => __('OES Filter', 'oes')
    ];
    return $categories;
}


/**
 * Register OES blocks.
 *
 * @return void
 */
function register(): void {

    $blocks = [
        'archive-count' => [],
        'archive-loop' => [],
        'author-byline' => [],
        'author-vita' => [],
        'citation' => [],
        'empty-result' => [],
        'featured-image' => [],
        'featured-post' => [],
        'filter' => [],
        'filter-active' => [],
        'filter-alphabet' => [],
        'filter-index' => [],
        'index' => [],
        'language-label' => [],
        'language-switch' => [],
        'literature' => [],
        'metadata' => [],
        'notes' => [],
        'print' => [],
        'search-panel' => [],
        'table-of-contents' => [],
        'terms' => [],
        'translation' => [],
        'title' => [],
        'title-page' => [],
        'version' => []
    ];


    /**
     * Filters OES blocks to be registered.
     *
     * @param array $blocks The OES block configurations.
     */
    $blocks = apply_filters('oes/blocks_register', $blocks);


    /* register all blocks */
    foreach($blocks as $block => $blockArgs)
        register_block_type(__DIR__ . '/' . $block . '/build', $blockArgs);
}


/**
 * Enqueue assets.
 *
 * @return void
 */
function assets(): void
{
    wp_enqueue_style(
        'oes-tables',
        plugins_url(OES_BASENAME . '/includes/blocks/tables.css')
    );

    wp_enqueue_style(
        'oes-lists',
        plugins_url(OES_BASENAME . '/includes/blocks/lists.css')
    );
}


/**
 * Register additional OES block styles.
 *
 * @return void
 */
function register_block_styles(): void
{
    register_block_style(
        'core/table',
        [
            'name'  => 'oes-default',
            'label' => 'OES Default'
        ]
    );

    register_block_style(
        'core/table',
        [
            'name'  => 'oes-simple',
            'label' => 'OES Simple'
        ]
    );

    register_block_style(
        'core/table',
        [
            'name'  => 'oes-list',
            'label' => 'OES List'
        ]
    );
}