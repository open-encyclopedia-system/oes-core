<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* get global instance */
$oes = OES();

/* block identifier */
$blockID = 'card';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['core'][$blockID])) {
    $oes->blocks['core'][$blockID] = [
        'attributes' => [
            'oes_card_title' => ['type' => 'string'],
            'oes_card_body' => ['type' => 'string'],
            'oes_card_image' => ['type' => 'string'],
            'oes_card_link' => ['type' => 'string'],
            'oes_card_link_text' => ['type' => 'string']
        ],
        'keywords' => ['OES', 'card', 'layout']
    ];
}


/**
 * Define the server side callback to render your block in the front end
 *
 * @param array $attributes The attributes that were set on the block or shortcode.
 * @return string
 */
function oes_block_render_card(array $attributes): string
{

    /* optional image string */
    $imageString = (isset($attributes['oes_card_image']) && $attributes['oes_card_image']) ?
        sprintf('<a href="%s" target="_blank" class="oes-card-img">' .
            '<div class="oes-card-overlay"></div><img class="card-img-top" src="%s" alt="card image"></a>',
            $attributes['oes_card_link'] ?? '',
            $attributes['oes_card_image']) :
        '';

    /* optional link string */
    $linkString = (isset($attributes['oes_card_link']) && $attributes['oes_card_link']) ?
        sprintf('<a href="%s" target="_blank">%s</a>',
            $attributes['oes_card_link'],
            $attributes['oes_card_link_text'] ?? $attributes['oes_card_link']) :
        '';

    return sprintf('<div class="oes-card card">%s' .
        '<div class="card-body"><h2>%s</h2>' .
        '<p class="oes-card-text">%s</p>' .
        '%s</div></div>',
        $imageString,
        $attributes['oes_card_title'] ?? '',
        $attributes['oes_card_body'] ?? '',
        $linkString
    );
}