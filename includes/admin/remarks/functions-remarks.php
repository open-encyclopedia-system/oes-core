<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Remarks;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Render OES remarks for main page.
 * @return void
 */
function main_page(): void
{
    // create OES remarks list table
    global $oesRemarksListTable;
    require_once plugin_dir_path(__FILE__) . 'class-oes-remarks-list-table.php';
    $oesRemarksListTable = new \OES_Remarks_List_Table();
    $oesRemarksListTable->prepare_items();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . __('OES Remarks', 'oes') . '</h1>';

    // display search term if present
    $searchTerm = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
    if (!empty($searchTerm)) {
        echo '<span class="subtitle">' . __('Showing results for: ', 'oes') .
            '<strong>' . esc_html($searchTerm) . '</strong></span>';
    }

    // search form
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="admin_oes_remarks" />';
    $oesRemarksListTable->search_box(__('Search Items', 'oes'), 'search_id');
    echo '</form>';

    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="admin_oes_remarks" />';
    $oesRemarksListTable->display();
    echo '</form>';

    echo '</div>';
}

/**
 * Create a page for OES remarks
 * @return void
 */
function create_page(): void
{
    $hook = \add_menu_page(
        'OES Remarks',
        'OES Remarks',
        'edit_posts',
        'admin_oes_remarks',
        '\OES\Remarks\main_page',
        plugins_url(OES_BASENAME . '/assets/images/oes_cubic_18x18_second.png'),
        58
    );

    add_action("load-$hook", '\OES\Remarks\screen_option');
}

/**
 * Display screen options for OES remarks
 * @return void
 */
function screen_option(): void
{
    // register screen option for number of items per page
    add_screen_option('per_page', [
        'label' => 'Number of items per page:',
        'default' => 10,
        'option' => 'oes_remarks_per_page']);

    // instantiated remarks list table
    require_once plugin_dir_path(__FILE__) . 'class-oes-remarks-list-table.php';
    global $oesRemarksListTable;
    $oesRemarksListTable = new \OES_Remarks_List_Table();
}

/**
 * Save screen options value when updated
 *
 * @param $status
 * @param $option
 * @param $value
 * @return int|mixed
 */
function set_screen_option($status, $option, $value)
{
    if ($option === 'oes_remarks_per_page') {
        return (int)$value;
    }
    return $status;
}

/**
 * Render manual entries for dashboard.
 * @return void
 */
function dashboard_html(): void
{
    ?>
    <p><?php _e('Here are the most recent remarks:', 'oes'); ?></p><?php

    $remarks = oes_get_wp_query_posts([
        'meta_key' => 'field_oes_comment',
        'meta_value' => '',
        'meta_compare' => '!=',
        'post_type' => 'any',
        'posts_per_page' => 10
    ]);

    $remarksTable = '';
    foreach ($remarks as $entry) {
        $remarksTable .= '<li>' .
            '<a href="' . admin_url('admin.php?page=admin_manual') . '&post_id=' . $entry->ID . '">' .
            $entry->post_title . '</a>' .
            ' ' . oes_get_field('field_oes_comment', $entry->ID) . '</li>';
    }

    echo '<ul>' . $remarksTable . '</ul>' .
        '<a class="page-title-action" href="' . admin_url('admin.php?page=admin_manual') . '">' .
        __('View all remarks', 'oes') . '</a>';
}