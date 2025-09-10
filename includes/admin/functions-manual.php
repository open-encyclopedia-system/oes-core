<?php

namespace OES\Manual;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use OES\Admin\Page;
use OES\Admin\Subpage;


/**
 * Render manual entries for main page.
 * @return void
 */
function main_page(): void
{

    ?>
    <div class="wrap oes-manual-wrap">
    <h1 class="wp-heading-inline"><?php echo get_manual_string(); ?></h1>
    <a href="<?php echo admin_url('post-new.php?post_type=oes_manual_entry'); ?>" class="page-title-action"><?php
        _e('Add New Entry', 'oes'); ?></a>
    <div class="oes-manual-page"><?php
        if (isset($_GET['post_id'])) display_entry($_GET['post_id']);
        else display_overview(); ?>
    </div>
    </div><?php
}


/**
 * Get the manual string, containing project name and manual.
 *
 * @return string Return the manual string.
 */
function get_manual_string(): string
{
    return strtoupper(str_replace('-', ' ', OES_BASENAME_PROJECT)) . ' ' . __('Manual', 'oes');
}


/**
 * Display a manual entry.
 *
 * @param int $postID The post ID of the manual entry.
 * @return void
 */
function display_entry(int $postID): void
{
    if ($post = get_post($postID)) :
        ?>
        <div class="oes-manual-breadcrumbs"><?php
            echo implode(' / ', get_breadcrumbs($post)); ?></div>
        <h1 class="oes-manual-header"><?php echo $post->post_title; ?>
            <a href="<?php echo get_edit_post_link($post->ID); ?>">
                <span class="dashicons dashicons-edit"></span>
            </a>
        </h1>
        <div class="entry-content"><?php echo do_blocks(do_shortcode($post->post_content)); ?></div>
    <?php
    else : display_overview();
    endif;
}


/**
 * Display the Manual overview.
 *
 * @return void
 */
function display_overview(): void
{
    ?><p><?php
    echo __('Welcome to ' . get_manual_string() . ' — an evolving, collaborative extension of the ', 'oes') .
        '<a href="https://manual.open-encyclopedia-system.org/" target="_blank">' . __('OES Manual', 'oes') .
        '</a>' .
        __('. While the general OES Manual provides guides for the standardized technical foundations, this ' .
            'space is dedicated to capturing project-specific knowledge that emerges while building, ' .
            'editing, and maintaining the content of your OES project.', 'oes'); ?></p><?php

    /* get selects */
    $selectedSorted = $_REQUEST['sort'] ?? 'default';
    $selectedComponent = $_REQUEST['term'] ?? 'all';
    $searchTerm = $_REQUEST['s'] ?? '';

    $sortedString = ($selectedSorted == 'default' ? '' : '&sort=' . $selectedSorted);
    $componentString = ($selectedComponent == 'all' ? '' : '&term=' . $selectedComponent);
    $searchString = (empty($searchTerm) ? '' : '&s=' . $searchTerm);

    ?>
    <h2><?php _e('Table of Contents', 'oes'); ?></h2>
    <div class="oes-manual-search">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="search" name="s" placeholder="search manual..." value="<?php echo($searchTerm ?? ''); ?>"/>
            <input type="submit" class="button" value="Search">
            <input type="hidden" name="page" value="admin_manual">
            <input type="hidden" name="sort" value="<?php echo $selectedSorted; ?>">
            <input type="hidden" name="term" value="<?php echo $selectedComponent; ?>">
        </form>
    </div>
    <div class="oes-manual-components"><?php

        /* prepare components list */
        $components = get_terms([
            'taxonomy' => 't_oes_manual_components',
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        if (!empty($components)):
            $componentsList = [
                sprintf('<a href="%s" class="%s">%s</a>',
                    admin_url('admin.php?page=admin_manual') . $sortedString . $searchString,
                    ($selectedComponent == 'all' ? 'current' : ''),
                    __('all', 'oes'))
            ];
            foreach ($components as $singleComponent):
                if ($singleComponent instanceof \WP_Term):
                    $componentsList[] = sprintf('<a href="%s" class="%s">%s</a>',
                        admin_url('admin.php?page=admin_manual') . '&term=' . $singleComponent->term_id .
                        $sortedString . $searchString,
                        ($selectedComponent == $singleComponent->term_id ? 'current' : ''),
                        $singleComponent->name);
                endif;
            endforeach;
            if (!empty($componentsList))
                echo '<ul class="subsubsub"><li>Filter </li><li>' . implode(' |</li><li> ', $componentsList) .
                    '</li></ul>';
        endif;

        ?></div>
    <div class="oes-manual-sort"><?php

        /* prepare sorting */
        $sortList = [];
        foreach ([
                     'default' => 'hierarchical',
                     'modified' => 'by edit date',
                     'date' => 'by publish date',
                     'title' => 'by name'] as $sortKey => $sortLabel):
            $sortList[] = sprintf('<a href="%s" class="%s">%s</a>',
                admin_url('admin.php?page=admin_manual') . '&sort=' . $sortKey . $componentString . $searchString,
                ($selectedSorted == $sortKey ? 'current' : ''),
                $sortLabel);
        endforeach;
        echo '<ul class="subsubsub"><li>Sort </li><li>' . implode(' |</li><li> ', $sortList) . '</li></ul>';

        ?></div>
    <hr>
    <div class="oes-manual-entry-list"><?php

    /* display all manual entries */

    /* prepare query arguments */
    $argsForAll = [
        'post_type' => 'oes_manual_entry',
        'numberposts' => -1
    ];
    $args = $argsForAll;
    if ($selectedComponent !== 'all')
        $args['tax_query'] = [[
            'taxonomy' => 't_oes_manual_components',
            'field' => 'term_id',
            'terms' => $selectedComponent,
        ]];
    if (!empty($searchTerm)) $args['s'] = $searchTerm;


    /* either hierarchically or sorted */
    $empty = false;
    if ($selectedSorted == 'default'):

        /* loop through all entries */
        $argsForAll['post_parent'] = 0;
        $allEntries = get_posts($argsForAll);

        $validIDs = [];
        if (($selectedComponent != 'all' || !empty($searchTerm))) {
            $args['fields'] = 'ids';
            $validIDs = get_posts($args);
            $empty = empty($validIDs);
        }

        if (!$empty):
            $toc = '';
            foreach ($allEntries as $entry)
                $toc .= toc_recursive($entry, false, $validIDs);
            echo '<ul>' . $toc . '</ul>';
        endif;
    else:

        switch ($selectedSorted) {
            case 'date':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;

            case 'modified':
                $args['orderby'] = 'modified';
                $args['order'] = 'DESC';
                break;

            case 'title':
            default:
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
        }

        $toc = '';
        foreach (get_posts($args) as $entry):
            $toc .= '<li class="oes-manual-toc-item"><a href="' . admin_url('admin.php?page=admin_manual') .
                '&post_id=' . $entry->ID . '">' .
                $entry->post_title . '</a></li>';
        endforeach;

        if (empty($toc)) $empty = true;
        else echo '<ul>' . $toc . '</ul>';
    endif;

    /* empty results */
    if ($empty) echo '<div>' . __('No manual entries found.', 'oes') . '</div>';

    ?></div><?php
}


/**
 * Create the side panel containing table of content info.
 *
 * @return void
 */
function toc_panel(): void
{
    /* return early if not on manual page */
    if (get_current_screen()->id !== 'toplevel_page_admin_manual') return;

    ?>
    <div id="oes-admin-toggle-panel" class="oes-admin-toggle-panel">
        <div class="oes-admin-toggle-header">
            <strong><?php _e('Table of Contents', 'oes'); ?></strong>
            <button class="oes-admin-toggle-close">×</button>
        </div>
        <div class="oes-admin-toggle-content">
            <a href="<?php echo admin_url('admin.php?page=admin_manual'); ?>"><?php _e('Start', 'oes'); ?></a>
            <hr>
            <ul><?php

                $toc = '';
                $allEntries = get_posts([
                    'post_type' => 'oes_manual_entry',
                    'post_parent' => 0,
                    'numberposts' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ]);
                foreach ($allEntries as $entry) $toc .= toc_recursive($entry, $_REQUEST['post_id'] ?? false);

                echo $toc; ?></ul>
        </div>
    </div>
    <div id="oes-admin-toggle-toggle" class="oes-admin-toggle-toggle"><?php _e('Contents', 'oes'); ?></div>
    <?php
}


/**
 * Get all manual entries.
 *
 * @param mixed $entry The manual entry.
 * @param mixed $currentPostID The current post ID.
 * @param array $validIDs Valid IDs. others are skipped.
 * @return string Return list of all manual entries.
 */
function toc_recursive(mixed $entry, mixed $currentPostID, array $validIDs = []): string
{
    /* break early for attachments */
    if ($entry->post_type == 'attachment') return '';

    /* check if valid id */
    $validID = empty($validIDs) || in_array($entry->ID, $validIDs);

    /* check fo children */
    $additional = '';
    if ($children = get_children([
        'post_parent' => $entry->ID,
        'post_type' => 'oes_manual_entry',
        'post_status' => 'any',
        'orderby' => 'title',
        'order' => 'ASC'
    ])) {
        $recursive = '';
        foreach ($children as $child) $recursive .= toc_recursive($child, $currentPostID, $validIDs);
        $additional = '<ul style="padding-left: 10px">' . $recursive . '</ul>';
    }

    /* check if entry is current post, if so, add inner toc */
    if ($validID && $entry->ID == $currentPostID) {

        $headings = '';
        $blocks = parse_blocks($entry->post_content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'core/heading')
                $headings .= '<li><span style="padding-left: ' .
                    (((int)($block['attrs']['level'] ?? 2) ?? 2) * 10) . 'px">' .
                    wp_strip_all_tags($block['innerHTML'] ?? '') . '</span></li>';
        }
        return '<li class="oes-manual-toc-item">' .
            '<div class="oes-manual-toc-item-active">' .
            '<span class="active">' . $entry->post_title . '</span>' .
            (empty($headings) ? '' : ('<ul class="oes-manual-inner-toc">' . $headings . '</ul>')) .
            '</div>' .
            $additional . '</li>';
    } elseif ($validID)
        return '<li class="oes-manual-toc-item">' .
            '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
            $entry->post_title . '</a>' . $additional . '</li>';
    else return $additional;
}


/**
 * Get breadcrumb for manual entry.
 *
 * @param mixed $entry The manual entry.
 * @return string[] Return array of breadcrumbs
 */
function get_breadcrumbs($entry, $selfID = false): array
{
    if (!$selfID) $selfID = $entry->ID;
    if ($entry->ID == $selfID) $crumbs = [];
    else
        $crumbs = [
            '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
            $entry->post_title . '</a>'
        ];
    if ($entry->post_parent) $crumbs = array_merge(get_breadcrumbs(get_post($entry->post_parent), $selfID), $crumbs);
    return $crumbs;
}


/**
 * Register the OES post type "Manual"
 * @return void
 */
function register(): void
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
                'menu_icon' => plugins_url(OES_BASENAME . '/assets/images/oes_cubic_18x18_second.png'),
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
            'page_title' => 'OES Manual',
            'menu_title' => 'OES Manual',
            'menu_slug' => 'admin_manual',
            'function' => '\OES\Manual\main_page',
            'position' => 58
        ]]);

    /* create subpages */
    new Subpage([
        'subpage' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'OES Manual',
            'menu_title' => 'Table of Contents',
            'menu_slug' => 'admin_manual',
            'function' => '',
            'position' => 12
        ],
        'is_core_page' => true
    ]);

    new Subpage([
        'subpage' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'Manual Entries',
            'menu_title' => 'Manual Entries',
            'menu_slug' => 'edit.php?post_type=oes_manual_entry',
            'function' => '',
            'position' => 80
        ]]);

    new Subpage([
        'subpage' => true,
        'page_parameters' => [
            'parent_slug' => 'admin_manual',
            'page_title' => 'Components',
            'menu_title' => 'Components',
            'menu_slug' => 'edit-tags.php?taxonomy=t_oes_manual_components',
            'function' => '',
            'position' => 90
        ]]);
}


/**
 * Modify admin menu to show active menu for "Manual" pages.
 *
 * @param string $parent_file The parent file.
 * @return string The filtered parent file.
 */
function modify_parent_file(string $parent_file): string
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
 * Enqueue block css for manual entries.
 *
 * @param string $hook
 * @return void
 */
function include_style(string $hook): void
{
    if ($hook !== 'toplevel_page_admin_manual') return;

    wp_enqueue_style('wp-block-library');
    wp_enqueue_style('wp-edit-blocks');
}


/**
 * Include theme stylesheet and colors.
 * @return void
 */
function include_stylesheet(): void
{
    if (get_current_screen()->id !== 'toplevel_page_admin_manual') return;

    /* add color presets */
    $css = ':root {' . PHP_EOL;
    foreach (wp_get_global_settings(['color', 'palette']) as $part) {
        foreach ($part as $color) {
            $slug = sanitize_title($color['slug']);
            $hex = esc_html($color['color']);
            $css .= '--wp--preset--color--' . $slug . ': ' . $hex . ';' . PHP_EOL .
                ".has-{$slug}-background-color { background-color: {$hex}; }" . PHP_EOL .
                ".has-{$slug}-color { color: {$hex}; }" . PHP_EOL;
        }
    }
    $css .= '}';

    echo '<style id="oes-manual-admin-global-styles">' . $css . '</style>';
}


/**
 * Render manual entries for dashboard.
 * @return void
 */
function dashboard_html(): void
{
    ?>
    <ul><?php

    $toc = '';
    $allEntries = get_posts([
        'post_type' => 'oes_manual_entry',
        'post_parent' => 0,
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    foreach ($allEntries as $entry) $toc .= '<li class="oes-manual-toc-item">' .
        '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
        $entry->post_title . '</a></li>';

    echo $toc; ?></ul><?php


    echo '<a class="page-title-action" href="' . admin_url('admin.php?page=admin_manual') . '">' .
        __('Go to manual', 'oes') . '</a>';

}
