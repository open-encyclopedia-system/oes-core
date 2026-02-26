<?php

/**
 * @file
 * @reviewed 3.0.0
 */

use OES\Admin\Operations\Display_Builder;
use OES\Admin\Operations\Operation;
use function OES\Admin\Operations\delete_operation;
use function OES\Rights\user_can_manage_content;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Constructs a table list for OES operations.
 */
class Operations_List_Table extends OES_List_Table
{
    /** @inheritdoc */
    protected array $filter = [];

    /**
     * Prepare the column information
     * @param $item
     * @return string
     */
    protected function column_info($item): string
    {
        $id = $item['id'] ?? false;

        $actions = '';
        if (user_can_manage_content()) {
            $actions = sprintf('<div class="row-actions visible">
        <span class="import"><a href="%s">%s</a></span> | <span class="delete"><a href="%s">%s</a></span>
    </div>',
                esc_url(add_query_arg([
                    'action' => 'oes_operation_import',
                    'list_ids' => $id,
                    '_wpnonce' => wp_create_nonce('oes_operation_import')
                ], admin_url('admin.php'))),
                __('Import', 'oes'),
                esc_url(add_query_arg([
                    'action' => 'oes_operation_delete',
                    'list_ids' => $id,
                    '_wpnonce' => wp_create_nonce('oes_operation_delete')
                ], admin_url('admin.php'))),
                __('Delete', 'oes')
            );
        }

        return ($item['info'] ?? __('Missing Information')) . $actions;
    }

    /**
     * Process bulk actions
     *
     * @return void
     */
    public function process_bulk_action(): void
    {
        if (!user_can_manage_content()) {
            return;
        }

        $action = $this->current_action();
        $operationIDs = $_POST['list_ids'] ?? [];

        if (empty($operationIDs) || empty($action)) {
            return;
        }

        check_admin_referer('oes_operation_bulk_action');

        foreach ($operationIDs as $operationID) {
            delete_operation($operationID);
        }
    }

    /** @inheritdoc */
    protected function get_bulk_actions(): array
    {
        $actions = [];

        if (user_can_manage_content()) {
            $actions['delete'] = __('Delete', 'oes');
        }

        return $actions;
    }

    /** @inheritdoc */
    protected function get_data(): array
    {
        $data = [];

        global $wpdb;

        $table = $wpdb->prefix . 'oes_operations';
        $results = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY operation_sequence ASC, id ASC"
        );

        $displayBuilder = new Display_Builder();

        $titles = [];
        foreach ($results as $row) {

            $operation = Operation::from_row($row);
            $opSummary = $displayBuilder->build_summary($operation);

            $rowID = $row->id;
            $dataID = $row->operation_object_id ?? $rowID;

            if(isset($titles[$row->operation_temp])){
                $info = __('Additional data for ', 'oes') . $titles[$row->operation_temp];
            }
            else{
                $info = $displayBuilder->build_title($operation);
                $titles[$row->operation_temp] = $info ?: __('Missing information', 'oes');
            }

            $data[$rowID] = [
                'id' => $row->id,
                'object_id' => $dataID,
                'object' => $row->operation_object,
                'type' => $row->operation_type,
                'component' => $row->operation_key,
                'timestamp' => $row->operation_date,
                'status' => $row->operation_status,
                'info' => $info,
                'summary' => $opSummary,
                'message' => $row->operation_comment
            ];
        }

        ksort($data);
        return $data;
    }
}
