<?php

namespace OES\Admin\Operations;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Insert an operation into the OES operation table.
 *
 * @param string $operation The operation action.
 * @param string $object The operation object.
 * @param string $object_id The operation object ID.
 * @param string $value The operation value.
 * @param string $key The operation key.
 * @param string $type The operation object type.
 * @param array $args Additional arguments. Valid parameters are:
 *  'status'    :   The operation status.
 *  'comment'   :   The operation comment.
 *  'author'    :   The operation author.
 *  'date'      :   The operation date.
 *  'temp'      :   Temporary argument.
 *
 *
 * @return int|bool The number of rows inserted, or false on error.
 */
function insert_operation(
    string $operation,
    string $object,
    string $object_id,
    string $value,
    string $key = '',
    string $type = '',
    array  $args = [])
{
    $data = [
        'operation' => $operation,
        'operation_object' => $object,
        'operation_object_id' => (int)$object_id,
        'operation_value' => $value,
        'operation_key' => $key ?? '',
        'operation_type' => $type ?? '',
        'operation_status' => $args['status'] ?? 'undefined',
        'operation_comment' => $args['comment'] ?? '',
        'operation_author' => $args['author'] ?? get_the_author_meta('id'),
        'operation_date' => $args['date'] ?? current_time('mysql'),
        'operation_temp' => $args['temp'] ?? '',
        'operation_sequence' => $args['sequence'] ?? 0
    ];

    global $wpdb;

    if ($wpdb->insert($wpdb->prefix . 'oes_operations', $data)) {
        return $wpdb->insert_id;
    }

    return false;
}

/**
 * Update an operation in the OES operation table.
 *
 * @param mixed $id The operation id.
 * @param array $values The values to be updated (in column => value pairs).
 *
 * @return int|false The number of rows updated, or false on error or empty values.
 */
function update_operation($id, array $values)
{
    if (empty($values)) {
        return false;
    }

    global $wpdb;

    return $wpdb->update(
        $wpdb->prefix . 'oes_operations',
        $values,
        ['id' => $id]
    );
}

/**
 * Delete a single operation.
 *
 * @param int $id
 * @return int|false Rows deleted or false on failure.
 */
function delete_operation(int $id)
{
    global $wpdb;

    return $wpdb->delete(
        $wpdb->prefix . 'oes_operations',
        ['id' => $id]
    );
}

/**
 * Get the maximum temp id from table.
 * @return int
 */
function get_max_temp(): int
{
    global $wpdb;

    return (int)$wpdb->get_var(
        "SELECT MAX(CAST(SUBSTRING_INDEX(operation_temp, '_', -1) AS UNSIGNED)) 
     FROM {$wpdb->prefix}oes_operations 
     WHERE operation_temp LIKE 'temp_%'"
    );
}

/**
 * Truncate operations table.
 *
 * @return int|false Number of affected rows or false on failure.
 */
function delete_all_operations()
{
    global $wpdb;

    return $wpdb->query(
        'TRUNCATE TABLE ' . $wpdb->prefix . 'oes_operations'
    );
}

function delete_operations(): void
{
    $updated = 0;
    $nonce   = $_GET['_wpnonce'] ?? '';

    if (!wp_verify_nonce($nonce, 'oes_operation_delete')) {
        wp_die(__('Invalid nonce.'));
    }

    if (\OES\Rights\user_can_manage_content()) {

        $operationIDs = (array)$_GET['list_ids'];

        foreach ($operationIDs as $operationID) {
            delete_operation($operationID);
            $updated = 1;
        }
    }

    $redirectURL = admin_url('admin.php?page=oes_tools_import&operations_deleted=' . $updated);
    wp_redirect($redirectURL);
    exit;
}

function import_operations(): void
{
    $updated = 0;
    $nonce   = $_GET['_wpnonce'] ?? '';

    if (!wp_verify_nonce($nonce, 'oes_operation_import')) {
        wp_die(__('Invalid nonce.'));
    }

    if (\OES\Rights\user_can_manage_content()) {
        $operationIDs = (array)$_GET['list_ids'];
        $updated = import_operations_from_array($operationIDs);
    }

    $redirectURL = admin_url('admin.php?page=oes_tools_import&operations_imported=' . $updated);
    wp_redirect($redirectURL);
    exit;
}

function import_operations_from_array(array $operationIDs): int
{
    $updated = 0;

    $repository = new Repository();
    $executor = new Executor($repository);

    foreach ($operationIDs as $operationID) {
        $operation = $repository->find($operationID);
        $executor->execute($operation);
        $updated = 1;
    }
    return $updated;
}

function import_all_operations(): int
{
    $updated = 0;

    if (!\OES\Rights\user_can_manage_content()) {
        return $updated;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'oes_operations';

    $operations = $wpdb->get_results(
        "SELECT id FROM $table WHERE operation_status = 'ready' ORDER BY operation_sequence ASC",
        ARRAY_A
    );

    if (empty($operations)) {
        return $updated;
    }

    $repository = new Repository();
    $executor = new Executor($repository);

    foreach ($operations as $op) {
        $operation = $repository->find($op['id']);
        if ($executor->execute($operation)) {
            $updated++;
        }
    }

    return $updated;
}