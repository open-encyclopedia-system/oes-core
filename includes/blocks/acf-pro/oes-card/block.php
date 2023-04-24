<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/* get global instance */
$oes = OES();


/* add admin style */
$oes->assets->add_style('oes-block-card-admin', '/includes/blocks/acf-pro/oes-card/block-editor.css', [], null, 'all', true);


/* block identifier */
$blockID = 'card';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID]))
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-card',
        'title' => 'OES Card',
        'render_callback' => 'oes_block_render_card',
        'keywords' => ['OES', 'layout', 'Card'],
        'enqueue_style' => plugins_url(OES()->basename . '/includes/blocks/acf-pro/oes-card/block.css'),
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
        'field_group' => [
            'title' => 'OES Card',
            'fields' => [
                [
                    'key' => 'block_field__title',
                    'label' => 'Title',
                    'name' => 'title',
                    'type' => 'text'
                ],
                [
                    'key' => 'block_field__image',
                    'label' => 'Image',
                    'name' => 'image',
                    'type' => 'image'
                ],
                [
                    'key' => 'block_field__image_link',
                    'label' => 'Image Link',
                    'name' => 'image_link',
                    'type' => 'link'
                ]
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-card',
            ]]]
        ]
    ];


/**
 * Define the server side callback to render your block in the front end.
 * @return void
 */
function oes_block_render_card(): void
{

    /* optional image string */
    $image = oes_get_field('image');
    $imageString = '';
    if(!empty($image)){
        if($imageLink = oes_get_field('image_link') ?? false){
            $imageString = sprintf('<a href="%s" target="%s" class="oes-card-img">' .
                    '<div class="oes-card-overlay"></div><img class="card-img-top" src="%s" alt="card image"></a>',
                $imageLink['url'] ?? '',
                $imageLink['target'] ?? '_self',
                $image['url']);
        }
        else{
            $imageString = sprintf('<img class="card-img-top" src="%s" alt="card image">',
                $image['url']);
        }
    }

    printf('<div class="oes-card card">%s' .
        '<div class="card-body"><span class="oes-card-header">%s</span>' .
        '<p class="oes-card-text"><InnerBlocks /></p></div></div>',
        $imageString,
        oes_get_field('post_title') ?? ''
    );
}