<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\acf_post_id;


/* get global OES instance parameter */
$oes = OES();

/* block identifier */
$blockID = 'table-of-contents';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['core'][$blockID]))
    $oes->blocks['core'][$blockID] = [
        'attributes' => [
            'oes_block_title' => ['type' => 'string']
        ],
        'keywords' => ['OES', 'toc', 'Table of Contents']
    ];


/**
 * Define the server side callback to render your block in the front end
 *
 * @param array $attributes The attributes that were set on the block or shortcode.
 * @return string
 */
function oes_block_render_table_of_contents(array $attributes): string
{
    /* @var $editMode string check if in admin dashboard and edit mode */
    $editMode = strrpos($_SERVER['REQUEST_URI'], "context=edit");

    /* create table of contents header */
    $tocHeader = sprintf('<h1 class="oes-content-table-header" id="toc-header">%s</h1>',
        $attributes['oes_block_title'] ?? 'Table of Contents'
    );

    /* get post object */
    //@oesDevelopment Create OES_Page instead of OES_Post, fix header generation ('-' instead of '_')
    global $post;
    $postObject = new OES_Post($post ? $post->ID : acf_post_id());

    /* loop through headings */
    $headingsList = '';
    $postObject->generate_headers_for_toc_in_content();
    $tableOfContent = $postObject->get_table_of_contents();

    if (!empty($tableOfContent)) {

        /* open list */
        $headingsList .= '<ul class="oes-table-of-contents">';

        foreach ($tableOfContent as $header) {
            $headingString = $editMode ? $header['label'] :
                oes_get_html_anchor(
                    $header['label'],
                    '#' . $header['anchor'],
                    'toc_' . $header['anchor'],
                    'toc-anchor');

            $headingsList .= sprintf('<li class="oes-toc-header%s">%s</a></li>',
                $header['level'],
                $headingString
            );
        }

        /* close list */
        $headingsList .= '</ul>';
    }

    /* return empty if no headings found */
    if (empty($headingsList)) return '';

    return sprintf('<div class="oes-table-of-contents-wrapper">%s%s</div>',
        $tocHeader,
        $headingsList
    );
}