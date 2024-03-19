<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use OES\Admin\DB\Operation;
use function OES\Admin\add_oes_notice_after_refresh;
use function OES\Admin\DB\delete_all_operation;
use function OES\Admin\DB\delete_operation;
use function OES\Admin\DB\display_all_operations;
use function OES\Admin\DB\get_all_operation;
use function OES\Admin\DB\get_operation;
use function OES\Admin\DB\update_operation;

if (!class_exists('Operations')) :

    /**
     * Class Operations
     *
     * Clean or execute operations from db.
     */
    class Operations extends Tool
    {


        public array $count_array = [];

        //Overwrite
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }


        //Overwrite parent
        function html(): void
        {
            echo '<div class="narrow">';

            /* get step */
            $this->greet();

            /* display operation table */
            global $wpdb;
            $table = $wpdb->prefix . 'oes_operations';
            $count = $wpdb->get_results(
                    "SELECT `operation_status`, `operation_type`, count(`operation_status`) AS count FROM $table GROUP BY `operation_status`, `operation_type`"
            ); //@oesDevelopment WHERE `operation_status` <> 'ignored'

            if (!empty($count)) {

                /* prepare count */
                foreach ($count as $countPerStatus) {

                    $type = $countPerStatus->operation_type ?: 'unknown';
                    $status = $countPerStatus->operation_status ?: 'executable';

                    if ($status == 'ignored_partly') {
                        $this->count_array[$type]['count_per_operation']['ignored'] =
                            ($this->count_array[$type]['count_per_operation']['ignored'] ?? 0) + $countPerStatus->count;
                        $this->count_array[$type]['count'] = $this->count_array[$type]['count'] + $countPerStatus->count;
                    }

                    $status = in_array($status, ['success', 'error', 'ignored']) ? $status : 'executable';

                    $this->count_array[$type]['count_per_operation'][$status] = $countPerStatus->count;
                    $this->count_array[$type]['count'] =
                        ($this->count_array[$type]['count'] ?? 0) + $countPerStatus->count;
                }

                $this->display_operation_submit_form();
            } else
                echo '<i>' . __('There are no executable operations.', 'oes') . '</i>';
            echo '</div>';
        }


        /**
         * Display the operation submit form.
         */
        function display_operation_submit_form()
        {
            ?>
            <div class="oes-pb-1 oes-settings-submit">
                <?php

                echo oes_html_get_form_element('select',
                    'operation-action',
                    'operation-action',
                    [],
                    ['options' => [
                        'execute' => 'Execute Operations',
                        'delete_executable' => 'Delete Executable Operations',
                        'delete_ignored' => 'Delete Ignored Operations',
                        'delete_success' => 'Delete Successful Executed Operations',
                        'delete_error' => 'Delete Error Occurred During Execution',
                        'empty' => 'Delete All Operations',
                        'reset' => 'Reset Status for all Operations',
                        //@oesDevelopment Make only available for admin: 'drop' => 'Drop TEMP'
                    ]]);
                ?>
                <input type="submit" name="apply_operations" id="apply_operations" class="button"
                       value="Apply">
            </div>
            <div class="oes-settings-nav-tabs-container">
            <h2 class="nav-tab-wrapper"><?php

                $selectedType = $_GET['type'] ?? (array_key_first($this->count_array) ?? 'unknown');
                $selectedOperation = $_GET['select'] ??
                    (array_key_first($this->count_array[$selectedType]['count_per_operation']) ?? 'unknown');

                foreach ($this->count_array as $type => $typeData)
                    printf('<a href="%s" class="nav-tab %s" data-type="%s">%s (%s)</a>',
                        admin_url('admin.php?page=oes_tools_operations&type=' . $type),
                        ($selectedType !== $type ? '' : 'nav-tab-active'),
                        $type,
                        (taxonomy_exists($type) ?
                            (get_taxonomy_label($type) ?? '') :
                            (get_post_type_object($type)->label ?? '')
                        ),
                        $typeData['count']
                    );
                ?>
            </h2>
            </div><?php

            $labelMatch = [
                'executable' => 'Executable',
                'success' => 'Successful Executed',
                'error' => 'Error Occurred During Execution',
                'ignored' => 'Ignored'
            ];

            $operationsList = [];
            if (isset($this->count_array[$selectedType]['count_per_operation']) &&
                !empty($this->count_array[$selectedType]['count_per_operation'])) {
                ksort($this->count_array[$selectedType]['count_per_operation']);
                foreach ($this->count_array[$selectedType]['count_per_operation'] as $operation => $count)
                    $operationsList[] = sprintf(
                            '<a href="%s" class="%s" data-type="%s">%s <span class="count">(%s)</span></a>',
                        admin_url('admin.php?page=oes_tools_operations&select=' . $operation . '&type=' . $selectedType),
                        ($selectedOperation !== $operation ? '' : 'current'),
                        $operation,
                        $labelMatch[$operation] ?? 'Unknown',
                        $count
                    );
            }

            if (!empty($operationsList))
                echo '<div class="oes-operation-select"><ul class="subsubsub"><li>' .
                    implode('</li> | <li>', $operationsList) . '</li></ul></div>';


            /* prepare status */
            if (!isset($_GET['select'])) {
                if ($selectedOperation == 'executable') $args['status_not_in'] = "'success','ignored','error'";
                else $args['status'] = ($selectedOperation ?? 'success');
            } elseif ($_GET['select'] == 'executable') $args['status_not_in'] = "'success','ignored','error'";
            else $args['status'] = $_GET['select'] ?? ($selectedOperation ?? 'success');

            /* prepare type */
            if (isset($_GET['type']) || $selectedType) $args['type'] = $_GET['type'] ?? ($selectedType ?? 'success');

            display_all_operations($args);
        }


        /* step 0: Display upload form. */
        function greet()
        {
            echo '<div class="oes-pb-1"><p>';
            _e('This tool allows you to view the available database operations and execute or delete them. ' .
                'You can view a list of all available operations and decide which operations will be executed below.',
                'oes');
            echo '</p><p>';
            _e('You can import operations using the import tool.', 'oes');
            echo '</p>';
            echo '<a href="' . admin_url('admin.php?page=oes_tools_import') . '" class="button button-secondary">' .
                __('Import Operations', 'oes') . '</a>';
            echo '</div>';
        }


        //Overwrite
        function admin_post_tool_action(): void
        {

            if (isset($_POST['operation-action'])) {

                /* prepare return message */
                $message = '';
                $success = false;

                if ($_POST['operation-action'] === 'drop') {
                    global $wpdb;
                    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'oes_operations');
                }
                elseif ($_POST['operation-action'] === 'empty') {

                    /* empty table */
                    $success = delete_all_operation();
                    $message = $success ?
                        __('All operations have been deleted.', 'oes') :
                        __('Sorry, there has been an error while trying to delete all operations.', 'oes');
                } else {

                    /* get operations from database */
                    $operations = get_all_operation();
                    $allOperations = 0;
                    $countOperations = 0;
                    $errorMessages = [];

                    if (count($operations) > 0)
                        foreach ($operations as $operation) {

                            if ($_POST['operation-action'] === 'reset') {

                                /* reset operation status */
                                if (update_operation($operation->id, ['operation_status' => 'reset'])) $countOperations++;
                                else $errorMessages[] = sprintf(
                                        __('There has been an error while trying to reset the status for operation %s.'),
                                        $operation->id);
                                $allOperations++;
                            } elseif ($_POST['operation-action'] === 'execute') {

                                /* get operation (in case something changed during the loop) */
                                $operationAgain = get_operation($operation->id);

                                /* execute operation */
                                $op = new Operation($operationAgain);
                                if (!in_array($op->operation_status, ['ignored', 'success', 'error'])) {
                                    if ($op->execute_operation()) $countOperations++;
                                    else $errorMessages[] = sprintf(
                                            __('There has been an error while trying to execute operation %s.'),
                                            $operation->id);
                                    $allOperations++;
                                }

                            } elseif (oes_starts_with($_POST['operation-action'], 'delete_')) {
                                $statusKey = substr($_POST['operation-action'], 7);
                                if ($operation->operation_status === $statusKey) {

                                    /* delete operation */
                                    if (delete_operation($operation->id)) $countOperations++;
                                    else $errorMessages[] = sprintf(
                                            __('There has been an error while trying to delete operation %s.'),
                                            $operation->id);
                                    $allOperations++;
                                }
                            }
                        }


                    if ($allOperations < 1) {
                        $message .= __('No operations found for this action', 'oes');
                        $success = true;
                    } elseif (!empty($errorMessages)) {
                        $countErrors = sizeof($errorMessages);
                        $message .= sprintf(
                                __(' There ha%s been %s error%s while trying to process operations. Error messages:%s',
                                    'oes'),
                            ($countErrors == 1 ? 's' : 've'),
                            $countErrors,
                            ($countErrors == 1 ? '' : 's'),
                            '<br>' . implode('<br>', $errorMessages)
                        );
                    } else {
                        $message = sprintf(__('%s of %s operations have been processed successfully.', 'oes'),
                            $countOperations,
                            $allOperations
                        );
                        $success = true;
                    }
                }

                /* send feedback */
                add_oes_notice_after_refresh(
                    $message,
                    $success ? 'success' : 'error');
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Operations', 'operations');

endif;