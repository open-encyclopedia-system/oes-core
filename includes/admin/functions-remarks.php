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
    global $oesRemarksListTable;
    require_once OES_CORE_PLUGIN . '/includes/admin/lists/class-remarks-list-table.php';
    $oesRemarksListTable = new \Remarks_List_Table([
        'singular' => 'OES remark',
        'plural' => 'OES remarks',
        'columns' => [
            'field_oes_comment' => 'OES Remark',
            'oes_status' => 'OES Status',
            'title' => 'Title'
        ],
        'meta_key' => 'field_oes_comment'
    ]);
    $oesRemarksListTable->prepare_items();

    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . __('OES Remarks', 'oes') . '</h1>';

    $searchTerm = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
    if (!empty($searchTerm)) {
        echo '<span class="subtitle">' . __('Showing results for: ', 'oes') .
            '<strong>' . esc_html($searchTerm) . '</strong></span>';
    }

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
        'Remarks',
        'oes_read',
        'admin_oes_remarks',
        '\OES\Remarks\main_page',
        oes_get_menu_icon_path('oes'),
        54
    );

    add_action("load-$hook", '\OES\Remarks\screen_option');
}

/**
 * Display screen options for OES remarks
 * @return void
 */
function screen_option(): void
{
    add_screen_option('per_page', [
        'label' => 'Number of items per page:',
        'default' => 10,
        'option' => 'oes_items_per_page']);

    require_once OES_CORE_PLUGIN . '/includes/admin/lists/class-remarks-list-table.php';
    global $oesRemarksListTable;
    $oesRemarksListTable = new \Remarks_List_Table([
        'singular' => 'OES remark',
        'plural' => 'OES remarks',
        'columns' => [
            'field_oes_comment' => 'OES Remark',
            'oes_status' => 'OES Status',
            'title' => 'Title'
        ],
        'meta_key' => 'field_oes_comment'
    ]);
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
    if ($option === 'oes_items_per_page') {
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