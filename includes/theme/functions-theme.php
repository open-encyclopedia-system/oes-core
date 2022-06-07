<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Add favicon inside <head></head> on page.
 *
 * @param string $href A string containing the link to the image which is to be used as favicon.
 * @param string $imgSize The image size. Recommended size is 16x16 px.
 */
function oes_theme_add_favicon(string $href, string $imgSize = "16x16")
{
    add_action('wp_head', function () use ($href, $imgSize) {
        ?>
        <link rel="icon" href="<?php echo $href; ?>" size="<?php echo $imgSize; ?>">
        <?php
    });
}


/**
 * Add search to top navigation menu at end
 *
 * @param string $label The navigation item label.
 * @param array $args Additional parameters.
 * @return string Return the modified HTML list item.
 */
function oes_theme_add_search_to_navigation(string $label = '', array $args = []): string
{
    return '<li id="menu-item-oes-search" class="menu-item">' .
        '<a id="oes-search" href="javascript:void(0)"' .
        (isset($args['class']) ? (' class="' . $args['class'] . '"') : '') .
        '>' . ($label ?? __('Search', 'oes')) . '</a>' .
        '</li>';
}