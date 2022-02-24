<?php

namespace OES\Navigation;

use WP_Post;
use function OES\ACF\oes_get_field;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* add default acf field groups */
add_action('init', '\OES\Navigation\add_acf_fields_to_navigation' );


/**
 * Add acf groups to menu and menu items.
 */
function add_acf_fields_to_navigation()
{
    /* create two default groups */
    $acfGroups = [
        [
            'key' => 'group_oes_menu_item',
            'title' => 'Menu Item Fields',
            'fields' => [
                [
                    'key' => 'field_link_new_tab',
                    'label' => 'Open Link in New Tab',
                    'name' => 'link_new_tab',
                    'type' => 'true_false',
                ],
                [
                    'key' => 'field_replace_label_image',
                    'label' => 'Replace Label with Image',
                    'name' => 'replace_label_with_image',
                    'type' => 'image',
                ],
                [
                    'key' => 'field_replace_label_icon',
                    'label' => 'Replace Label with Icon',
                    'name' => 'replace_label_with_icon',
                    'type' => 'text',
                    'instructions' => 'Replace the label with a fontawesome icon. Check for icons <a href="https://fontawesome.com/v4.7.0/icons/">https://fontawesome.com/v4.7.0/icons/</a>. E.g, for the twitter icon insert \'fa-twitter\'.',
                ]
            ],
            'location' => [[[
                'param' => 'nav_menu_item',
                'operator' => '==',
                'value' => 'all',
            ]]],
        ]
    ];


    /**
     * Filters the acf groups for navigation
     *
     * @param array $acfGroups The navigation field groups.
     */
    if (has_filter('oes/navigation_acf_field_groups'))
        $acfGroups = apply_filters('oes/navigation_acf_field_groups', $acfGroups);


    /* add field groups */
    if (function_exists('acf_add_local_field_group')) {
        foreach ($acfGroups as $acfGroup) acf_add_local_field_group($acfGroup);
    }
}


/* modify title */
add_filter('nav_menu_item_title', '\OES\Navigation\modify_item_title', 10, 2);


/**
 * Filters a menu item's title and check if the title is to be replaced by an icon or image.
 *
 * @param string $title The menu item's title.
 * @param WP_Post $item The current menu item.
 *
 * @return string The modified title
 */
function modify_item_title(string $title, WP_Post $item): string
{
    if($icon = oes_get_field('field_replace_label_icon', $item->ID))
        return sprintf('<i class="fa %s"></i>', $icon);
    elseif($image = oes_get_field('field_replace_label_image', $item->ID))
        return oes_get_html_img(esc_url($image['url']), esc_attr($image['alt']));
    return $title;
}


/* modify link attributes */
add_filter('nav_menu_link_attributes', '\OES\Navigation\modify_item_link', 10, 2);


/**
 * Filters the HTML attributes applied to a menu item's anchor element.
 *
 * @param array $atts The HTML attributes applied to the menu item's `<a>` element.
 * @param WP_Post $item The current menu item.
 *
 * @return array The modified attributes.
 */
function modify_item_link(array $atts, WP_Post $item): array
{
    /* check if link opens new tab */
    if(oes_get_field('field_link_new_tab', $item->ID)) $atts['target'] = '_blank';
    return $atts;
}