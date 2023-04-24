<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use OES\Admin\Page;

add_action('init', 'oes_register_manual');
add_filter('parent_file', 'oes_manual_modify_parent_file');


/**
 * Register the OES post type "Manual"
 * @return void
 */
function oes_register_manual(): void
{

    /* check if post type 'oes_manual_entry' is already registered */
    if (!post_type_exists('oes_manual_entry')) {

        register_taxonomy('t_oes_manual_components', ['oes_manual_entry'], [
            'labels' => [
                'name' => __('Components', 'oes'),
                'singular_name' => __('Component', 'oes'),
                'search_items' => __('Search Components', 'oes'),
                'all_items' => __('All Components', 'oes'),
                'parent_item' => __('Parent Component', 'oes'),
                'parent_item_colon' => __('Parent Component:', 'oes'),
                'edit_item' => __('Edit Component', 'oes'),
                'update_item' => __('Update Component', 'oes'),
                'add_new_item' => __('Add New Component', 'oes'),
                'new_item_name' => __('New Component Name', 'oes'),
                'menu_name' => __('Components', 'oes'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true
        ]);

        register_post_type(
            'oes_manual_entry',
            [
                'label' => 'Manual Entries',
                'description' => 'internal use only',
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'menu_icon' => plugins_url(OES()->basename . '/assets/images/oes_cubic_18x18_second.png'),
                'hierarchical' => true,
                'show_in_rest' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 58,
                'supports' => ['title', 'page-attributes', 'editor', 'excerpt'],
                'taxonomies' => ['t_oes_manual_components']
            ]
        );
    }


    /* create container page */
    new Page([
        'page_parameters' => [
            'page_title' => 'Manual',
            'menu_title' => 'Manual',
            'menu_slug' => 'admin_manual',
            'function' => 'oes_manual_main_page',
            'position' => 58
        ]]);

    /* create subpages */
    new Page([
        'sub_page' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'Manual',
            'menu_title' => 'Table of Contents',
            'menu_slug' => 'admin_manual',
            'function' => ''
        ]]);

    new Page([
        'sub_page' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'Components',
            'menu_title' => 'Components',
            'menu_slug' => 'edit-tags.php?taxonomy=t_oes_manual_components',
            'function' => '',
            'position' => 98
        ]]);

    new Page([
        'sub_page' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'Manual Entries',
            'menu_title' => 'Manual Entries',
            'menu_slug' => 'edit.php?post_type=oes_manual_entry',
            'function' => '',
            'position' => 99
        ]]);
}


/**
 * Modify admin menu to show active menu for "Manual" pages.
 *
 * @param string $parent_file The parent file.
 * @return string The filtered parent file.
 */
function oes_manual_modify_parent_file(string $parent_file): string
{
    /* get current screen */
    global $current_screen;
    if (in_array($current_screen->base, ['post', 'edit', 'edit-tags']) &&
        $current_screen->post_type === 'oes_manual_entry' ||
        $current_screen->taxonomy === 't_oes_manual_components')
        $parent_file = 'admin_manual';

    return $parent_file;
}