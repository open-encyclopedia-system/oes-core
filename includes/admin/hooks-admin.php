<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_head', 'oes_admin_head');


/**
 * Add favicon to WordPress admin pages.
 * @return void
 */
function oes_admin_head(): void
{
    echo '<link rel="icon" type="image/x-icon" href="' . plugin_dir_url(__FILE__) . '../../assets/images/favicon.ico" />';
}