<?php


use OES\Admin\Page;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* Register the OES post type "Manual". */
add_action('init', 'oes_register_manual');


/**
 * Register the OES post type "Manual"
 */
function oes_register_manual()
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
 * Render manual entries for main page.
 */
function oes_manual_main_page()
{
    $toc = '';
    $allEntries = get_posts([
        'post_type' => 'oes_manual_entry',
        'post_parent' => 0,
        'numberposts' => -1
    ]);
    foreach ($allEntries as $entry) $toc .= oes_manual_toc_recursive($entry);

    ?>
    <div class="wrap">
    <h1>Manual</h1>
    <div>
        <div class="oes-manual-wrap" style="width:75%;float:left;background-color:white;">
            <div style="padding:10px;margin-top:10px;"><?php

            if (isset($_GET['post_id'])) :
                if ($post = get_post($_GET['post_id'])) :
                    ?><div class="oes-manual-breadcrumbs"><?php
                    echo implode(' / ', oes_manual_get_breadcrumbs($post));?></div>
                    <h1 class="oes-manual-header"><?php echo $post->post_title;?><a href="<?php
                echo get_edit_post_link($entry->ID);?>"><span class="dashicons dashicons-edit"></span></a></h1>
                    <div style="padding:10px;"><?php echo $post->post_content; ?></div>
                <?php
                else :
                    printf('Post with post ID %s not found', $_GET['post_id']);
                endif;
            endif;
                ?></div></div>
        <div class="oes-manual-toc" style="width:25%;display:inline-block;float:right">
            <div style="padding:10px;border:1px solid grey">
                <h3>Table Of Contents</h3><ul class="oes-manual-toc-list"><?php echo $toc; ?></ul></div><?php
            ?>
        </div>
    </div></div><?php
}


/**
 * Get all manual entries.
 *
 * @param WP_Post $entry The manual entry.
 * @return string Return list of all manual entries.
 */
function oes_manual_toc_recursive(WP_Post $entry): string
{
    /* check fo children */
    if ($children = get_children($entry->ID)) {
        $recursive = '';
        foreach ($children as $child) $recursive .= oes_manual_toc_recursive($child);
        return '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
            $entry->post_title . '</a><ul>' . $recursive . '</ul></li>';
    } else return '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
        $entry->post_title . '</a></li>';
}


/**
 * Get breadcrumb for manual entry.
 *
 * @param WP_Post $entry The manual entry.
 * @return string[] Return array of breadcrumbs
 */
function oes_manual_get_breadcrumbs(WP_Post $entry): array
{

    $crumbs = [
        '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
        $entry->post_title . '</a>'
    ];
    if($entry->post_parent) $crumbs = array_merge(oes_manual_get_breadcrumbs(get_post($entry->post_parent)), $crumbs);
    return $crumbs;
}


/* modify admin menu if necessary (to show active menu)*/
add_filter('parent_file', 'oes_manual_modify_parent_file');

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


/**
 * Render manual entries for dashboard.
 */
function oes_dashboard_manual_html()
{
    ?>
    <a href="#"><?php _e('Click here for the OES handbook [link to handbook... TODO]', 'oes'); ?></a>
    <p>Here the FAQs Articles:</p><?php

    $faqEntries = '';
    $manualEntry = get_posts([
        'post_type' => 'oes_manual_entry',
        'numberposts' => -1
    ]);
    if (!empty($manualEntry))
        foreach ($manualEntry as $entry)
        if(has_term('faq', 't_oes_manual_components', $entry->ID))
            $faqEntries .= '<li><a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
                $entry->post_title . '</a></li>';

    echo '<ul>' . $faqEntries . '</ul>' .
        '<a class="page-title-action" href="' . admin_url('admin.php?page=admin_manual') . '">Manual</a>';

}