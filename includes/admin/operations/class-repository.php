<?php

namespace OES\Admin\Operations;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Repository')) :

    /**
     * Class Repository
     *
     * Handles all database interactions for Operation entities.
     */
    class Repository
    {
        /**
         * Fully qualified table name.
         *
         * @var string
         */
        private string $table;

        /**
         * Repository constructor.
         *
         * Initializes the table name using WordPress table prefix.
         */
        public function __construct()
        {
            global $wpdb;
            $this->table = $wpdb->prefix . 'oes_operations';
        }

        /**
         * Find an operation by its primary ID.
         *
         * @param int $id Operation ID.
         * @return Operation|null Returns Operation entity or null if not found.
         */
        public function find(int $id): ?Operation
        {
            global $wpdb;

            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table} WHERE id = %d",
                    $id
                )
            );

            return $row ? Operation::from_row($row) : null;
        }

        /**
         * Update operation data in database.
         *
         * @param int $id Operation ID.
         * @param array<string, mixed> $data Associative array of column => value.
         * @return bool True on success, false on failure.
         */
        public function update(int $id, array $data): bool
        {
            global $wpdb;

            return (bool) $wpdb->update(
                $this->table,
                $data,
                ['id' => $id]
            );
        }

        /**
         * Find all operations by temporary reference value.
         *
         * Used during batch inserts where objects do not yet have a real ID.
         *
         * @param string $temp Temporary identifier.
         * @return Operation[] Array of Operation entities.
         */
        public function find_by_temp(string $temp): array
        {
            global $wpdb;

            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table} WHERE operation_temp = %s",
                    $temp
                )
            );

            return array_map(
                fn($row) => Operation::from_row($row),
                $rows ?? []
            );
        }

        public function update_by_temp(string $temp, array $data): bool
        {
            global $wpdb;

            $updated = $wpdb->update(
                $this->table,
                $data,
                ['operation_temp' => $temp],
                null,
                ['%s']
            );

            return $updated !== false;
        }
    }

endif;
