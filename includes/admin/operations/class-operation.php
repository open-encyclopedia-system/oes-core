<?php

namespace OES\Admin\Operations;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Operation')) :

    /**
     * Class Operation
     *
     * Domain entity representing a single row in the `oes_operations` table.
     */
    class Operation
    {
        /**
         * Unique operation ID (Primary Key).
         *
         * @var int
         */
        public int $id;

        /**
         * Operation action.
         * Example values: insert, update, delete.
         *
         * @var string
         */
        public string $operation;

        /**
         * Target object type.
         * Example values: post, term.
         *
         * @var string
         */
        public string $operation_object;

        /**
         * ID of the affected object (post ID or term ID).
         *
         * 0 indicates a new object (not yet created).
         *
         * @var int
         */
        public int $operation_object_id;

        /**
         * Object subtype.
         * Post type (for posts) or taxonomy (for terms).
         *
         * @var string
         */
        public string $operation_type;

        /**
         * Field key or "args" for full object operations.
         *
         * @var string
         */
        public string $operation_key;

        /**
         * Raw value stored in the database.
         * May contain JSON (for args, repeater, relationship fields, etc.).
         *
         * @var string
         */
        public string $operation_value;

        /**
         * Current status of the operation.
         * Example values: pending, processing, success, error, ignored.
         *
         * @var string
         */
        public string $operation_status;

        /**
         * Additional information, warnings, or error messages.
         *
         * @var string
         */
        public string $operation_comment;

        /**
         * WordPress user ID of the author who created the operation.
         *
         * @var int
         */
        public int $operation_author;

        /**
         * Creation date (MySQL datetime string).
         *
         * @var string
         */
        public string $operation_date;

        /**
         * Temporary reference value used during batch inserts
         * (e.g. linking operations before real object ID exists).
         *
         * @var string
         */
        public string $operation_temp;

        /**
         * Create Operation entity from database row.
         *
         * @param object $row Raw database row object.
         * @return self
         */
        public static function from_row(object $row): self
        {
            $self = new self();

            $self->id = (int) $row->id;
            $self->operation = (string) $row->operation;
            $self->operation_object = (string) $row->operation_object;
            $self->operation_object_id = (int) $row->operation_object_id;
            $self->operation_type = (string) $row->operation_type;
            $self->operation_key = (string) $row->operation_key;
            $self->operation_value = (string) $row->operation_value;
            $self->operation_status = (string) $row->operation_status;
            $self->operation_comment = (string) $row->operation_comment;
            $self->operation_author = (int) $row->operation_author;
            $self->operation_date = (string) $row->operation_date;
            $self->operation_temp = (string) $row->operation_temp;

            return $self;
        }

        /**
         * Determine whether the operation is already finalized.
         *
         * Final states:
         * - success
         * - error
         * - ignored
         *
         * @return bool True if operation should no longer be processed.
         */
        public function is_final(): bool
        {
            return in_array(
                $this->operation_status,
                ['success', 'error', 'ignored'],
                true
            );
        }

        /**
         * Check if this operation represents a full object operation
         * (i.e. uses "args" instead of a single field key).
         *
         * @return bool
         */
        public function is_args(): bool
        {
            return $this->operation_key === 'args';
        }

        /**
         * Get decoded args array if operation_key is "args".
         *
         * Returns null if not an args operation.
         *
         * @return array<string, mixed>|null
         */
        public function get_args(): ?array
        {
            if (!$this->is_args()) {
                return null;
            }

            $decoded = json_decode($this->operation_value, true);

            return is_array($decoded) ? $decoded : null;
        }
    }

endif;
