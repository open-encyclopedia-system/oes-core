<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/* get global instance */
$oes = OES();

/* block identifier */
$blockID = 'image-panel';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID]))
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-image-panel',
        'title' => 'OES Image Panel',
        'render_callback' => 'oes_render_image_panel',
        'keywords' => ['OES', 'Image', 'Panel'],
        'mode' => 'auto',
        'field_group' => [
            'key' => 'group_oes_image_panel',
            'title' => 'OES Image Panel',
            'fields' => [
                [
                    'key' => 'field_figure',
                    'label' => 'Image',
                    'name' => 'figure',
                    'type' => 'image',
                ],
                [
                    'key' => 'field_figure_title',
                    'label' => 'Title',
                    'instructions' => 'Image title if empty',
                    'name' => 'figure_title',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_figure_number',
                    'label' => 'Number',
                    'name' => 'figure_number',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_figure_label',
                    'label' => 'Label',
                    'name' => 'figure_label',
                    'type' => 'text',
                ],
                [
                    'key' => 'field_figure_expanded',
                    'label' => 'Expanded',
                    'name' => 'figure_expanded',
                    'type' => 'true_false',
                ],
                [
                    'key' => 'field_figure_bootstrap',
                    'label' => 'Use Bootstrap',
                    'name' => 'figure_bootstrap',
                    'type' => 'true_false',
                    'default_value' => true
                ]
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-image-panel',
            ]]]
        ]
    ];


/**
 * Define the server side callback to render the block 'OES Image Panel' in the frontend.
 * @return void
 */
function oes_render_image_panel(): void
{
    if ($image = oes_get_field('figure') ?? false) {

        $panelTitle = oes_get_field('figure_title') ?? '';
        if (empty($panelTitle) && $image['title']) $panelTitle = $image['title'];

        echo oes_get_image_panel_html([
            'figure' => $image,
            'figure_number' => oes_get_field('figure_number') ?? '',
            'figure_include' => true
        ], [
            'label_prefix' => oes_get_field('figure_label') ?? '',
            'panel_title' => $panelTitle,
            'bootstrap' => oes_get_field('figure_bootstrap') ?? true,
            'active' => oes_get_field('figure_expanded') ?? true
        ]);

    } else
        echo '<span style="color:red;font-style:italic">Image Block: No valid Image selected</span>';
}