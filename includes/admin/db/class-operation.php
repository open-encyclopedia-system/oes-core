<?php

namespace OES\Admin\DB;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Error;


if (!class_exists('Operation')) :

    /**
     * Class Operation
     *
     * Class to display, modify or execute a database operation.
     */
    class Operation
    {

        /** @var string The operation id */
        public string $id = '';

        /** @var string The operation action, e.g. 'update', 'insert' */
        public string $operation = '';

        /** @var string The operation object, e.g. 'post', 'term' */
        public string $operation_object = '';

        /** @var string The operation object id. */
        public string $operation_object_id = '';

        /** @var string The operation type, e.g. the post type or taxonomy */
        public string $operation_type = '';

        /** @var string The operation key, e.g. the field key */
        public string $operation_key = '';

        /** @var string The operation value */
        public string $operation_value = '';

        /** @var string The operation status */
        public string $operation_status = '';

        /** @var string The operation comment */
        public string $operation_comment = '';

        /** @var string The operation author */
        public string $operation_author = '';

        /** @var string The operation date (when the operation was inserted) */
        public string $operation_date = '';

        /** @var string A temporary information for this operation */
        public string $operation_temp = '';

        /** @var mixed The affected object, e.g. a WP_Post or WP_Term instance */
        public $object = false;

        /** @var mixed The id of the affected object */
        public $object_id = false;

        /** @var mixed The object type of the operation. */
        public $object_type = false;

        /** @var string The operation key label */
        public string $operation_key_label = '';

        /** @var mixed The args for the post or term. */
        public $key_args = false;

        /** @var array Collect comments for the key args parameter. */
        public array $key_args_ignore_comments = [];

        /** @var bool Boolean identifying if operation is term operation. */
        public bool $is_term = false;

        /** @var array The data to be displayed for e.g. import tool. */
        public array $data_for_display = [];


        /**
         * Operation Constructor.
         *
         * Parse database object to object
         */
        public function __construct($operation)
        {
            foreach (get_object_vars($operation) as $key => $value) {
                $this->$key = $value;
            }
        }


        /**
         * Validate operation parameters.
         * @return void
         */
        public function validate_parameters(): void
        {
            /* prepare general information */
            $generalInformation = [
                'author' => get_the_author_meta('display_name', $this->operation_author),
                'date' => date_i18n('d.m.Y H:m', strtotime($this->operation_date)),
                'comment' => $this->operation_comment,
                'status' => $this->operation_status
            ];


            /**
             * Filters the general data of operation
             *
             * @param array $generalInformation The general information.
             * @param string $this- >id The operation id.
             */
            $generalInformation = apply_filters('oes/get_operation_data_labels', $generalInformation, $this->id);


            /* set general data */
            $this->data_for_display = $generalInformation;

            /* check if key is args (post or term data and not post_meta) */
            if ($this->operation_key === 'args') {
                $args = json_decode($this->operation_value, true);
                $this->key_args = empty($args) ? 'invalid' : $args;
                $this->validate_single_parameter('operation_sequence', '2');
            }

            /* check if post or term */
            $checkID = $this->operation_object_id;
            if ($this->operation_object_id) {
                if (term_exists((int)$this->operation_object_id)) {
                    $term = get_term($this->operation_object_id);
                    $this->object = $term;
                    $this->is_term = true;
                    $this->object_id = $this->object->term_id;

                    /* validate operation object */
                    $this->validate_single_parameter('operation_object', 'term');
                    $this->validate_single_parameter('operation', 'update');
                    $this->validate_single_parameter('operation_type', $term->taxonomy);
                } elseif (get_post_status($this->operation_object_id)) {
                    $post = get_post($this->operation_object_id);
                    $this->object = $post;
                    $this->object_id = $this->object->ID;

                    /* validate operation parameter */
                    $this->validate_single_parameter('operation_object', 'post');
                    $this->validate_single_parameter('operation', 'update');
                    $this->validate_single_parameter('operation_type', $post->post_type);

                } else {
                    $checkID = false;
                    $this->data_for_display['info'][] = sprintf(
                        __('Object with the ID \'%s\' does not exist. Create new object.', 'oes'),
                        $this->operation_object_id
                    );
                }
            }

            if (!$checkID) {

                /* validate post type or taxonomy */
                $objectType = $this->operation_type;
                if ((empty($objectType) || $this->operation_type === 'missing') && $this->key_args) {
                    if (isset($this->key_args['post_type'])) $objectType = $this->key_args['post_type'];
                    elseif (isset($this->key_args['taxonomy'])) $objectType = $this->key_args['taxonomy'];
                }

                if (post_type_exists($objectType)) {
                    $this->object = 'new_post';

                    /* validate operation parameter */
                    $this->validate_single_parameter('operation_object', 'post');
                    $this->validate_single_parameter('operation', 'insert');
                    $this->validate_single_parameter('operation_object_id', 0);
                } elseif (taxonomy_exists($objectType)) {
                    $this->object = 'new_term';
                    $this->is_term = true;

                    /* validate operation parameter */
                    $this->validate_single_parameter('operation_object', 'term');
                    $this->validate_single_parameter('operation', 'insert');
                    $this->validate_single_parameter('operation_object_id', 0);
                } else $this->object = 'unknown';
            }


            /* validate field data */
            if (!$this->key_args) {

                /* get id for field value */
                $idForField = false;
                if ($this->object && $this->object_id)
                    $idForField = $this->is_term ?
                        $this->object->taxonomy . '_' . $this->object_id :
                        $this->object_id;

                if ($fieldObject = get_field_object($this->operation_key, $idForField)) {

                    /* validate new value */
                    $dbValue = $this->operation_value;
                    switch ($fieldObject['type']) {

                        case 'relationship' :

                            /* prepare value */
                            $cleanValue = [];
                            if ($decodedValue = json_decode($dbValue, true)) {

                                /* validate that value is a json decoded array of valid post IDs */
                                if (is_array($decodedValue)) {
                                    foreach ($decodedValue as $singlePostID)
                                        if (get_post($singlePostID)) $cleanValue[] = $singlePostID;
                                }

                                /* update database with clean value if necessary */
                                if ($dbValue !== json_encode($cleanValue))
                                    $this->validate_single_parameter('operation_value', json_encode($cleanValue));

                            } elseif (is_string($dbValue)) {

                                /* convert string to json encoded value */
                                $parseValue = str_replace(['\'', '[', ']'], ['', '', ''], $dbValue);
                                $parseValueArray = explode(';', $parseValue);
                                foreach ($parseValueArray as $singlePostID)
                                    if (get_post($singlePostID)) $cleanValue[] = $singlePostID;

                                /* update database with clean, json encoded value */
                                $this->validate_single_parameter('operation_value', json_encode($cleanValue));
                            }
                            break;

                        case 'post':
                        case 'link' :
                        case 'taxonomy' :
                        case 'date_picker' :
                        case 'date_time_picker' :
                        case 'repeater' :
                            //@oesDevelopment Validate / modify operation_value
                            break;
                    }
                }
            }
        }


        /**
         * Validate single operation parameter and update database if necessary.
         *
         * @param string $key The operation column.
         * @param mixed $value_target The new value.
         * @return void
         */
        function validate_single_parameter(string $key, $value_target): void
        {
            if (($this->operation_status !== 'success' && $this->operation_status !== 'error') &&
                ($this->$key !== $value_target)) {

                $matchLabel = [
                    'operation' => 'operation action',
                    'operation_object' => 'operation object',
                    'operation_object_id' => 'operation object id',
                    'operation_type' => 'operation type',
                    'operation_key' => 'operation key',
                    'operation_value' => 'operation value',
                    'operation_status' => 'operation status',
                    'operation_comment' => 'operation comment',
                    'operation_author' => 'operation author',
                    'operation_date' => 'operation date',
                    'operation_temp' => 'temporary information',
                    'operation_sequence' => 'operation sequence'
                ];

                /* try to update database */
                $success = false;
                if (isset($matchLabel[$key]))
                    $success = update_operation($this->id, [$key => $value_target, 'operation_status' => 'updated']);

                if ($success) {
                    $this->data_for_display['info'][] = sprintf(
                        __('Change %s from \'%s\' to \'%s\'.', 'oes'),
                        $matchLabel[$key],
                        $this->$key,
                        $value_target
                    );
                    $this->$key = $value_target;
                } else {
                    $this->data_for_display['info'][] = sprintf(
                        __('There was an error trying to change %s from \'%s\' to \'%s\'.', 'oes'),
                        $matchLabel[$key],
                        $this->$key,
                        $value_target
                    );
                }
            }
        }


        /**
         * Ignore an operation and update database if necessary.
         *
         * @param string $ignore_message The operation message.
         * @param bool $force Force update
         * @return void
         */
        function ignore_operation(string $ignore_message = '', bool $force = false): void
        {
            if (($this->operation_status !== 'success' && $this->operation_status !== 'error') || $force) {
                $newMessage = $ignore_message;
                if ($this->key_args && !$force) {
                    $this->key_args_ignore_comments[] = $ignore_message;
                    $newMessage = implode('<br>', $this->key_args_ignore_comments);
                }

                /* update database */
                $success = update_operation($this->id, [
                    'operation_comment' => $newMessage,
                    'operation_status' => ($this->key_args && !$force) ? 'ignored_partly' : 'ignored'
                ]);

                if (!$success)
                    $this->data_for_display['info'][] = __('There was an error trying to ignore the operation', 'oes');
                else {
                    $this->operation_status = ($this->key_args && !$force) ? 'ignored_partly' : 'ignored';
                    $this->operation_comment = $newMessage;
                }
            }
        }


        /**
         * Get the data to be displayed
         *
         * @return array The data to be displayed
         */
        function get_data_for_display(): array
        {
            $this->validate_parameters();
            if (!isset($this->data_for_display['values']) || empty($this->data_for_display['values']))
                $this->set_data_for_display();
            return $this->data_for_display;
        }


        /**
         * Set the data to be displayed.
         * @return void
         */
        function set_data_for_display(): void
        {
            /* get data */
            if ($this->key_args) {

                $this->operation_key_label = 'args';

                $labelMatch = [
                    'ID' => 'ignore',
                    'post_title' => 'Post Title',
                    'post_status' => 'Post Status',
                    'post_author' => 'Post Author',
                    'post_content' => 'Post Content',
                    'post_excerpt' => 'Post Excerpt',
                    'post_name' => 'Post Name (Slug)',
                    'post_parent' => 'Post Parent',
                    'post_type' => 'ignore',
                    'post_date' => 'ignore',
                    'post_date_gmt' => 'ignore',
                    'comment_status' => 'ignore',
                    'ping_status' => 'ignore',
                    'post_password' => 'ignore',
                    'to_ping' => 'ignore',
                    'pinged' => 'ignore',
                    'post_modified' => 'ignore',
                    'post_modified_gmt' => 'ignore',
                    'term_id' => 'Term ID',
                    'name' => 'Term Name',
                    'term' => 'Term Name',
                    'taxonomy' => 'Taxonomy',
                    'slug' => 'Term Slug',
                    'description' => 'Term Description',
                    'parent' => 'Term Parent'
                ];


                /**
                 * Filters the considered label.
                 *
                 * @param array $labelMatch The considered label.
                 */
                $labelMatch = apply_filters('oes/get_operation_data_labels', $labelMatch);


                $data = [];
                if (is_array($this->key_args)) {

                    $executableKeys = 0;
                    foreach ($this->key_args as $key => $value) {
                        if (isset($labelMatch[$key])) {
                            if ($labelMatch[$key] !== 'ignore') {

                                $oldValue = '';
                                switch ($key) {

                                    case 'post_author':
                                        if ($this->operation_object_id)
                                            $oldValue = get_the_author_meta('display_name', $this->object->$key);
                                        $newValue = get_the_author_meta('display_name', $value);
                                        break;

                                    case 'post_parent':
                                        if ($this->operation_object_id)
                                            $oldValue = property_exists($this->object, $key) ?
                                                oes_get_display_title($this->object->$key) :
                                                '[unknown]';
                                        $newValue = get_post($value) ?
                                            oes_get_display_title($value) :
                                            '[unknown]';
                                        break;

                                    default:
                                        if ($this->operation_object_id)
                                            $oldValue = property_exists($this->object, $key) ?
                                                $this->object->$key :
                                                '[unknown]';
                                        $newValue = $value;
                                        break;
                                }

                                if ($this->operation_object_id && !property_exists($this->object, $key)) {
                                    $this->ignore_operation(sprintf(__('Unknown property key \'%s\'.', 'oes'), $key));
                                } elseif ((string)$newValue === $oldValue) {
                                    $this->ignore_operation(sprintf(
                                        __('New value for \'%s\' is same as old value.', 'oes'),
                                        $key));
                                } else {
                                    $data[] = [
                                        'key' => $key,
                                        'key_label' => sprintf(
                                            '<strong>%s</strong><code class="oes-object-identifier">%s</code>',
                                            $labelMatch[$key],
                                            $key
                                        ),
                                        'value_new' => (string)$newValue,
                                        'value_old' => $oldValue
                                    ];
                                    $executableKeys++;
                                }
                            }
                        } else {
                            $this->ignore_operation(sprintf(__('Unknown property key \'%s\'.', 'oes'), $key));
                        }
                    }

                    /* no executable keys found */
                    if ($executableKeys < 1 && $this->operation_status !== 'success')
                        $this->ignore_operation(
                            __('No executable parameters found. Values are the same or have invalid keys.', 'oes'),
                            true);
                } else {
                    $this->ignore_operation(__('Invalid value. Value must be json encoded.', 'oes'));
                }

                $this->data_for_display['values'] = $data;
            } else {

                if (empty($this->operation_key)) {
                    $this->ignore_operation(__('Empty key, ignore operation.', 'oes'));
                } else {
                    $key = '<code class="oes-object-identifier">' . $this->operation_key . '</code>';

                    /* get id for field value */
                    $idForField = false;
                    if ($this->object && $this->object_id)
                        $idForField = $this->is_term ?
                            $this->object->taxonomy . '_' . $this->object_id :
                            $this->object_id;

                    $oes = OES();
                    $oldValue = '';
                    $newValue = $this->operation_value;
                    if ($fieldObject = get_field_object($this->operation_key, $idForField)) {

                        $this->operation_key_label = '<strong>' . $fieldObject['label'] . '</strong>' . $key;

                        /* get old value */
                        if ($idForField) $oldValue = oes_get_field_display_value(
                            $this->operation_key,
                            $idForField,
                            ['status' => 'all']);

                        /* skip if value hasn't changed */
                        $displayArgs = [];
                        if (!$idForField && $newValue === $fieldObject['value']) {
                            $this->ignore_operation(__('New value is same as old value.', 'oes'));
                        } /* skip if field is not registered for this post type */
                        elseif (!isset($oes->post_types[$this->operation_type]['field_options'][$this->operation_key]) &&
                            !isset($oes->taxonomies[$this->operation_type]['field_options'][$this->operation_key])) {

                            $this->ignore_operation(sprintf(__('This field is not part of the %s (%s).', 'oes'),
                                $this->is_term ? 'taxonomy' : 'post type',
                                ($this->is_term ?
                                    (get_taxonomy_label($this->operation_type) ?? '') :
                                    (get_post_type_object($this->operation_type)->label ?? '')
                                )
                            ));
                        } else {

                            /* validate new value */
                            $dbValue = $this->operation_value;
                            switch ($fieldObject['type']) {

                                case 'relationship' :

                                    /* prepare value */
                                    $displayedValue = [];
                                    if ($decodedValue = json_decode($dbValue, true)) {

                                        /* validate that value is a json decoded array of valid post IDs */
                                        if (is_array($decodedValue)) {
                                            foreach ($decodedValue as $singlePostID) {
                                                if ($singlePost = get_post($singlePostID))
                                                    $displayedValue[] = $singlePost;
                                                else $this->data_for_display['info'][] = sprintf(
                                                    __('Post with ID \'%s\' not found.', 'oes'),
                                                    $singlePostID);
                                            }
                                        } else {
                                            $this->data_for_display['info'][] =
                                                __('The value has not the right format.', 'oes');
                                        }
                                    }

                                    /* update field */
                                    $dbValue = $displayedValue;

                                    /* display all posts */
                                    $displayArgs['status'] = 'all';
                                    break;

                                case 'taxonomy' :
                                case 'repeater' :
                                    $dbValue = json_decode($dbValue, true);
                                    break;

                                case 'post':
                                case 'link' :
                                case 'date_picker' :
                                case 'date_time_picker' :
                                    //@oesDevelopment Validate / modify operation_value
                                    break;
                            }


                            $displayArgs['value'] = $dbValue;
                            $newValue = oes_get_field_display_value($this->operation_key, false, $displayArgs);

                            if ((string)$newValue === (string)$oldValue && !empty($dbValue))
                                $this->ignore_operation(__('New value is same as old value.', 'oes'));
                            else
                                $this->data_for_display['values'] = [[
                                    'key' => $this->operation_key,
                                    'key_label' => $this->operation_key_label,
                                    'value_new' => (string)$newValue,
                                    'value_old' => (string)$oldValue
                                ]];
                        }
                    } else {

                        //@oesDevelopment Add exceptions for internal fields.
                        $this->ignore_operation(sprintf(__('Operation key \'%s\' is unknown (not a field key).', 'oes'),
                            $this->operation_key
                        ));
                    }
                }
            }
        }


        /**
         * Execute the operation
         */
        function execute_operation(): bool
        {
            if (!in_array($this->operation_status, ['ignored', 'success', 'error'])) {
                switch ([$this->operation, $this->operation_object]) {

                    case ['insert', 'post']:

                        /* insert post */
                        if ($this->operation_key === 'args') {

                            $inserted = oes_insert_post(json_decode($this->operation_value, true), false);

                            /* 'post' , 'wrong_parameter' */
                            if (isset($inserted['post'])) {

                                if ($inserted['post'] instanceof WP_Error) {
                                    $this->update_database_after_execution('error');
                                    return false;
                                } else {
                                    $this->update_database_after_execution('success', $inserted['post']);
                                    $this->update_all_operation_with_new_post_id($inserted['post']);
                                    return true;
                                }
                            }

                        } /* insert post meta */
                        else {

                            /* update fields and post meta */
                            if ($fieldObject = get_field_object($this->operation_key, $this->operation_object_id)) {
                                $value = $this->operation_value;
                                if (in_array($fieldObject['type'], ['taxonomy', 'relationship', 'repeater']))
                                    $value = json_decode($this->operation_value, true);
                                $inserted = update_field($this->operation_key, $value, $this->operation_object_id);
                            } else {
                                $inserted = oes_insert_post_meta(
                                    $this->operation_object_id,
                                    [$this->operation_key => $this->operation_value]);
                            }

                            if (isset($inserted['error']) || !$inserted) {
                                $this->update_database_after_execution('error', 0, (
                                $inserted ?
                                    implode('<br>', $inserted['error']) :
                                    sprintf(
                                        __('Could not update field \'%s\' with value \'%s\' for post with ID ' .
                                            '\'%s\'.', 'oes'),
                                        $this->operation_key,
                                        $this->operation_value,
                                        $this->operation_object_id
                                    )
                                ));
                                return false;
                            } else {
                                $this->update_database_after_execution('success');
                                return true;
                            }
                        }

                        break;

                    /* update post */
                    case ['update', 'post']:

                        /* update post */
                        if ($this->operation_key === 'args') {

                            $updated = oes_insert_post(json_decode($this->operation_value, true));

                            /* 'post' , 'wrong_parameter' */
                            if (isset($updated['post'])) {
                                if ($updated['post'] instanceof WP_Error) {
                                    $this->update_database_after_execution('error');
                                    return false;
                                } else {
                                    $this->update_database_after_execution('success');
                                    return true;
                                }
                            }

                        } /* update post meta */
                        else {

                            /* update fields and post meta */
                            $previousValue = oes_get_field($this->operation_key, $this->operation_object_id);
                            if ($fieldObject = get_field_object($this->operation_key, $this->operation_object_id)) {
                                $value = $this->operation_value;
                                if (in_array($fieldObject['type'], ['taxonomy', 'relationship', 'repeater']))
                                    $value = json_decode($this->operation_value, true);
                                $inserted = update_field($this->operation_key, $value, $this->operation_object_id);
                            } else {
                                $inserted = oes_insert_post_meta(
                                    $this->operation_object_id,
                                    [$this->operation_key => $this->operation_value]);
                            }

                            if (isset($inserted['error'])) {
                                $this->update_database_after_execution('error', 0, implode('<br>',
                                    $inserted ?
                                        $inserted['error'] :
                                        sprintf(
                                            __('Could not update field \'%s\' with value \'%s\' for post with ID ' .
                                                '\'%s\'.', 'oes'),
                                            $this->operation_key,
                                            $this->operation_value,
                                            $this->operation_object_id
                                        )
                                ));
                                return false;
                            } elseif (!$inserted &&
                                ($previousValue !== oes_get_field($this->operation_key, $this->operation_object_id))) {
                                $this->update_database_after_execution('error');
                                return true;
                            } else {
                                $this->update_database_after_execution('success');
                                return true;
                            }
                        }

                        break;

                    /* insert term */
                    case ['insert', 'term']:

                        /* insert term */
                        if ($this->operation_key === 'args') {

                            $inserted = oes_insert_term(json_decode($this->operation_value, true));

                            /* 'post' , 'wrong_parameter' */
                            if (isset($inserted['term'])) {

                                if ($inserted['term'] instanceof WP_Error) {
                                    $this->update_database_after_execution('error',
                                        0,
                                        implode('</br>',
                                            $inserted['term']->errors[array_key_first($inserted['term']->errors)]));
                                    return false;
                                } elseif (isset($inserted['term']['term_id'])) {
                                    $this->update_database_after_execution('success', $inserted['term']['term_id']);
                                    $this->update_all_operation_with_new_post_id($inserted['term']['term_id']);
                                    return true;
                                }
                            }

                        } /* insert post meta */
                        else {

                            /* insert fields and term meta */
                            $inserted = oes_insert_term_meta(
                                $this->operation_object_id,
                                $this->operation_type,
                                [$this->operation_key => $this->operation_value]);

                            if (isset($inserted['error']) || !$inserted) {
                                $this->update_database_after_execution('error', 0, (
                                $inserted ?
                                    implode('<br>', $inserted['error']) :
                                    sprintf(
                                        __('Could not update field \'%s\' with value \'%s\' for term with term ID ' .
                                            '\'%s\'.', 'oes'),
                                        $this->operation_key,
                                        $this->operation_value,
                                        $this->operation_object_id
                                    )
                                ));
                                return false;
                            } else {
                                $this->update_database_after_execution('success');
                                return true;
                            }
                        }

                        break;

                    //@oesDevelopment
                    case ['update', 'term']:
                    case ['insert', 'post_term'] :
                    case 'delete' :
                    default:
                        break;
                }
            }

            return false;
        }


        /**
         * Update database after execution.
         *
         * @param string $status New status.
         * @param int $new_ID New post ID.
         * @param string $messages Add message.
         * @return void
         */
        function update_database_after_execution(string $status, int $new_ID = 0, string $messages = ''): void
        {

            $args = ['operation_status' => $status];
            if (!empty($messages)) $args['operation_comment'] = $messages;
            if ($new_ID) $args['operation_object_id'] = $new_ID;

            $success = update_operation($this->id, $args);

            if ($success) {
                $this->data_for_display['info'][] = __('Successfully executed.', 'oes');
            } else {
                $this->data_for_display['info'][] =
                    __('There has been an error trying to update the database after execution.', 'oes');
            }
        }


        /**
         * Update all operation with new post ID.
         *
         * @param int $new_ID The new post ID.
         * @return void
         */
        function update_all_operation_with_new_post_id(int $new_ID): void
        {

            /* get all rows with temp id */
            if (($tempID = (int)$this->operation_temp) > 0) {

                global $wpdb;
                $table = $wpdb->prefix . 'oes_operations';
                $operations = $wpdb->get_results("SELECT * from $table WHERE `operation_temp` = $tempID");

                if (!empty($operations))
                    foreach ($operations as $operation) {
                        $args = ['operation_object_id' => $new_ID];
                        if ($operation->id != $this->id) $args['operation_status'] = 'updated';
                        $success = update_operation($operation->id, $args);
                        if ($success) {
                            $this->data_for_display['info'][] = __('Successfully executed.', 'oes');
                        } else {
                            $this->data_for_display['info'][] =
                                __('There has been an error trying to update the database after execution.', 'oes');
                        }
                    }
            } elseif (oes_starts_with($this->operation_temp, 'new_term_')) {

                global $wpdb;
                $table = $wpdb->prefix . 'oes_operations';
                $tempID = $this->operation_temp;
                $operations = $wpdb->get_results("SELECT * from $table WHERE `operation_value` LIKE '%$tempID%'");

                /* update value */
                if (!empty($operations))
                    foreach ($operations as $operation) {

                        $newValue = str_replace($tempID, $new_ID, $operation->operation_value);
                        $args = ['operation_value' => $newValue];
                        if ($operation->id != $this->id) $args['operation_status'] = 'updated';
                        $success = update_operation($operation->id, $args);
                        if ($success) {
                            $this->data_for_display['info'][] = __('Successfully executed.', 'oes');
                        } else {
                            $this->data_for_display['info'][] =
                                __('There has been an error trying to update the database after execution.', 'oes');
                        }
                    }
            }
        }
    }
endif;