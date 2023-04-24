<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//TODO next release : temporary fix for ACF Plugin 6.0, check with new updates
add_filter('acf/pre_load_post_id', 'oes_acf_pre_load_post_id', 10, 2);
function oes_acf_pre_load_post_id($null, $post_id)
{
    if (is_preview()) {
        return get_the_ID();
    } else {
        $acf_post_id = $post_id->ID ?? $post_id;
        return !empty($acf_post_id) ? $acf_post_id : $null;
    }
}