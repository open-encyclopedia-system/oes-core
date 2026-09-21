<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) {
    exit;
}

use WP_Term;
use function OES\Admin\Operations\insert_operation;
use function OES\Admin\Operations\get_max_temp;

if (!class_exists('Import')) :

    /**
     * Class Import
     *
     * Import post, terms and relations from csv files.
     */
    class Import extends Tool
    {
        private int $operation_count = 0;

        /**
         * Store new terms
         * @var array<string, array<string, string>>
         *      [taxonomy][termName] => tempTempId
         */
        private array $new_terms = [];

        /** All parameters for wp_insert_term. */
        private const ARGS_WP_INSERT_TERM = ['term_id', 'term', 'taxonomy', 'alias_of', 'description', 'parent', 'slug'];

        /** All parameters for wp_insert_post. Additional: import_id for import matching. */
        private const ARGS_WP_INSERT_POST = ['ID', 'import_id', 'post_type', 'post_title', 'post_status', 'post_author',
                'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_excerpt', 'comment_status',
                'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
                'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input',
                'meta_input'];


        /** @inheritdoc */
        public function display(): void
        {
            if (!\OES\Rights\user_can_manage_content()) {
                wp_die(__('You do not have permission to import data.', 'oes'));
            }

            echo '<div class="narrow">';

            $displayTable = $this->process_actions();
            $this->set_count();
            $this->display_admin_notices();

            if ($displayTable) {
                $this->greet();
                $this->render_upload_form();

                if ($this->operation_count > 0) {
                    $this->display_bulk_actions();
                    $this->display_operations();
                }
            }
            echo '</div>';
        }

        protected function set_count(): void
        {
            global $wpdb;
            $table = $wpdb->prefix . 'oes_operations';
            $this->operation_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
        }

        protected function process_actions(): bool
        {
            if (!empty($_FILES)) {
                check_admin_referer('import-upload');

                $originalName = $_FILES['import']['name'] ?? '';

                if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'csv') {
                    echo '<div class="notice notice-error is-dismissible"><p>'
                            . esc_html__('Invalid file type. CSV required.', 'oes')
                            . '</p></div>';
                } else {

                    $file = wp_import_handle_upload();
                    if (isset($file['file'])) {
                        $this->handle_upload($file);
                    }
                }

                return true;
            }

            if (!empty($_GET['operations_deleted'])) {
                echo '<div class="notice notice-success is-dismissible"><p>'
                        . esc_html__('Operations deletion completed.', 'oes')
                        . '</p></div>';
            } elseif (!empty($_GET['operations_imported'])) {
                echo '<div class="notice notice-success is-dismissible"><p>'
                        . esc_html__('Operations successfully imported.', 'oes')
                        . '</p></div>';
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {

                if ($_POST['action'] === 'delete_all_operations') {

                    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'oes_delete_all_operations')) {
                        wp_die(__('Security check failed', 'oes'));
                    }

                    \OES\Admin\Operations\delete_all_operations();

                    echo '<div class="updated notice"><p>' . __('Operations table has been reset.', 'oes') . '</p></div>';
                }

                if ($_POST['action'] === 'import_all_operations') {

                    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'oes_import_all_operations')) {
                        wp_die(__('Security check failed', 'oes'));
                    }

                    \OES\Admin\Operations\import_all_operations();

                    $this->display_successful_operations();

                    return false;
                }
            }

            return true;
        }

        protected function display_bulk_actions(): void {

            ?>
            <div class="oes-action-buttons" style="display:flex; gap:6px; align-items:center;">
                <form method="post" onsubmit="return confirm('Are you sure you want to delete all operations?');">
                    <?php wp_nonce_field('oes_delete_all_operations', '_wpnonce'); ?>
                    <input type="hidden" name="action" value="delete_all_operations">
                    <?php submit_button(__('Delete All', 'oes'), 'delete', 'reset_operations_btn', false); ?>
                </form>

                <form method="post" onsubmit="return confirm('Are you sure you want to import all operations?');">
                    <?php wp_nonce_field('oes_import_all_operations', '_wpnonce'); ?>
                    <input type="hidden" name="action" value="import_all_operations">
                    <?php submit_button(__('Import All', 'oes'), 'primary', 'import_all_btn', false); ?>
                </form>
            </div>
            <?php
        }

        protected function display_successful_operations(): void {

            global $wpdb;
            $table = $wpdb->prefix . 'oes_operations';

            $results = $wpdb->get_results(
                    "SELECT * FROM $table WHERE operation_status = 'success' ORDER BY operation_sequence ASC"
            );

            if (empty($results)) {
                _e('No operations to be displayed.', 'oes');
                return;
            }

            $displayBuilder = new \OES\Admin\Operations\Display_Builder();

            $updatedObjects = [];
            foreach ($results as $row) {

                $operation = \OES\Admin\Operations\Operation::from_row($row);
                echo $displayBuilder->build_success_message($operation, $updatedObjects) . '<br>';
            }

            echo '<div class="updated notice"><p>' . sizeof($updatedObjects). __(' Operations have been imported.', 'oes') . '</p></div>';

            $wpdb->query(
                    "DELETE FROM $table WHERE operation_status = 'success'"
            );
        }


        /**
         * Display the upload form and instructions for the import tool.
         */
        protected function greet(): void
        {
            echo '<p>' . esc_html__('Upload a UTF-8 encoded CSV file to prepare import operations.', 'oes') . '</p>';
        }

        protected function display_operations(): void
        {
            require_once OES_CORE_PLUGIN . '/includes/admin/operations/class-operations_list_table.php';
            $listTable = new \Operations_List_Table([
                    'singular' => 'Operation',
                    'plural' => 'Operations',
                    'columns' => [
                            'cb' => ' ',
                            'info' => __('Title', 'oes'),
                            'summary' => __('Summary', 'oes'),
                            'status' => __('Status', 'oes'),
                            'message' => __('Message', 'oes')
                    ]
            ]);

            $listTable->process_bulk_action();
            $listTable->prepare_items();

            echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=oes_tools_import')) . '">';
            echo '<input type="hidden" name="page" value="admin_oes_import" />';
            $listTable->display();
            echo wp_nonce_field('oes_import_bulk_action');
            echo '</form>';
        }

        /**
         * Render upload form.
         * @return void
         */
        protected function render_upload_form(): void
        {
            $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
            $size = size_format($bytes);
            $uploadDir = wp_upload_dir();

            if (!empty($uploadDir['error'])) :
                echo '<div class="error"><p>' . esc_html__('Before uploading, you need to fix the following error:', 'oes') . '</p>';
                echo '<p><strong>' . esc_html($uploadDir['error']) . '</strong></p></div>';
            else :
                ?>
                <form enctype="multipart/form-data" id="import-upload-form" method="POST" class="wp-upload-form"
                      action="<?php echo esc_url(wp_nonce_url('admin.php?page=oes_tools_import', 'import-upload')); ?>">
                    <p>
                        <label for="upload"><?php echo esc_html__('Choose a file from your computer:', 'oes'); ?></label>
                        (<?php printf(esc_html__('Maximum size: %s', 'oes'), esc_html($size)); ?>)
                        <input type="file" id="upload" name="import" size="25"/>
                        <input type="hidden" name="action" value="save"/>
                        <input type="hidden" name="max_file_size" value="<?php echo esc_attr($bytes); ?>"/>
                    </p>
                    <?php submit_button(__('Upload file and prepare import', 'oes')); ?>
                </form>
            <?php
            endif;
        }

        /**
         * Handles the upload and initial parsing of the file to prepare for displaying import options.
         *
         * @param array $file The uploaded file.
         *
         * @return bool False if error uploading or invalid file, true otherwise
         */
        function handle_upload(array $file): bool
        {
            if (isset($file['error'])) {
                return oes_write_log(
                        sprintf(
                                __('Upload error: %s', 'oes'),
                                esc_html($file['error'])
                        ),
                        'Import');
            } else if (!file_exists($file['file'])) {
                return oes_write_log(
                        sprintf(
                                __('Uploaded file could not be found at %s. This may be a permission issue.', 'oes'),
                                '<code>' . esc_html($file['file'] ?? 'unknown') . '</code>'
                        ),
                        'Import');
            }

            $success = $this->parse($file);

            if (!empty($file['id'])) {
                $deleted = wp_delete_attachment((int)$file['id'], true);

                if (!$deleted) {
                    oes_write_log(
                            __('Warning: Imported file could not be deleted from media library.', 'oes'),
                            'Import'
                    );
                }
            }

            return $success;
        }

        /**
         * Parse the csv file and insert the operations.
         *
         * @param array $file
         * @return bool
         */
        private function parse(array $file): bool
        {
            if (!$this->validate_csv_file($file)) {
                return false;
            }

            $handle = fopen($file['file'], 'r');

            if (!$handle) {
                return oes_write_log(
                        __('Could not open the file. Please check file permissions.', 'oes'),
                        'Import'
                );
            }

            $timeLimit = (int)apply_filters('oes/import_time_limit', 600);
            set_time_limit($timeLimit);

            $delimiter = apply_filters('oes/import_csv_delimiter', ';');
            $headers = fgetcsv($handle, 0, $delimiter);

            if (!$headers || count($headers) < 2) {
                fclose($handle);
                return oes_write_log(
                        __('The CSV file appears to be empty or invalid.', 'oes'),
                        'Import'
                );
            }

            $headers = $this->sanitize_headers($headers);
            $matchedLabels = $this->match_labels($headers);
            $columnMap = $this->build_column_map($matchedLabels);

            $operations = [];
            $rowIndex = 0;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {

                $rowData = $this->map_row_to_values($row, $matchedLabels, $columnMap);
                $rowData = $this->modify_values($rowData, $headers, $rowIndex);

                $rowOperations = $this->build_operations_from_row($rowData, $rowIndex);

                if (!empty($rowOperations)) {
                    $operations = array_merge($operations, $rowOperations);
                }

                $rowIndex++;
            }

            fclose($handle);

            $operations = $this->append_new_term_operations($operations);
            $this->persist_operations($operations);

            return true;
        }

        /**
         * Validate csv file.
         *
         * @param array $file
         * @return bool
         */
        private function validate_csv_file(array $file): bool
        {
            if (empty($file['file']) || !file_exists($file['file'])) {
                return oes_write_log(__('Uploaded file not found.', 'oes'), 'Import');
            }

            return true;
        }

        /**
         * Sanitize the file header row.
         *
         * @param array $headers
         * @return array
         */
        private function sanitize_headers(array $headers): array
        {
            return array_map(function ($header) {
                return preg_replace('/^\xEF\xBB\xBF/', '', trim($header));
            }, $headers);
        }

        /**
         * Build the columns header row.
         *
         * @param array $matchedLabels
         * @return array
         */
        private function build_column_map(array $matchedLabels): array
        {
            $map = [];
            $isPost = true;

            foreach ($matchedLabels as $index => $label) {
                if ($label === 'operation') {
                    $map[$index] = 'operation';
                } elseif (in_array($label, self::ARGS_WP_INSERT_POST, true)) {
                    $map[$index] = 'post';
                } elseif (in_array($label, self::ARGS_WP_INSERT_TERM, true)) {
                    $map[$index] = 'term';
                    $isPost = false;
                } elseif (in_array($label, ['term_id', 'append'])) {
                    $map[$index] = 'term';
                } else {
                    $map[$index] = $isPost ? 'meta' : 'term_meta';
                }
            }

            return $map;
        }

        /**
         * Map rows to values.
         *
         * @param array $row
         * @param array $labels
         * @param array $map
         * @return array
         */
        private function map_row_to_values(array $row, array $labels, array $map): array
        {
            $values = [];

            foreach ($row as $col => $value) {

                if (!isset($labels[$col], $map[$col])) {
                    continue;
                }

                if ($map[$col] === 'operation') {
                    $values['operation'] = $value ?: 'insert';
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                $values[$map[$col]][$labels[$col]] = $value;
            }

            return $values;
        }

        /**
         * Build operations represented from the row.
         *
         * @param array $values
         * @param int $rowIndex
         * @return array
         */
        private function build_operations_from_row(array $values, int $rowIndex): array
        {
            $operations = [];
            $operation = $values['operation'] ?? 'insert';

            if (isset($values['post'])) {
                $operations[] = [
                        'operation' => 'post',
                        'args' => [
                                'insert_or_update' => $operation,
                                'object_args' => $values['post'],
                                'meta' => $values['meta'] ?? [],
                                'row' => $rowIndex
                        ]
                ];
            }

            if (isset($values['term'])) {
                $operations[] = [
                        'operation' => 'term',
                        'args' => [
                                'insert_or_update' => $operation,
                                'object_args' => $values['term'],
                                'meta' => $values['meta'] ?? [],
                                'row' => $rowIndex
                        ]
                ];
            }

            return $operations;
        }

        /**
         * Add new term operations if necessary.
         *
         * @param array $operations
         * @return array
         */
        private function append_new_term_operations(array $operations): array
        {
            foreach ($this->new_terms as $taxonomy => $terms) {
                foreach ($terms as $termName => $tempId) {
                    $operations[] = [
                            'operation' => 'term',
                            'sequence' => 2,
                            'temp' => $tempId,
                            'args' => [
                                    'object_args' => [
                                            'term' => $termName,
                                            'taxonomy' => $taxonomy
                                    ]
                            ]
                    ];
                }
            }

            return $operations;
        }

        /**
         * Persist operations by importing them into the database.
         *
         * @param array $operations
         * @return void
         */
        private function persist_operations(array $operations): void
        {
            $rowCounter = get_max_temp();

            foreach ($operations as $data) {

                $tempID = 'temp_' . ++$rowCounter;
                $objectArgs = $data['args']['object_args'] ?? [];
                $objectType = $objectArgs['post_type'] ?? $objectArgs['taxonomy'] ?? 'missing';
                $objectID = (int)($data['args']['object_args']['ID'] ?? ($data['args']['object_args']['object_ID'] ?? 0));
                $postOrTerm = $data['object'] ?? 'post';

                $operation = $data['args']['insert_or_update'] ?? ($objectID ? 'update' : 'insert');

                $success = $this->validate_and_insert_operation(
                        $operation,
                        $postOrTerm,
                        $objectID,
                        wp_json_encode($objectArgs),
                        'args',
                        $objectType,
                        [
                                'temp' => $tempID,
                                'status' => 'ready',
                                'sequence' => 1
                        ]
                );

                if (!$success) {
                    oes_write_log(
                            sprintf(
                                    __('Error while trying to insert args operation for row %s.', 'oes'),
                                    $data['args']['row'] ?? 'row info missing'),
                            'INSERT OPERATION'
                    );
                }

                foreach ($data['args']['meta'] ?? [] as $metaKey => $metaValue) {

                    $success = $this->validate_and_insert_operation(
                            $operation,
                            'meta',
                            $objectID,
                            is_string($metaValue) ? $metaValue : wp_json_encode($metaValue),
                            $metaKey,
                            $objectType,
                            [
                                    'temp' => $tempID,
                                    'status' => 'ready',
                                    'sequence' => 5
                            ]
                    );

                    if ($success) {
                        oes_write_log(
                                sprintf(
                                        __('Error while trying to insert meta operation for row %s.', 'oes'),
                                        $data['args']['row'] ?? 'row info missing'),
                                'INSERT OPERATION'
                        );
                    }
                }
            }
        }

        private function validate_and_insert_operation(string $operation,
                                                       string $object,
                                                       string $objectID,
                                                       string $value,
                                                       string $key = '',
                                                       string $type = '',
                                                       array  $args = [])
        {
            $errors = [];
            $warnings = [];

            if (!in_array($operation, ['insert', 'update', 'delete'], true)) {
                $errors[] = __('Invalid operation type.', 'oes');
            }

            if (!in_array($object, ['post', 'term', 'meta'], true)) {
                $errors[] = __('Invalid operation object.', 'oes');
            }

            $objectArgs = [];

            if ($key === 'args') {
                $objectArgs = json_decode($value, true);

                if (!is_array($objectArgs)) {
                    $errors[] = __('Args must be valid JSON.', 'oes');
                    $objectArgs = [];
                }
            }

            $existingObject = null;

            if ($object === 'post') {

                if (!empty($type) && !post_type_exists($type)) {
                    $errors[] = __('Post type does not exist.', 'oes');
                }

                if (!empty($objectID)) {
                    $existingObject = get_post($objectID);
                }
            } elseif ($object === 'term') {

                if (!empty($type) && !taxonomy_exists($type)) {
                    $errors[] = __('Taxonomy does not exist.', 'oes');
                }

                if (!empty($objectID) && !empty($type)) {
                    $existingObject = get_term($objectID, $type);
                }
            }

            if ($object !== 'meta' && $operation === 'update' && (!$existingObject || is_wp_error($existingObject))) {
                $errors[] = __('Object does not exist.', 'oes');
            }

            if ($existingObject && !is_wp_error($existingObject) && !empty($objectArgs)) {

                $updateRequired = false;

                foreach ($objectArgs as $argsKey => $argsValue) {

                    $currentValue = property_exists($existingObject, $argsKey)
                            ? $existingObject->$argsKey
                            : null;

                    if ((string)$argsValue !== (string)$currentValue) {
                        $updateRequired = true;
                        break;
                    }
                }

                if (!$updateRequired) {
                    $warnings[] = __('No changes detected.', 'oes');
                    $args['status'] = 'ignored';
                }
            }

            $notes = '';
            if (!empty($warnings)) {
                $notes .= __('Warnings: ', 'oes') . implode(' ', $warnings);
            }
            if (!empty($errors)) {
                $notes .= __('Errors: ', 'oes') . implode(' ', $errors);
                $args['status'] = 'error';
            }

            $args['comment'] = ($args['comment'] ?? '') . $notes;

            return insert_operation(
                    $operation,
                    $object,
                    $objectID,
                    $value,
                    $key,
                    $type,
                    $args
            );
        }

        /**
         * Match labels for import.
         *
         * @param array $fieldLabels An array containing the labels (first row of CSV file).
         * @return array Matched labels ready for processing.
         */
        private function match_labels(array $fieldLabels): array
        {
            $fieldLabels = apply_filters('oes/import_match_labels_before', $fieldLabels);
            $matchArray = apply_filters('oes/import_match_data_array', false, $fieldLabels);

            $returnLabels = [];

            if ($matchArray && isset($matchArray['fields']) && is_array($matchArray['fields'])) {

                foreach ($fieldLabels as $index => $label) {

                    if (isset($matchArray['fields'][$label])) {
                        $fieldMatch = $matchArray['fields'][$label];

                        if (is_array($fieldMatch)) {
                            $returnLabel = $fieldMatch['field_key']
                                    ?? (($fieldMatch['repeater_base'] ?? $index) . '_' .
                                            ($fieldMatch['sequence'] ?? 0) . '_' .
                                            ($fieldMatch['repeater_field_key'] ?? $index));
                        } elseif (is_string($fieldMatch)) {
                            $returnLabel = $fieldMatch;
                        } else {
                            $returnLabel = $index;
                        }

                        $returnLabels[$index] = $returnLabel;

                    } elseif ($label === 'operation'
                            || in_array($label, self::ARGS_WP_INSERT_POST, true)
                            || in_array($label, self::ARGS_WP_INSERT_TERM, true)) {
                        $returnLabels[$index] = $label;
                    }
                }

            } else {
                $returnLabels = $fieldLabels;
            }

            return apply_filters('oes/import_match_labels_after', $returnLabels);
        }

        /**
         * Modify values for import.
         *
         * @param array $values An array containing the values for this CSV row.
         * @param array $fieldLabels Field labels from CSV header.
         * @param int $row Current row number.
         * @return array Processed values ready for import.
         */
        private function modify_values(array $values, array $fieldLabels, int $row): array
        {
            $returnValues = apply_filters('oes/import_modify_values_before', $values, $fieldLabels);
            $matchArray = apply_filters('oes/import_match_data_array', false, $fieldLabels);

            if ($matchArray) {

                if (!empty($matchArray['additional']) && is_array($matchArray['additional'])) {
                    foreach ($matchArray['additional'] as $key => $fieldArray) {
                        foreach ($fieldArray as $field => $value) {

                            if (is_array($value) && isset($value['callback'])) {
                                $args = array_merge(['values' => $values], $value['args'] ?? []);
                                $additionalValue = call_user_func($value['callback'], $args);
                            } else {
                                //@oesDevelopment Validate value
                                $additionalValue = is_string($value) ? $value : '';
                            }

                            $returnValues[$key][$field] = $additionalValue;
                        }
                    }
                }

                // Handle field callbacks / repeater / term fields
                $tempStore = [];
                $tempCounter = 0;

                if (!empty($matchArray['fields']) && is_array($matchArray['fields'])) {
                    foreach ($matchArray['fields'] as $tableKey => $match) {
                        if (is_array($match)) {
                            $fieldKey = $match['field_key'] ?? ($match['repeater_base'] ?
                                    ($match['repeater_base'] . '_' . ($match['sequence'] ?? 0) . '_' .
                                            ($match['repeater_field_key'] ?? '')) :
                                    $tableKey); //@oesDevelopment Validate value.
                            $tempStore[$fieldKey] = $match;
                        }
                    }

                    foreach ($returnValues as $subArray => $subArrayValues) {
                        foreach ($subArrayValues as $fieldKey => $fieldValue) {

                            if (!isset($tempStore[$fieldKey])) continue;

                            $args = array_merge([
                                    'match_value' => $fieldValue,
                                    'field_key' => $fieldKey,
                                    'values' => $values
                            ], $tempStore[$fieldKey]['args'] ?? []);

                            // Handle term fields
                            if (isset($tempStore[$fieldKey]['args']['get_term_by'])) {
                                $newValue = '';
                                if (!empty($args['match_value'])) {
                                    $terms = [];
                                    $split = explode(';', $args['match_value']);
                                    foreach ($split as $singleTerm) {
                                        $term = get_term_by($args['get_term_by'] ?? 'name', $singleTerm, $args['taxonomy'][0] ?? false);
                                        if ($term instanceof WP_Term) {
                                            $terms[] = $term->term_id;
                                        } else {
                                            $termTempID = $this->new_terms[$args['taxonomy'][0] ?? 'missing'][$singleTerm]
                                                    ?? ('new_term_' . ($row + 1) . '_' . $tempCounter++);
                                            $this->new_terms[$args['taxonomy'][0] ?? 'missing'][$singleTerm] = $termTempID;
                                            $terms[] = $termTempID;
                                        }
                                    }

                                    $newValue = implode(';', $terms);
                                }
                            } else {
                                $newValue = isset($tempStore[$fieldKey]['callback']) ?
                                        call_user_func($tempStore[$fieldKey]['callback'], $args) :
                                        $fieldValue;
                            }

                            // Handle post-term relation or repeaters
                            if ($fieldKey === 'post_term_ID') {
                                $returnValues[$subArray] = $newValue;
                            } elseif (!empty($tempStore[$fieldKey]['repeater']) && !empty($tempStore[$fieldKey]['repeater_base'])) {
                                $returnValues[$subArray][$tempStore[$fieldKey]['repeater_base']][($tempStore[$fieldKey]['sequence'] ?? 0)][($tempStore[$fieldKey]['repeater_field_key'] ?? '')] = $newValue;
                                unset($returnValues[$subArray][$fieldKey]);
                            } else {
                                $returnValues[$subArray][$fieldKey] = $newValue;
                            }
                        }
                    }
                }

                if (!empty($matchArray['skip_insert'])) {
                    if ($matchArray['skip_insert'] === 'ID' && isset($returnValues['post']['ID'])) {
                        $returnValues['skip_insert']['object_id'] = $returnValues['post']['ID'];
                    } elseif ($matchArray['skip_insert'] === 'term_id' && isset($returnValues['term']['term_id'])) {
                        $returnValues['skip_insert']['term_id'] = $returnValues['term']['term_id'];
                    } else {
                        //@oesDevelopment Error handling.
                    }
                }

                if (!empty($matchArray['parent'])) {
                    $returnValues['parent'] = $matchArray['parent'];
                }
            }

            // Encode relationship / multi-select meta fields as JSON
            if (!empty($returnValues['meta'])) {
                foreach ($returnValues['meta'] as $fieldKey => $fieldValue) {
                    $fieldObject = get_field_object($fieldKey);
                    if (!empty($fieldObject) &&
                            ($fieldObject['type'] === 'relationship' || ($fieldObject['type'] === 'taxonomy' && ($fieldObject['field_type'] ?? '') === 'multi_select'))) {

                        $cleanValue = [];
                        if (is_string($fieldValue)) {
                            $parseValue = str_replace(['\'', '[', ']'], '', $fieldValue);
                            $cleanValue = explode(';', $parseValue);
                        }
                        $returnValues['meta'][$fieldKey] = wp_json_encode($cleanValue);
                    }
                }
            }

            return apply_filters('oes/import_modify_values_after', $returnValues, $fieldLabels);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Import', 'import');

endif;