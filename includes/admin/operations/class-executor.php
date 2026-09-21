<?php

namespace OES\Admin\Operations;

if (!defined('ABSPATH')) {
    exit;
}

use WP_Error;

if (!class_exists('Executor')) :

    /**
     * Class Executor
     *
     * Executes Operation entities by delegating to the appropriate WordPress core functions (posts, terms, meta updates).
     */
    class Executor
    {
        /**
         * Repository used to persist operation status changes.
         *
         * @var Repository
         */
        private Repository $repository;

        /**
         * Executor constructor.
         *
         * @param Repository $repository
         */
        public function __construct(Repository $repository)
        {
            $this->repository = $repository;
        }

        /**
         * Execute a single operation.
         *
         * Prevents execution if operation is already final.
         *
         * @param Operation $operation
         * @return bool True if executed successfully, false otherwise.
         */
        public function execute(Operation $operation): bool
        {
            if ($operation->is_final()) {
                return false;
            }

            return match ([$operation->operation, $operation->operation_object]) {
                ['insert', 'post'] => $this->insert_post($operation),
                ['update', 'post'] => $this->insert_post($operation, true),
                ['insert', 'term' ]=> $this->insert_term($operation),
                ['update', 'term'] => $this->insert_term($operation, true),
                ['insert', 'meta'], ['update', 'meta'] => $this->update_post_meta($operation),
                ['insert', 'term_meta'], ['update', 'term_meta'] => $this->update_term_meta($operation),
                default => false,
            };
        }

        /**
         * Insert a new post or update post meta.
         *
         * @param Operation $operation
         * @param bool $update
         * @return bool
         */
        private function insert_post(Operation $operation, bool $update = false): bool
        {
            if ($operation->is_args()) {

                $args = $operation->get_args();
                if($update) {
                    $args['ID'] = $operation->operation_object_id;
                    $result = wp_update_post($args, true);
                }
                else {
                    $result = wp_insert_post($args, true);
                }

                if ($result instanceof WP_Error) {
                    return $this->fail($operation, $result->get_error_message());
                }

                return $this->success($operation, $result);
            }

            return $this->update_post_meta($operation);
        }

        /**
         * Update post meta field.
         *
         * @param Operation $operation
         * @return bool
         */
        private function update_post_meta(Operation $operation): bool
        {
            $result = update_post_meta(
                $operation->operation_object_id,
                $operation->operation_key,
                $operation->operation_value
            );

            if ($result === false) {
                return $this->fail($operation, __('Meta update failed.', 'oes'));
            }

            return $this->success($operation);
        }

        /**
         * Update term meta field.
         *
         * @param Operation $operation
         * @return bool
         */
        private function update_term_meta(Operation $operation): bool
        {
            $result = update_term_meta(
                $operation->operation_object_id,
                $operation->operation_key,
                $operation->operation_value
            );

            if ($result === false) {
                return $this->fail($operation, __('Meta update failed.', 'oes'));
            }

            return $this->success($operation);
        }

        /**
         * Insert a new term.
         *
         * @param Operation $operation
         * @param bool $update
         * @return bool
         */
        private function insert_term(Operation $operation, bool $update = false): bool
        {
            if ($operation->is_args()) {

                $args = $operation->get_args();

                if($update){

                    $termID = (int) $operation->operation_object_id;

                    if (empty($termID)) {
                        return $this->fail($operation, __('Missing term ID.', 'oes'));
                    }

                    $result = wp_update_term(
                        $termID,
                        $operation->operation_type,
                        $args
                    );
                }
                else {
                    $result = wp_insert_term(
                        $args['name'],
                        $operation->operation_type,
                        $args
                    );
                }

                if (is_wp_error($result)) {
                    return $this->fail($operation, $result->get_error_message());
                }

                return $this->success($operation, $result['term_id']);
            }

            return $this->update_term_meta($operation);
        }

        /**
         * Mark operation as successful and update temp ID.
         *
         * @param Operation $operation
         * @param int $objectID
         * @return bool Always true.
         */
        private function success(Operation $operation, int $objectID = 0): bool
        {
            $this->repository->update($operation->id, [
                'operation_status' => 'success'
            ]);

            if(!$objectID){
                return true;
            }

            if (empty($operation->operation_temp)) {
                return false;
            }

            return $this->repository->update_by_temp(
                $operation->operation_temp,
                [
                    'operation_object_id'  => $objectID,
                ]
            );
        }

        /**
         * Mark operation as failed and store error message.
         *
         * @param Operation $operation
         * @param string $message
         * @return bool Always false.
         */
        private function fail(Operation $operation, string $message): bool
        {
            $this->repository->update($operation->id, [
                'operation_status' => 'error',
                'operation_comment' => $message
            ]);

            return false;
        }
    }

endif;
