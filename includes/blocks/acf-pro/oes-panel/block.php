<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;


/* get global instance */
$oes = OES();

/* add admin style */
$oes->assets->add_style('oes-block-panel-admin', '/includes/blocks/acf-pro/oes-panel/block-editor.css', [], null, 'all', true);


/* block identifier */
$blockID = 'panel';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['acf_pro'][$blockID])) {
    $oes->blocks['acf_pro'][$blockID] = [
        'name' => 'oes-panel',
        'title' => 'OES Panel',
        'render_callback' => 'oes_block_render_panel',
        'keywords' => ['OES', 'panel', 'layout'],
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
        'field_group' => [
            'title' => 'OES Content',
            'fields' => [
                [
                    'key' => 'block_field__panel_title',
                    'label' => 'Title',
                    'name' => 'panel_title',
                    'type' => 'text',
                ],
                [
                    'key' => 'block_field__panel_number',
                    'label' => 'Number',
                    'name' => 'panel_number',
                    'type' => 'text',
                ],
                [
                    'key' => 'block_field__panel_number_prefix',
                    'label' => 'Number Prefix',
                    'name' => 'panel_number_prefix',
                    'type' => 'text',
                ],
                [
                    'key' => 'block_field__panel_expanded',
                    'label' => 'Expanded',
                    'name' => 'panel_expanded',
                    'type' => 'true_false',
                ],
                [
                    'key' => 'block_field__panel_class',
                    'label' => 'Class',
                    'name' => 'panel_class',
                    'type' => 'text',
                ],
            ],
            'location' => [[[
                'param' => 'block',
                'operator' => '==',
                'value' => 'acf/oes-panel',
            ]]]
        ]
    ];
}


/**
 * Define the server side callback to render your block in the front end.
 * @return void
 */
function oes_block_render_panel(): void
{
    echo '<div class="' . (oes_get_field('panel_class') ?? '') . '">' .
        oes_get_panel_html('<InnerBlocks />', [
            'caption' => oes_get_field('panel_title') ?? '',
            'number-prefix' => oes_get_field('panel_number_prefix') ?? '',
            'number' => oes_get_field('panel_number') ?? '',
            'bootstrap' => false,
            'active' => isset($_POST['post']) ?
                true :
                (oes_get_field('panel_expanded') ?? false)
        ]) .
        '</div>';
}