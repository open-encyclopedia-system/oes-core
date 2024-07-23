<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Post;
use WP_Term;
use function OES\Admin\DB\insert_operation;

if (!class_exists('Import')) :

    /**
     * Class Import
     *
     * Import post, terms and relations from csv files.
     */
    class Import extends Tool
    {

        /** @var array Store new terms. */
        public array $new_terms = [];

        /** All parameters for wp_insert_term. */
        const ARGS_WP_INSERT_TERM = ['term_id', 'term', 'taxonomy', 'alias_of', 'description', 'parent', 'slug'];

        /** All parameters for wp_insert_post. Additional: import_id for import matching. */
        const ARGS_WP_INSERT_POST = ['ID', 'import_id', 'post_type', 'post_title', 'post_status', 'post_author',
            'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_excerpt', 'comment_status',
            'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
            'post_parent', 'menu_order', 'post_mime_type', 'guid', 'post_category', 'tags_input', 'tax_input',
            'meta_input'];


        //Overwrite parent
        function display(): void
        {

            $file = wp_import_handle_upload();
            if (isset($file['file'])) $this->handle_upload($file);

            echo '<div class="narrow">';
            $this->display_admin_notices();
            $this->greet();
            echo '</div>';
        }


        /* step 0: Display upload form. */
        function greet()
        {
            echo '<p>';
            printf(__('Select a .csv file with data you want to import to your database. The file ' .
                'must follow a required format concerning field names or matching classes have to be ' .
                'implemented. A full documentation will be following soon. You can download an import ' .
                'template %shere%s. You can import any database parameter with an import file ' .
                'but we recommend to only import the parameter as provided in the import template. A ' .
                'full documentation for e.g. post type parameters can be found here: %s.', 'oes'),
                '<a href="' . admin_url('admin.php?page=oes_tools_export') . '">',
                '</a>',
                '<a href="https://developer.wordpress.org/reference/classes/wp_post/">' .
                'https://developer.wordpress.org/reference/classes/wp_post/</a>'
            );
            echo '</p><p>';
            _e('The valid operations are: "insert" to add a new object and "update" to edit an ' .
                'existing object. For an "update" ' .
                'operation you need a valid object id.', 'oes');
            echo '</p><p>';
            _e(//@oesDevelopment Validate 'The time limit for import operations has been set to 10 minutes. ' .
                '<strong>Please note</strong> that as for now the .csv files are ' .
                '<strong>utf-8</strong> encoded!',
                'oes');
            echo '</p><p>';
            _e('It is recommended to import not too much data at once.'
                /* @oesDevelopment Implement . 'You can import selected rows by filling the row input options.' */,
                'oes');
            echo '</p>';


            //@oesDevelopment Modify wp_import_upload_form('admin.php?page=oes_tools_import');
            $bytes = apply_filters('import_upload_size_limit', wp_max_upload_size());
            $size = size_format($bytes);
            $upload_dir = wp_upload_dir();
            if (!empty($upload_dir['error'])) :
                ?>
                <div class="error">
                    <p><?php
                        _e('Before you can upload your import file, you will need to fix the following error:');
                        ?></p>
                    <p><strong><?php echo $upload_dir['error']; ?></strong></p></div>
            <?php
            else :
                ?>
                <form enctype="multipart/form-data" id="import-upload-form" method="POST" class="wp-upload-form"
                      action="<?php echo esc_url(wp_nonce_url('admin.php?page=oes_tools_import', 'import-upload')); ?>">
                    <p>
                        <?php
                        printf(
                            '<label for="upload">%s</label> (%s)',
                            __('Choose a file from your computer:', 'oes'),
                            sprintf(__('Maximum size: %s', 'oes'), $size)
                        );
                        ?>
                        <input type="file" id="upload" name="import" size="25"/>
                        <input type="hidden" name="action" value="save"/>
                        <input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>"/>
                    </p>
                    <?php submit_button(__('Upload file and import', 'oes')); ?>
                </form>
            <?php
            endif;


            /* get current available operations */
            $operations = 0;
            global $wpdb;
            $table = $wpdb->prefix . 'oes_operations';
            $count = $wpdb->get_results(
                "SELECT count(`operation_status`) AS count from $table WHERE `operation_status` <> 'ignored' OR `operation_status` <> 'success' OR `operation_status` <> 'error'"
            );
            if (isset($count[0]) && property_exists($count[0], 'count') && $count[0]->count)
                $operations = $count[0]->count;

            echo '<p>';
            _e('The imported data is stored as executable operations. You can view a list of all available ' .
                'operations and decide which operations will be executed. The number inside the brackets indicates ' .
                'how many operations are currently stored in the database.', 'oes');
            echo '</p>';
            printf('<a href="%s" class="button button-secondary">%s (%s)</a>',
                admin_url('admin.php?page=oes_tools_operations'),
                __('See Operations', 'oes'),
                $operations
            );
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
            /* error handling */
            if (isset($file['error'])) {
                $this->admin_notices[] = [
                    'notice' => __('Sorry, there has been an error while uploading the file. %s', 'oes'),
                    esc_html($file['error']),
                    'type' => 'error'
                ];
                return false;
            } else if (!file_exists($file['file'])) {
                $this->admin_notices[] = [
                    'notice' => sprintf(__('Sorry, there has been an error while uploading the file. %s', 'oes'),
                            esc_html($file['error'])) . '<br>' .
                        sprintf(__('The export file could not be found at <code>%s</code>. It is likely that this ' .
                            'was caused by a permission problem.', 'oes'),
                            esc_html($file['file'])),
                    'type' => 'error'
                ];
                return false;
            }

            /* parse file */
            $success = $this->parse($file);

            /* delete attachment */
            $deleteSuccess = wp_delete_attachment($file['id']);
            if (!$deleteSuccess)
                $this->admin_notices[] = [
                    'notice' => __('There was an error trying to delete the imported file.', 'oes'),
                    'type' => 'error'
                ];

            return $success;
        }


        /**
         * Parse file and retrieve operation information.
         *
         * @param array $file The file data.
         * @return bool Return true on success.
         */
        function parse(array $file): bool
        {

            /* Read file ---------------------------------------------------------------------------------------------*/

            /* try to open file */
            $handle = fopen($file['file'], 'r');
            if (!$handle) {
                $this->admin_notices[] = [
                    'notice' => __('Could not open the file. Please check the permission rights.', 'oes'),
                    'type' => 'error'
                ];
                return false;
            }
            if (!strpos($file['file'], '.csv')) {
                $this->admin_notices[] = [
                    'notice' => __('You seem to have uploaded an incorrect file type. ' .
                        'CSV-File is required, please check the file and make sure that is has the ' .
                        'extension \'.csv\'.', 'oes'),
                    'type' => 'error'
                ];
                return false;
            }


            /**
             * Filter the time limit.
             *
             * @param string $timeLimit The time limit.
             */
            $timeLimit = apply_filters('oes/import_time_limit', 600);
            set_time_limit($timeLimit);


            /* check if file is empty */
            $firstRow = fgetcsv($handle, 0, ";");
            if (sizeof($firstRow) < 2) {
                $this->admin_notices[] = [
                    'notice' => __('The file appears to be empty. Check the file and make sure that more than the ' .
                        'first column is filled.', 'oes'),
                    'type' => 'error'
                ];
                return false;
            }

            /* get number of columns and prepare labels */
            $numColumns = count($firstRow);
            $readFieldLabels = [];

            /* strip value of UTF-8 tags (ZWNBSP) */
            for ($col = 0; $col < $numColumns; $col++)
                $readFieldLabels[$col] = str_replace('?', '', utf8_decode($firstRow[$col])); //@oesDevelopment Validate utf8

            /* filter for matching labels */
            $matchedFieldLabels = $this->match_labels($readFieldLabels);

            /* sort fields into WordPress objects 'post', 'term', (post-/term-) 'meta'. */
            $colMatch = [];
            $operationIncluded = false;
            foreach ($matchedFieldLabels as $key => $label) {
                if ($label == 'operation') $operationIncluded = $key;
                elseif (in_array($label, self::ARGS_WP_INSERT_POST)) $colMatch[$key] = 'post';
                elseif (in_array($label, self::ARGS_WP_INSERT_TERM)) $colMatch[$key] = 'term';
                elseif ($label == 'term_id') $colMatch[$key] = 'term';
                elseif ($label == 'append') $colMatch[$key] = 'term';
                elseif ($label == 'post_ID') $colMatch[$key] = 'post_term';
                else $colMatch[$key] = 'meta';

                //@oesDevelopment Add case when post meta and term meta are in same file.
            }

            /* prepare loop */
            $firstRow = $_POST['file-row-start'] ?? 0;
            $lastRow = $_POST['file-row-end'] ?? 0;
            $countRows = 0;
            $allRows = [];
            while (($fileRow = fgetcsv($handle, 0, ";")) !== FALSE &&
                ($lastRow < 1 || $countRows < $lastRow + 1)) {
                if ($countRows > $firstRow - 1) $allRows[] = $fileRow;
                $countRows++;
            }

            /* Close file --------------------------------------------------------------------------------------------*/
            fclose($handle);


            /**
             * Filter the rows.
             *
             * @param string $allRows The rows.
             * @param string $readFieldLabels Field Labels.
             */
            $allRows = apply_filters('oes/import_rows_before_loop', $allRows, $readFieldLabels);


            /* Prepare data  -----------------------------------------------------------------------------------------*/
            $prepareOperations = [];
            foreach ($allRows as $row => $nextRow) {

                //@oesDevelopment Handle encoding to include umlaute, $nextRow = array_map("utf8_encode", $nextRow);

                /* read row into value array */
                $values = [];

                /* loop through columns and skip not matched and operation columns */
                for ($col = 0; $col < $numColumns; $col++)
                    if ((!$operationIncluded || $col != $operationIncluded) && isset($matchedFieldLabels[$col])
                        && isset($colMatch[$col]) && !is_null($nextRow[$col])) {
                        $values[$colMatch[$col]][$matchedFieldLabels[$col]] = $nextRow[$col];
                    }

                /* get operation */
                $operation = ($operationIncluded !== false) ? $nextRow[$operationIncluded] : 'update';

                /* optional modify or augment values */
                $values = $this->modify_values($values, $readFieldLabels, $row);

                /* check if post data */
                if (isset($values['post'])) {

                    $collectDataForOperation = [];

                    /* check if post already exists or creation is skipped */
                    $post = false;
                    if (isset($values['skip_insert']) && $values['skip_insert'])
                        $post = intval($values['skip_insert']['post_id']) ?
                            get_post($values['skip_insert']['post_id']) : false;
                    elseif ($operation == 'update' && isset($values['post']['ID']) && !empty($values['post']['ID']))
                        $post = get_post($values['post']['ID']);
                    else
                        $collectDataForOperation = [
                            'insert_or_update' => 'insert',
                            'object_args' => $values['post'],
                            'operation' => $operation,
                            'row' => $row
                        ];

                    /* check if successful */
                    if ($post instanceof WP_Post || !empty($collectDataForOperation)) {

                        /* update post data --------------------------------------------------------------------------*/
                        if (sizeof($values['post']) > 2) {
                            if (empty($collectDataForOperation))
                                $collectDataForOperation = [
                                    'insert_or_update' => 'update',
                                    'object_id' => $post->ID,
                                    'row' => $row
                                ];
                            $collectDataForOperation['object_args'] = $values['post'];
                        }

                        /* update fields and post meta ---------------------------------------------------------------*/
                        if (!empty($values['meta'])) {
                            if (empty($collectDataForOperation))
                                $collectDataForOperation = [
                                    'insert_or_update' => 'update',
                                    'object_args' => $values['post'],
                                    'object_id' => $post->ID,
                                    'row' => $row
                                ];
                            $collectDataForOperation['meta'] = $values['meta'];
                            $collectDataForOperation['add'] = $values['add'] ?? false;
                        }

                        /* update terms ------------------------------------------------------------------------------*/
                        //@oesDevelopment Differentiate between append and replace
                        if (!empty($values['term']) &&
                            isset($values['term']['term_id']) &&
                            isset($values['term']['taxonomy'])) {
                            if (empty($collectDataForOperation))
                                $collectDataForOperation = [
                                    'insert_or_update' => 'update',
                                    'object_args' => $values['post'],
                                    'object_id' => $post->ID,
                                    'row' => $row
                                ];
                            if (intval($values['term']['term_id']))
                                $collectDataForOperation['terms'][$values['term']['taxonomy']] =
                                    intval($values['term']['term_id']);
                            $collectDataForOperation['append'] = boolval($values['term']['append'] ?? false);
                        }
                    }

                    if (!empty($collectDataForOperation))
                        $prepareOperations[] = [
                            'operation' => 'post',
                            'args' => $collectDataForOperation
                        ];

                } /* check if term data */
                elseif (isset($values['term'])) {

                    $collectDataForOperation = [];

                    /* insert term or skip */
                    $term = false;
                    if (isset($values['skip_insert']) && $values['skip_insert'])
                        $term = intval($values['skip_insert']['term_id']) ?
                            get_term($values['skip_insert']['term_id']) : false;
                    elseif ($operation == 'update' &&
                        isset($values['term']['term_id']) &&
                        !empty($values['term']['term_id']))
                        $term = get_term($values['term']['term_id']) ?? false;
                    else
                        $collectDataForOperation = [
                            'operation' => 'term',
                            'args' => [
                                'object_args' => $values['term'],
                                'row' => $row
                            ]];

                    /* check if successful */
                    $isTerm = $term instanceof WP_Term;
                    if ($isTerm || !empty($collectDataForOperation)) {

                        /* update term data --------------------------------------------------------------------------*/
                        if (sizeof($values['term']) > 2) {
                            if (empty($collectDataForOperation))
                                $collectDataForOperation = [
                                    'insert_or_update' => 'update',
                                    'object_id' => $term->term_id,
                                    'row' => $row
                                ];
                            $collectDataForOperation['object_args'] = $values['term'];
                        }

                        /* update fields and post meta ---------------------------------------------------------------*/
                        if (!empty($values['meta'])) {

                            if (isset($collectDataForOperation['operation'])) {
                                if ($isTerm) $collectDataForOperation['args']['object_id'] = $term->term_id; //sic!
                                $collectDataForOperation['args']['meta'] = $values['meta'];
                                if ($isTerm) $collectDataForOperation['args']['taxonomy'] = $term->taxonomy;
                            } else
                                $collectDataForOperation = [
                                    'operation' => 'term_meta_update',
                                    'args' => [
                                        'object_id' => $term->term_id, //sic!
                                        'meta' => $values['meta'],
                                        'taxonomy' => $term->taxonomy,
                                        'row' => $row
                                    ]];
                        }
                    }

                    if (!empty($collectDataForOperation)) $prepareOperations[] = $collectDataForOperation;
                }
            }

            /* check if new terms are added */
            foreach ($this->new_terms as $taxonomy => $taxonomyTerms)
                foreach ($taxonomyTerms as $newTerm => $newTermID) {

                    $prepareOperations[] = [
                        'operation' => 'term',
                        'sequence' => 1,
                        'temp' => $newTermID,
                        'args' => [
                            'object_args' => [
                                'term' => $newTerm,
                                'taxonomy' => $taxonomy
                            ]
                        ]];
                }

            /* Add operations to database ----------------------------------------------------------------------------*/
            $countSuccess = 0;
            foreach ($prepareOperations as $data) {

                /* prepare data */
                $tempID = (int)($data['args']['row'] ?? 0) + 1;
                $objectID = ($data['args']['object_id'] ?? 0);

                $objectType = 'missing';
                switch ($data['operation']) {
                    case 'post':
                        $objectType = $data['args']['object_args']['post_type'] ?? 'missing';
                        break;

                    case 'term':
                        $objectType = $data['args']['object_args']['taxonomy'] ?? 'missing';
                        break;
                }

                /* insert post data */
                if (isset($data['args']['object_args']) &&
                    sizeof($data['args']['object_args']) > 1) {
                    $success = insert_operation(
                        $objectID ? 'update' : 'insert',
                        $data['operation'],
                        $objectID ?? 0,
                        json_encode($data['args']['object_args']),
                        'args',
                        $objectType,
                        [
                            'temp' => $data['temp'] ?? ($objectID ? '' : $tempID),
                            'sequence' => $data['sequence'] ?? 0,
                            'status' => 'imported']);
                    if ($success) $countSuccess++;
                    else
                        $this->admin_notices[] = [
                            'notice' => '<p>' .
                                sprintf(__('Error while inserting operation for row %s.', 'oes'), $tempID) .
                                '</p>',
                            'type' => 'error'
                        ];
                }


                /* insert metadata */
                if (isset($data['args']['meta']) && sizeof($data['args']['meta']) > 0)
                    foreach ($data['args']['meta'] as $key => $value) {
                        $success = insert_operation(
                            $objectID ? 'update' : 'insert',
                            $data['operation'],
                            $objectID ?? 0,
                            (is_array($value) ? json_encode($value) : $value),
                            $key,
                            $objectType,
                            ['temp' => $objectID ? '' : $tempID, 'status' => 'imported']
                        );
                        if ($success) $countSuccess++;
                        else $this->admin_notices[] = [
                            'notice' => sprintf('<p>' .
                                __('Error while inserting operation for row %s.', 'oes') .
                                '</p>',
                                $tempID),
                            'type' => 'error'
                        ];
                    }
            }

            if ($countSuccess)
                $this->admin_notices[] = [
                    'notice' => sprintf(
                            _n('1 operation has been inserted into the database. ',
                                '%s operations have been inserted into the database. ',
                                $countSuccess,
                                'oes'),
                            $countSuccess) .
                        sprintf(__('You can see the prepared operations %shere%s.', 'oes'),
                            '<a href="' . admin_url('admin.php?page=oes_tools_operations') . '">',
                            '</a>'),
                    'type' => 'info'
                ];

            /* return success */
            return true;
        }


        /**
         * Match labels for import.
         *
         * @param array $fieldLabels An array containing the labels (first row of csv file)
         * @return mixed|void Returns error or array with matched labels.
         */
        function match_labels(array $fieldLabels)
        {

            /**
             * Filter the field labels.
             *
             * @param array $fieldLabels The field labels.
             */
            $fieldLabels = apply_filters('oes/import_match_labels_before', $fieldLabels);


            /**
             * Filter if fields have match array.
             *
             * @param array $matchArray The matching information
             * @param array $fieldLabels The field labels.
             */
            $matchArray = apply_filters('oes/import_match_data_array', false, $fieldLabels);


            /* prepare matched labels */
            $returnLabels = [];
            if ($matchArray) {

                /* check for fields and loop through fields */
                if (isset($matchArray['fields'])) {
                    foreach ($fieldLabels as $key => $value) {
                        if (isset($matchArray['fields'][$value])) {

                            /* prepare label */
                            $returnLabel = $key;
                            if (is_array($matchArray['fields'][$value])) {
                                $returnLabel = $matchArray['fields'][$value]['field_key'] ??
                                    (($matchArray['fields'][$value]['repeater_base'] ?? $key) . '_' .
                                        ($matchArray['fields'][$value]['sequence'] ?? 0) . '_' .
                                        ($matchArray['fields'][$value]['repeater_field_key'] ?? $key));
                            } elseif (is_string($matchArray['fields'][$value])) {
                                $returnLabel = $matchArray['fields'][$value];
                            }

                            $returnLabels[$key] = $returnLabel;

                        } /* add to label if key is part of post, term or operation*/
                        elseif ($value == 'operation'
                            || in_array($value, self::ARGS_WP_INSERT_POST)
                            || in_array($value, self::ARGS_WP_INSERT_TERM)) {
                            $returnLabels[$key] = $value;
                        }
                    }
                }
            } else {
                /* no modification */
                $returnLabels = $fieldLabels;
            }


            /**
             * Filter the return labels.
             *
             * @param array $returnLabels The field labels.
             */
            return apply_filters('oes/import_match_labels_after', $returnLabels);
        }


        /**
         * Modify values for import.
         *
         * @param array $values An array containing the values (for this csv-file row).
         * @param array $fieldLabels An array containing the field labels.
         * @param int $row The row id.
         * @return mixed|void Returns error or array with modified values.
         */
        function modify_values(array $values, array $fieldLabels, int $row)
        {

            /**
             * Filter the return values before processing.
             *
             * @param array $values The return values.
             * @param array $fieldLabels The field labels.
             */
            $returnValues = apply_filters('oes/import_modify_values_before', $values, $fieldLabels);


            /**
             * Filter the matching information.
             *
             * @param array $matchArray The matching information.
             * @param array $fieldLabels The field labels.
             */
            $matchArray = apply_filters('oes/import_match_data_array', false, $fieldLabels);


            if ($matchArray) {

                /* check if additional fields, merge to existing values */
                if (isset($matchArray['additional'])) {

                    /* check for callbacks */
                    foreach ($matchArray['additional'] as $key => $valueArray) {
                        foreach ($valueArray as $field => $value) {

                            /* prepare value */
                            $additionalValue = '';

                            /* check for callback */
                            if (is_array($value)) {
                                if (isset($value['callback'])) {
                                    $args = $value['args'] ?? [];
                                    $args = array_merge(['values' => $values], $args);
                                    $additionalValue = call_user_func($value['callback'], $args);
                                }
                            } else {
                                //@oesDevelopment Validate value
                                $additionalValue = is_string($value) ? $value : '';
                            }
                            $returnValues[$key][$field] = $additionalValue;
                        }
                    }
                }

                /* check for callbacks */
                if (isset($matchArray['fields'])) {

                    /* prepare matching array */
                    $temp = 0;
                    foreach ($matchArray['fields'] as $tableKey => $match) {
                        if (is_array($match)) {
                            $fieldKey = $match['field_key'] ?? ($match['repeater_base'] ?
                                ($match['repeater_base'] . '_' . ($match['sequence'] ?? 0) . '_' .
                                    ($match['repeater_field_key'] ?? '')) :
                                $tableKey); //@oesDevelopment Validate value.
                            $tempStore[$fieldKey] = $match;
                        }
                    }

                    /* store as new value */
                    foreach ($returnValues as $subArray => $subArrayValues) {
                        foreach ($subArrayValues as $fieldKey => $fieldValue) {

                            /* call function*/
                            if (isset($tempStore[$fieldKey])) {

                                /* add original value */
                                $args = array_merge([
                                    'match_value' => $fieldValue,
                                    'field_key' => $fieldKey,
                                    'values' => $values
                                ],
                                    $tempStore[$fieldKey]['args'] ?? []);

                                /* check if term field */
                                if (isset($tempStore[$fieldKey]['args']['get_term_by'])) {

                                    $newValue = '';
                                    if (isset($args['match_value']) && !empty($args['match_value'])) {

                                        $split = explode(';', $args['match_value']);
                                        $terms = [];
                                        foreach ($split as $singleTerm) {
                                            $term = get_term_by($args['get_term_by'] ?? 'name',
                                                $singleTerm,
                                                $args['taxonomy'][0] ?? false);
                                            if ($term instanceof WP_Term) $terms[] = $term->term_id;
                                            else {

                                                /* check if term already prepared */
                                                if (isset($this->new_terms[$args['taxonomy'][0] ?? 'missing'][$singleTerm])) {
                                                    $termTempID = $this->new_terms[$args['taxonomy'][0] ?? 'missing'][$singleTerm];
                                                } else {
                                                    /* insert new term operation */
                                                    $termTempID = 'new_term_' . ($row + 1) . '_' . $temp++;
                                                    $this->new_terms[$args['taxonomy'][0] ?? 'missing'][$singleTerm] =
                                                        $termTempID;
                                                }

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


                                /* check if post - term relation */
                                if ($fieldKey == 'post_term_ID') {
                                    $returnValues[$subArray] = $newValue;
                                } elseif (isset($tempStore[$fieldKey]['repeater']) &&
                                    $tempStore[$fieldKey]['repeater'] &&
                                    $tempStore[$fieldKey]['repeater_base']) {

                                    if (!empty($newValue))
                                        $returnValues[$subArray][$tempStore[$fieldKey]['repeater_base']][($tempStore[$fieldKey]['sequence'] ?? 0)][$tempStore[$fieldKey]['repeater_field_key']] =
                                            $newValue;
                                    unset($returnValues[$subArray][$fieldKey]);
                                } else {
                                    $returnValues[$subArray][$fieldKey] = $newValue;
                                }
                            }
                        }
                    }
                }

                /* check if skip insert */
                if (isset($matchArray['skip_insert'])) {

                    /* skip post */
                    if ($matchArray['skip_insert'] == 'ID') {
                        $returnValues['skip_insert']['object_id'] = $returnValues['post']['ID'];
                    } elseif ($matchArray['skip_insert'] == 'term_id') {
                        $returnValues['skip_insert']['term_id'] = $returnValues['term']['term_id'];
                    } else {
                        //@oesDevelopment Error handling.
                    }
                }

                /* check for parent and store for after processing */
                //@oesDevelopment Validate value.
                if (isset($matchArray['parent'])) $returnValues['parent'] = $matchArray['parent'];

            }

            /* validate field values : json encoded relationship values */
            if (isset($returnValues['meta']))
                foreach ($returnValues['meta'] as $fieldKey => $fieldValue)
                    if ($fieldObject = get_field_object($fieldKey))
                        if (isset($fieldObject['type']) &&
                            ($fieldObject['type'] === 'relationship' ||
                                $fieldObject['type'] == 'taxonomy' && $fieldObject['field_type'] == 'multi_select')) {

                            /* prepare value */
                            $cleanValue = [];
                            if (is_string($fieldValue)) {

                                /* convert string to json encoded value */
                                $parseValue = str_replace(['\'', '[', ']'], ['', '', ''], $fieldValue);
                                $cleanValue = explode(';', $parseValue);
                            }
                            $returnValues['meta'][$fieldKey] = json_encode($cleanValue);
                        }


            /**
             * Filter the return values after processing.
             *
             * @param array $returnValues The return values.
             * @param array $returnValues The field labels.
             */
            return apply_filters('oes/import_modify_values_after', $returnValues, $fieldLabels);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Import', 'import');

endif;