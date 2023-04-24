<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/* get global instance */
$oes = OES();

/* block identifier */
$blockID = 'gallery-panel';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID]))
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-gallery-panel',
        'title' => 'OES Gallery Panel',
        'render_callback' => 'oes_render_gallery_panel',
        'keywords' => ['OES', 'Gallery', 'Panel'],
        'mode' => 'auto',
        'field_group' => [
            'key' => 'group_oes_gallery_panel',
            'title' => 'OES Gallery Panel',
            'fields' => [
                [
                    'key' => 'field_gallery_title',
                    'label' => 'Title',
                    'instructions' => 'image title if empty',
                    'name' => 'gallery_title',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_gallery_number',
                    'label' => 'Number',
                    'name' => 'gallery_number',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_gallery_repeater',
                    'name' => 'gallery_repeater',
                    'label' => 'Images',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'collapsed' => 'field_gallery_figure_title',
                    'sub_fields' => [
                        [
                            'key' => 'field_gallery_figure',
                            'label' => 'Image',
                            'name' => 'gallery_figure',
                            'type' => 'image',
                        ],
                        [
                            'key' => 'field_gallery_figure_number',
                            'instructions' => 'compute on empty',
                            'label' => 'Number',
                            'name' => 'gallery_figure_number',
                            'type' => 'text',
                        ]
                    ]
                ],
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-gallery-panel',
            ]]]
        ]
    ];


/**
 * Define the server side callback to render the block 'OES Gallery Panel' in the frontend.
 * @return void
 */
function oes_render_gallery_panel(): void
{
    if ($figures = oes_get_field('gallery_repeater') ?? false)
        echo oes_get_gallery_panel_html($figures,
            ['gallery_title' => oes_get_field('gallery_title') ?? 'NO HEADER',
                'bootstrap' => false]
        );
    else
        echo '<span style="color:red;font-style:italic">Image Block: No valid Image selected</span>';
}