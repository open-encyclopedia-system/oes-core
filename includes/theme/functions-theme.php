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
 * Include a modified and styled display of the GND information in frontend box.
 */
function oes_theme_gnd_display_modified_table(){
    add_filter('oes/api_lobid_display_table', '\OES\API\gnd_modify_table_data', 10, 2);
    add_filter('oes/api_lobid_display_entry', '\OES\API\gnd_display_entry', 10, 4);
}


/**
 * Add search to top navigation menu at end
 *
 * @param string $label The navigation item label.
 * @return string Return the modified HTML list item.
 */
function oes_theme_add_search_to_navigation(string $label = ''): string
{
    return '<li id="menu-item-oes-search" class="menu-item">' .
            '<a id="oes-search" href="javascript:void(0)">' . ($label ?: __('Search', 'oes')) . '</a>' .
            '</li>';
}