<?php

namespace OES\Navigation;

use Walker_Nav_Menu_Checklist;
use WP_Post;


/**
 * Add OES menu meta box to theme menu page
 */
function admin_head_nav_menus(): void
{
    add_meta_box(
        'oes_menu_items',
        __('OES Menu Items', 'oes'),
        '\OES\Navigation\render_menu_meta_box',
        'nav-menus',
        'side',
        'high',
        []);
}


/**
 * Add default acf field groups
 */
function init(): void
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
                    'key' => 'field_link_disable',
                    'label' => 'Disable Link',
                    'name' => 'field_link_disable',
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
                    'instructions' => 'Replace the label with an svg icon. Enter the svg file name including the ' .
                        'relative path.',
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
    $acfGroups = apply_filters('oes/navigation_acf_field_groups', $acfGroups);


    /* add field groups */
    if (function_exists('acf_add_local_field_group')) {
        foreach ($acfGroups as $acfGroup) acf_add_local_field_group($acfGroup);
    }
}


/**
 * Displays a menu meta_box
 *
 * @return void
 */
function render_menu_meta_box(): void
{
    global $nav_menu_selected_id;

    /* Create an array of objects that imitate Post objects */
    $menuItems = [
        (object)[
            'ID' => 1,
            'attr_title' => 'Open OES Search Panel',
            'classes' => ['oes-menu-item-search'],
            'db_id' => 0,
            'menu_item_parent' => 0,
            'object' => 'oes-menu-item-search',
            'object_id' => 1,
            'post_parent' => 0,
            'target' => '',
            'title' => 'Search',
            'type' => 'oes-item',
            'type_label' => 'OES Menu',
            'url' => '',
            'description' => 'Add button to open OES search panel',
            'xfn' => ''
        ],
        (object)[
            'ID' => 2,
            'db_id' => 0,
            'menu_item_parent' => 0,
            'object_id' => 2,
            'post_parent' => 0,
            'type' => 'oes-item',
            'object' => 'oes-menu-item-language-switch',
            'type_label' => 'OES Menu',
            'title' => 'Language Switch',
            'url' => '',
            'target' => '',
            'attr_title' => '',
            'description' => '',
            'classes' => [],
            'xfn' => '',
        ]
    ];

    /* add index pages */
    $objectID = 2;
    $oes = OES();
    if (!empty($oes->theme_index_pages))
        foreach ($oes->theme_index_pages as $indexKey => $indexPage)
            $menuItems[] = (object)[
                'ID' => ++$objectID,
                'db_id' => 0,
                'menu_item_parent' => 0,
                'object_id' => $objectID,
                'post_parent' => 0,
                'type' => 'custom',
                'object' => 'oes-menu-item-index.page',
                'type_label' => 'OES Menu',
                'title' => (empty($indexPage['label']['language0'] ?? '') ?
                    ('[Index] (' . $indexKey . ')') :
                    $indexPage['label']['language0']),
                'url' => home_url(($indexPage['slug'] ?? 'index') . '/'),
                'target' => '',
                'attr_title' => '',
                'description' => '',
                'classes' => [],
                'xfn' => '',
            ];


    $walker = new Walker_Nav_Menu_Checklist(); ?>
    <div id="oes-menu-items">
    <div class="tabs-panel tabs-panel-active">
        <ul class="categorychecklist form-no-clear">
            <?php echo walk_nav_menu_tree(
                array_map('wp_setup_nav_menu_item', $menuItems),
                0,
                (object)['walker' => $walker]); ?>
        </ul>
        <p class="button-controls">
            <span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check($nav_menu_selected_id); ?>
                       class="button-secondary submit-add-to-menu right"
                       value="<?php esc_attr_e('Add to Menu'); ?>" name="oes-menu-item-add"
                       id="oes-submit-menu-items"/>
				<span class="spinner"></span>
			</span>
        </p>
    </div>
    <?php
}


/**
 * Filters the css classes applied to a menu item's anchor element.
 *
 * @param array $classes The css classes.
 * @param WP_Post $item The current menu item.
 *
 * @return array The modified classes.
 */
function nav_menu_css_class(array $classes, WP_Post $item): array
{
    if (oes_get_field('field_link_disable', $item->ID)) $classes[] = 'oes-menu-disable-link';
    return $classes;
}


/**
 * Filters a menu item's title and check if the title is to be replaced by an icon or image.
 *
 * @param string $title The menu item's title.
 * @param WP_Post $item The current menu item.
 *
 * @return string The modified title
 */
function nav_menu_item_title(string $title, WP_Post $item): string
{
    if ($icon = oes_get_field('field_replace_label_icon', $item->ID))
        return sprintf('<span class="oes-menu-replace-with-icon"><img alt="" src="%s"></span>',
            get_stylesheet_directory_uri() . $icon);
    elseif ($image = oes_get_field('field_replace_label_image', $item->ID)) {

        $html = '';
        if (is_array($image) && isset($image['ID'])) {

            /* check if file exists */
            $path = get_post_meta($image['ID'], '_wp_attached_file', true);
            if (!empty($path) &&
                file_exists(WP_CONTENT_DIR . '/uploads/' . $path))
                $html = oes_get_html_img(esc_url($image['url']), esc_attr($image['alt']));
        }

        if (empty($html))
            $html = '<span class="oes-nav-replace-with-image">' .
                ($image['alt'] ?: ($image['title'] ?: '')) . '</span>';

        return $html;
    } elseif (property_exists($item, 'object') &&
        $item->object === 'oes-menu-item-language-switch' &&
        $title === 'Language Switch' &&
        $languageSwitch = oes_get_language_switch()) $title = $languageSwitch->get_menu_item_title();
    return $title;
}


/**
 * Filters the HTML attributes applied to a menu item's anchor element.
 *
 * @param array $args The HTML attributes applied to the menu item's `<a>` element.
 * @param WP_Post $item The current menu item.
 *
 * @return array The modified attributes.
 */
function nav_menu_link_attributes(array $args, WP_Post $item): array
{
    /* check if link opens new tab */
    if (oes_get_field('field_link_new_tab', $item->ID)) $args['target'] = '_blank';

    if (property_exists($item, 'object') && $item->object === 'oes-menu-item-search')
        $args['id'] = 'oes-search';
    elseif (property_exists($item, 'object') &&
        $item->object === 'oes-menu-item-language-switch' &&
        $languageSwitch = oes_get_language_switch()) $args['href'] = $languageSwitch->get_menu_item_link();
    return $args;
}