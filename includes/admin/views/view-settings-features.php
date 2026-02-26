<?php

require_once OES_CORE_PLUGIN . '/includes/admin/lists/class-features-list-table.php';

$listTable = new Features_List_Table([
    'singular' => __('feature', 'oes'),
    'plural' => __('features', 'oes'),
    'columns' => [
        'title' => __('Feature', 'oes'),
        'description' => __('Description', 'oes'),
        'actions' => __('Actions', 'oes')
    ],
    'meta_key' => 'field_oes_comment',
    'filter' => ['group', 'status']
]);

$listTable->prepare_items();

echo '<div class="wrap">';
echo '<h1 class="wp-heading-inline">' . __('OES Features', 'oes') . '</h1>';

$searchTerm = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
if (!empty($searchTerm)) {
    echo '<span class="subtitle">' . __('Showing results for: ', 'oes') .
        '<strong>' . esc_html($searchTerm) . '</strong></span>';
}

echo '<form method="get">';
echo '<input type="hidden" name="page" value="oes_settings_features" />';
$listTable->search_box(__('Search Features', 'oes'), 'search_id');
echo '</form>';

echo '<form method="get">';
echo '<input type="hidden" name="page" value="oes_settings_features" />';
$listTable->display();
echo '</form>';

echo '</div>';