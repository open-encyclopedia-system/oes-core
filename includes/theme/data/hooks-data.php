<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('the_content', 'oes_the_content', 12, 1);
add_filter('render_block_core/heading', 'oes_render_block_core_heading', 10, 2);


/**
 * Modify the content while rendering with OES specific content features.
 *
 * @param string $content The content about to be displayed.
 * @return string The modified content.
 */
function oes_the_content(string $content): string
{
    global $oes_frontpage, $oes_post;
    return (!empty($oes_post) && !$oes_frontpage) ?
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
    global $oes_post, $oes_frontpage;
    if ($oes_post instanceof OES_Object && !$oes_frontpage && $oes_post->include_table_of_contents) {

        /* generate new header by adding class and id */
        $headingText = oes_get_text_from_html_heading($block_content);
        $level = $parsed_block['attrs']['level'] ?? 2;
        $block_content = "\n" .
            sprintf('<h%s class="%s" id="%s">%s</h%s>',
                $level,
                'oes-content-table-header',
                oes_replace_string_for_anchor(strip_tags($headingText ?? '')),
                $headingText ?? '',
                $level
            ) .
            "\n";
    }
    return $block_content;
}