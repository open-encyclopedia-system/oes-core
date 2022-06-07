<?php

use function OES\ACF\oes_get_field;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* get global instance */
$oes = OES();

/* block identifier */
$blockID = 'post-content';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID]))
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-post-content',
        'title' => 'OES Post Content',
        'render_callback' => 'oes_block_render_post_content',
        'enqueue_style' => plugins_url($oes->basename . '/includes/blocks/acf-pro/oes-post-content/block.css'),
        'keywords' => ['OES', 'content'],
        'mode' => 'auto',
        'field_group' => [
            'title' => 'OES Content',
            'fields' => [
                [
                    'key' => 'field_block__post_content__post',
                    'label' => 'Post',
                    'name' => 'block__post_content__post',
                    'type' => 'post_object',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_block__post_content__paragraphs',
                    'label' => 'Number of Paragraphs',
                    'name' => 'block__post_content__paragraphs',
                    'type' => 'number',
                    'min' => 1,
                    'default_value' => 2
                ],
                [
                    'key' => 'field_block__post_content__exclude_link',
                    'label' => 'Exclude Post Link',
                    'name' => 'block__post_content__exclude_link',
                    'type' => 'true_false',
                ]
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-post-content',
            ]]]
        ]
    ];


/**
 * Define the server side callback to render your block in the front end.
 */
function oes_block_render_post_content(){

    /* get block fields */
    $previewPost = oes_get_field('block__post_content__post') ?? false;
    $n = oes_get_field('block__post_content__paragraphs') ?? 2;
    $excludeLink = oes_get_field('block__post_content__exclude_link') ?? false;

    /* add warning if no valid post is selected. */
    if ($previewPost instanceof WP_Post):
        ?>
        <div class="oes-post-content <?php echo $block['className'] ?? ''; ?>"><?php

            /* add post link */
            $preview = $excludeLink ? '' :
                '<a href="' . get_permalink($previewPost) . '">' . oes_get_display_title($previewPost) . '</a>';

            /* prepare content */
            $allParagraphs = explode('</p>', wpautop($previewPost->post_content));
            if (sizeof($allParagraphs) <= $n) $preview = wpautop($previewPost->post_content);
            else
                for ($i = 0; $i < $n; $i++) {

                    /* skip if empty */
                    if (!in_array($allParagraphs[$i], [
                        "<p><!-- wp:paragraph -->",
                        "\n<p><!-- wp:paragraph -->",
                        "\n<p><!-- /wp:paragraph -->",
                        "<p><!--/wp:paragraph -->"]))
                        $preview .= $allParagraphs[$i] . '</p>';
                    else $n++;
                }

            echo $preview;
            ?></div>
    <?php
    else:
        ?>
        <div class="oes-warning">No valid post selected.</div><?php
    endif;
}