<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* get global OES instance parameter */
$oes = OES();

/* block identifier */
$blockID = 'featured-post';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['core'][$blockID])) {
    $oes->blocks['core'][$blockID] = [
        'attributes' => [
            'oes_post' => ['type' => 'string']
        ],
        'keywords' => ['OES', 'featured', 'post']
    ];
}


/**
 * Define the server side callback to render your block in the front end
 *
 * @param array $attributes The attributes that were set on the block or shortcode.
 * @return string
 */
function oes_block_render_featured_post(array $attributes): string
{
    /* get term */
    $featuredPostID = $attributes['oes_post'] ?? false;
    $featuredPost = $featuredPostID ? get_post($featuredPostID) : false;
    if ($featuredPost instanceof WP_Post) {

        /* get rendered html representation */
        $postType = $featuredPost->post_type;
        $featuredPost = class_exists($postType) ? new $postType($featuredPost->ID) : new OES_Post($featuredPost->ID);

        if (method_exists($featuredPost, 'get_html_featured_post'))
            $content = $featuredPost->get_html_featured_post([]);
        else
            $content = '<span class="oes-notice">' .
                __('Featured Post: No render method for post type.', 'oes') . '</span>' . $postType;
    }
    else{
        $content = '<span class="oes-notice">' .
            __('Featured Post: No post selected.', 'oes') . '</span>';
    }

    return $content;
}