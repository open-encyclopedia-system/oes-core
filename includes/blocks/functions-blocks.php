<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get field data from block
 *
 * @param string $fieldName The field name or key
 * @param mixed $block The block data
 * @param mixed $postID The post_id of which the value is saved against
 *
 * @return false|mixed Return the block data or false.
 */
function get_block_data(string $fieldName, $block, $postID = false)
{
    if (is_preview()) return $block['data'][$fieldName] ?? false;
    else return \OES\ACF\oes_get_field($fieldName, $postID) ?? false;
}


/**
 * Default render output if no callback defined.
 * @return void
 */
function default_block_render(): void
{
    echo 'No render callback or template defined.';
}