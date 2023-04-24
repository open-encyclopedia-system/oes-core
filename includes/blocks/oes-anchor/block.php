<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* get global instance */
$oes = OES();

/* block identifier */
$blockID = 'anchor';

/* add to global instance if not yet registered */
if (!isset($oes->blocks['core'][$blockID])) {
    $oes->blocks['core'][$blockID] = [
        'keywords' => ['OES', 'anchor', 'layout'],
        'attributes' => [
            'oes_anchor_id' => ['type' => 'string']
        ],
    ];
}


/**
 * Define the server side callback to render your block in the front end
 *
 * @param array $attributes The attributes that were set on the block or shortcode.
 */
function oes_block_render_anchor(array $attributes): string
{
    $anchorID = $attributes['oes_anchor_id'] ?? false;

    /* modify anchor */
    if($anchorID) $anchorID = oes_replace_string_for_anchor(strip_tags($anchorID));

    $anchorText = '';
    if (isset($_GET['action']) || isset($_GET['context']) || isset($_GET['post_id']))
        $anchorText = sprintf('<span class="oes-anchor-id">%s</span>',
            $anchorID ?
                get_permalink($_GET['post_id']) . '#' . $anchorID :
                '<span class="oes-anchor-no-id">No anchor ID set!</span>');

    return sprintf('<div class="oes-anchor-block" id="%s">%s</div>',
        $anchorID,
        $anchorText
    );
}