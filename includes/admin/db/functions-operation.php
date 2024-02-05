<?php

namespace OES\Admin\DB;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


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
 * @return int|false The number of rows inserted, or false on error.
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
    /* prepare arguments */
    $argsForInsert = [
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
        'operation_sequence' => $args['sequence'] ?? ''
    ];

    global $wpdb;
    return $wpdb->insert($wpdb->prefix . 'oes_operations', $argsForInsert);
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
    if (!empty($values)) {
        global $wpdb;
        return $wpdb->update($wpdb->prefix . 'oes_operations', $values, ['id' => (int)$id]);
    } else return false;
}


/**
 * Get all operation from the OES operation table.
 *
 * @param array $args Additional args.
 *
 * @return array|object|null Database query results.
 */
function get_all_operation(array $args = [])
{
    global $wpdb;
    $table = $wpdb->prefix . 'oes_operations';

    $additional = 'ORDER BY `operation_sequence` DESC';
    $where = "`id` IS NOT NULL";
    if (isset($args['top'])) $additional .= " LIMIT " . $args['top'];
    if (isset($args['status'])) $where .= " AND `operation_status` = '" . $args['status'] . "'";
    if (isset($args['status_not_in'])) $where .= " AND `operation_status` NOT IN (" . $args['status_not_in'] . ")";
    if (isset($args['type'])) $where .= " AND `operation_type` = '" . $args['type'] . "'";

    return $wpdb->get_results("SELECT * from $table WHERE ($where) $additional");
}


/**
 * Delete an operation from the OES operation table.
 *
 * @param mixed $id The operation id.
 *
 * @return int|false The number of rows updated, or false on error.
 */
function delete_operation($id)
{
    global $wpdb;
    return $wpdb->delete($wpdb->prefix . 'oes_operations', ['id' => (int)$id]);
}


/**
 * Delete all operation from the OES operation table (truncate table).
 *
 * @return int|bool Return false on error.
 */
function delete_all_operation()
{
    global $wpdb;
    return $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'oes_operations');
}


/**
 * Get operation by id.
 *
 * @param mixed $id The operation id.
 * @return array|false|object|\stdClass Return the database row.
 */
function get_operation($id)
{
    global $wpdb;
    $id = (int)$id;
    if (!$id) return false;

    $oesTable = $wpdb->prefix . 'oes_operations';
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $oesTable WHERE id = %d LIMIT 1", $id));

    if (!$row) return false;
    return $row;
}


/**
 * Display all operations as table (including checkbox for further processing on table).
 * @param array $args
 * @return void
 */
function display_all_operations(array $args = []): void
{

    /* get operations from database */
    $operations = get_all_operation($args);

    /* prepare table with existing results */
    $tableData = [];
    if (count($operations) > 0) {

        //@oesDevelopment Set maximum for columns, min(500, count($operations)).
        for ($i = 0; $i < count($operations); $i++) {

            $operation = $operations[$i];

            /* get data from operation object */
            $op = new \OES\Admin\DB\Operation($operation);
            $data = $op->get_data_for_display();
            $objectID = $op->object_id;

            /* prepare permalink */
            $permalink = false;
            if ($objectID)
                $permalink = $op->is_term ?
                    sprintf('<a href="%s" target="_blank">%s</a>',
                        get_edit_term_link($op->object_id, $op->object->taxonomy),
                        $op->object->name
                    ) :
                    sprintf('<a href="%s" target="_blank">%s</a>',
                        get_edit_post_link($op->object_id),
                        $op->object->post_title
                    );

            /* prepare table identifier */
            if (!$objectID) $identifier = empty($op->operation_temp) ? 'unknown' : $op->operation_temp;
            else $identifier = $objectID;

            /* prepare summary data */
            $tableDataKey = in_array($op->operation_status, [
                'ignored_partly',
                'ignored',
                'success',
                'error']) ? $op->operation_status : 'executable';
            if (!isset($tableData[$tableDataKey][$op->operation_object][$op->operation_type][$op->operation][$identifier]['id']))
                $tableData[$tableDataKey][$op->operation_object][$op->operation_type][$op->operation][$identifier] = [
                    'id' => $objectID,
                    'permalink' => $permalink
                ];

            /* prepare data */
            switch ($op->operation_status) {

                case 'ignored':
                    $tableData['ignored'][$op->operation_object][$op->operation_type][$op->operation][$identifier]['rows'][$op->id]['values'][] = [
                        'info' => $op->operation_comment,
                        'key' => $op->operation_key,
                        'key_label' => $op->operation_key_label,
                        'value' => $op->operation_value
                    ];
                    break;

                case 'success':
                    $tableData['success'][$op->operation_object][$op->operation_type][$op->operation][$identifier]['rows'][$op->id]['values'][] = [
                        'info' => __('Successfully executed.', 'oes'),
                        'key' => $op->operation_key,
                        'key_label' => $op->operation_key_label,
                        'value' => $op->operation_value
                    ];
                    break;

                case 'error':
                    $tableData['error'][$op->operation_object][$op->operation_type][$op->operation][$identifier]['rows'][$op->id]['values'][] = [
                        'info' => $op->operation_comment,
                        'key' => $op->operation_key,
                        'key_label' => $op->operation_key_label,
                        'value' => $op->operation_value
                    ];
                    break;

                case 'ignored_partly':
                    $tableData['ignored'][$op->operation_object][$op->operation_type][$op->operation][$identifier]['rows'][$op->id]['values'][] = [
                        'info' => $op->operation_comment,
                        'key' => $op->operation_key,
                        'key_label' => $op->operation_key_label,
                        'value' => $op->operation_value
                    ];

                    /* add additionally to executable */
                    if (!isset($tableData['executable'][$op->operation_object][$op->operation_type][$op->operation][$identifier]))
                        $tableData['executable'][$op->operation_object][$op->operation_type][$op->operation][$identifier] = [
                            'id' => $objectID,
                            'permalink' => $permalink
                        ];

                default:
                    $tableData['executable'][$op->operation_object][$op->operation_type][$op->operation][$identifier]['rows'][$op->id] = $data;
                    break;

            }
        }


        /* prepare table HTML representation */
        $tableHTMLArray = [
            'executable' => ['header' => __('Executable Operation', 'oes')],
            'success' => ['header' => __('Successful Executed Operation', 'oes')],
            'error' => ['header' => __('Error Occurred During Execution', 'oes')],
            'ignored' => ['header' => __('Ignored Operation', 'oes')]
        ];
        foreach ($tableHTMLArray as $section => $ignore) {
            if (isset($tableData[$section]) && !empty($tableData[$section])) {

                $rowsHTML = '';

                /* Post Data */
                if (isset($tableData[$section]['post']))
                    foreach ($tableData[$section]['post'] as $postType => $data)
                        $rowsHTML .= get_html_operation_row($postType, $data, false, $section, $args);

                /* Term Data */
                if (isset($tableData[$section]['term']))
                    foreach ($tableData[$section]['term'] as $taxonomy => $data)
                        $rowsHTML .= get_html_operation_row($taxonomy, $data, true, $section, $args);


                if (isset($tableData[$section]['unknown']))
                    foreach ($tableData[$section]['unknown'] as $operation => $data)
                        $rowsHTML .= get_html_operation_row($operation, $data, false, $section, $args);

                //@oesDevelopment Add post_term.

                if (!empty($rowsHTML)) $tableHTMLArray[$section]['html'] = $rowsHTML;
            }
        }
    }

    if (!empty($tableHTMLArray)) :
        ?>
        <div class="oes-operations">
            <div class="oes-operations-container"><?php

                foreach ($tableHTMLArray as $section => $sectionData)
                    if (isset($sectionData['html']) && !empty($sectionData['html'])) {

                        printf('<div class="oes-operations-%s oes-operations-table">' .
                            '<table class="oes-config-table oes-replace-select2-inside wp-list-table widefat fixed table-view-list">' .
                            '<tbody>%s</tbody>' .
                            '</table>' .
                            '</div>',
                            $section,
                            $sectionData['html']
                        );
                    }
                ?>
            </div>
        </div>
    <?php
    endif;
}


/**
 * Get html operation row.
 *
 * @param string $objectType The object type (post, term, ignore, invalid)
 * @param array $data The object data.
 * @param bool $isTaxonomy Indicate if objectType is taxonomy.
 * @param string $section Indicate the table section.
 * @param array $args Additional arguments.
 *
 * @return string Return the operation row as HTML string.
 */
function get_html_operation_row(
    string $objectType,
    array  $data,
    bool   $isTaxonomy = false,
    string $section = 'executable',
    array  $args = []): string
{
    $executable = ($section === 'executable');

    /* prepare header row */
    $rowHTML = '';

    /* prepare table header row */
    $innerTableHeaderRow = $executable ?
        sprintf('<thead><tr><th>%s</th><th>%s</th><th>%s</th><th>%s</th><th class="oes-expandable-row-20"></th></tr></thead>',
            __('Key', 'oes'),
            __('New Value', 'oes'),
            __('Old Value', 'oes'),
            __('Additional Info', 'oes')
        ) :
        sprintf('<thead><tr><th>%s</th><th>%s</th><th>%s</th></tr></thead>',
            __('Key', 'oes'),
            __('Value', 'oes'),
            __('Additional Info', 'oes')
        );


    $countRowsMax = $_GET['rows_per_page'] ?? 20;
    ksort($data);
    foreach ($data as $objects) {

        /* slice objects */
        $paging = isset($_GET['paging']) ? (int)$_GET['paging'] : 1;
        $objectsSliced = array_slice($objects, ($paging > 1 ? ($paging - 1) : 0) * $countRowsMax, $countRowsMax);

        /* prepare paging */
        $navigation = '';
        if (sizeof($objects) > $countRowsMax) {

            $url = admin_url('admin.php?page=tools_operations&select=' . ($_GET['select'] ?? $section) .
                '&type=' . ($_GET['type'] ?? ($args['type'] ?? 'all')));
            $maxPages = ceil(sizeof($objects) / $countRowsMax);

            $navigation = ($paging > 1) ?
                sprintf('<a class="first-page button" href="%s">' .
                    '<span class="screen-reader-text">%s</span><span aria-hidden="true">«</span>' .
                    '</a>' .
                    '<a class="previous-page button" href="%s">' .
                    '<span class="screen-reader-text">%s</span><span aria-hidden="true">‹</span>' .
                    '</a>',
                    __('First page', 'oes'),
                    $url,
                    $url . ($paging > 2 ? ('&paging=' . ($paging - 1)) : ''),
                    __('Previous page', 'oes')
                ) :
                ('<span class="pagination-links">' .
                    '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>' .
                    '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>' .
                    '</span>');

            $navigation .= '<span class="oes-paging-info">' . $paging .
                '<span class="tablenav-paging-text">' . __(' of ', 'oes') .
                '<span class="total-pages">' . $maxPages . '</span></span>' .
                '</span>';

            $navigation .= ($paging < $maxPages) ?
                sprintf('<a class="next-page button" href="%s">' .
                    '<span class="screen-reader-text">%s</span><span aria-hidden="true">›</span>' .
                    '</a>' .
                    '<a class="last-page button" href="%s">' .
                    '<span class="screen-reader-text">%s</span><span aria-hidden="true">»</span>' .
                    '</a>',
                    __('Next page', 'oes'),
                    $url . '&paging=' . ($paging + 1),
                    $url . '&paging=' . $maxPages,
                    __('Last page', 'oes')
                ) :
                ('<span class="pagination-links">' .
                    '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>' .
                    '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>' .
                    '</span>');
        }
        $rowHTML .= '<div class="tablenav"><div class="oes-pt-1-0 tablenav-pages">' . $navigation . '</div></div>';


        foreach ($objectsSliced as $objectData)
            if (isset($objectData['id'])) {

                $objectID = $objectData['id'];

                $rows = '';
                $countRows = 0;
                $status = [];
                $title = '';
                foreach ($objectData['rows'] ?? [] as $operationID => $operationsContainer)
                    if (isset($operationsContainer['values'])) {

                        /* prepare additional information for each operation */
                        $additional = '';
                        if ($executable)
                            $additional =
                                sprintf('<td rowspan="%s" class="oes-operation-additional-info">' .
                                    '<div><span>%s</span>%s</div>' .
                                    '<div><span>%s</span>%s</div>' .
                                    '<div><span>%s</span>%s</div>' .
                                    '<div><span>%s</span>%s</div></td>',
                                    sizeof($operationsContainer['values']),
                                    __('Author', 'oes'),
                                    $operationsContainer['author'] ?: '-',
                                    __('Creation Date', 'oes'),
                                    $operationsContainer['date'] ?: '-',
                                    __('Comment', 'oes'),
                                    $operationsContainer['comment'] ?: '-',
                                    __('Remark', 'oes'),
                                    !empty($operationsContainer['info']) ?
                                        ('<br>' . implode('<br>', $operationsContainer['info'])) :
                                        '-'
                                ) .
                                sprintf('<td rowspan="%s" class="oes-operation-include oes-expandable-row-20">%s</td>',
                                    sizeof($operationsContainer['values']),
                                    oes_html_get_form_element('checkbox',
                                        'operations[' . $operationID . ']',
                                        'operations_' . $operationID,
                                        true));

                        /* prepare status */
                        if (isset($operationsContainer['status']) && !in_array($operationsContainer['status'], $status))
                            $status[] = $operationsContainer['status'];

                        /* loop through operation data */
                        foreach ($operationsContainer['values'] as $key => $singleValue) {

                            /* prepare title for new instances */
                            if (!$objectID)
                                if ($singleValue['key'] === 'post_title') $title = $singleValue['value_new'];
                                elseif ($singleValue['key'] === 'term') $title = $singleValue['value_new'];

                            /* prepare inner table */
                            $first = ($key === array_key_first($operationsContainer['values'])) && $executable;

                            $rows .= $executable ?
                                sprintf('<tr%s><td>%s</td><td><strong>%s</strong></td><td>%s</td>%s</tr>',
                                    $first ? ' class="oes-operation-container-row"' : '',
                                    $singleValue['key_label'],
                                    $singleValue['value_new'] ?:
                                        '<span class="oes-operation-empty-value">' . __('[empty]', 'oes') . '</span>',
                                    ($singleValue['value_old'] ?:
                                        '<span class="oes-operation-empty-value">' . __('[empty]', 'oes') . '</span>'),
                                    $first ? $additional : ''
                                ) :
                                sprintf('<tr%s><td>%s</td><td><strong>%s</strong></td><td>%s</td></tr>',
                                    $first ? ' class="oes-operation-container-row"' : '',
                                    $singleValue['key_label'],
                                    $singleValue['value'] ?:
                                        '<span class="oes-operation-empty-value">' . __('[empty]', 'oes') . '</span>',
                                    $singleValue['info']
                                );
                        }
                        ++$countRows;
                    }

                /* prepare header row */
                $summary = $objectID ?
                    ($countRows . ' ' .
                        _n('operation for ',
                            'operations for ',
                            (sizeof($objectData['rows'] ?? [])),
                            'oes') .
                        ' ' .
                        sprintf('<a href="%s" target="_blank">%s</a>',
                            ($isTaxonomy ? get_edit_term_link($objectID, $objectType) : get_edit_post_link($objectID)),
                            ($isTaxonomy ? get_term($objectID, $objectType)->name : oes_get_display_title($objectID))
                        )) :
                    ($countRows . ' ' .
                        _n('operation for new ',
                            'operations for new ',
                            (sizeof($objectData['rows'] ?? [])),
                            'oes') .
                        ' ' .
                        ($isTaxonomy ? 'term' : 'post') .
                        ' "' . '<strong>' . (empty($title) ? 'Title missing' : $title) . '</strong>' . '".'
                    );

                /* prepare table */
                if (!empty($rows)) {
                    $rowHTML .= '<tr class="oes-expandable-header oes-capabilities-header-row">' .
                        '<td class="oes-expandable-row-20">' .
                        '<a href="javascript:void(0)" class="oes-plus oes-dashicons" onclick="oesConfigTableToggleRow(this)"></a>' .
                        '</td>' .
                        '<td colspan="3">' . $summary . '</td>' .
                        '<td>' . implode(', ', $status) . '</td>' .
                        ($executable ?
                            '<td class="oes-expandable-row-20">' .
                            oes_html_get_form_element('checkbox',
                                'operations[' . $objectID . ']',
                                'operations_' . $objectID,
                                true) . '</td>' :
                            '<td class="oes-expandable-row-20"></td>') .
                        '</tr>' .
                        '<tr class="oes-expandable-row" style="display:none">' .
                        '<td></td>' .
                        '<td colspan="5">' .
                        '<table class="oes-operation-display oes-option-table striped wp-list-table widefat table-view-list">' .
                        $innerTableHeaderRow .
                        '<tbody>' . $rows .
                        '</tr></tbody></table>' .
                        '</td>' .
                        '</tr>';
                }
            }
    }

    if (empty($rowHTML)) return '';

    /* prepare table area */
    return (sprintf('<tr class="oes-operation-type-header">' .
            '<th class="oes-expandable-row-20"></th>' .
            '<th colspan="3"><strong>%s</strong><code class="oes-object-identifier">' . $objectType . '</code></th>' .
            '<th>%s</th>' .
            '<th>%s</th>' .
            '</tr>',
            __('Operation', 'oes'),
            __('Status', 'oes'),
            __('Include', 'oes')
        ) . $rowHTML);
}
