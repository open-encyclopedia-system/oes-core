<?php

namespace OES\Admin\Operations;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Display_Builder')) :

    /**
     * Class Display_Builder
     *
     * Transforms an Operation entity into a structured array suitable for UI rendering.
     */
    class Display_Builder
    {

        /**
         * The considered parameter for post and term display.
         */
        private const ARGS_LABELS = [
            'post_title'   => 'Post Title',
            'post_status'  => 'Post Status',
            'post_author'  => 'Post Author',
            'post_content' => 'Post Content',
            'post_excerpt' => 'Post Excerpt',
            'post_name'    => 'Post Name (Slug)',
            'post_parent'  => 'Post Parent',
            'term_id'      => 'Term ID',
            'name'         => 'Term Name',
            'term'         => 'Term Name',
            'taxonomy'     => 'Taxonomy',
            'slug'         => 'Term Slug',
            'description'  => 'Term Description',
            'parent'       => 'Term Parent',
        ];

        /**
         * Build title display data for a single operation.
         *
         * @param Operation $operation
         * @return string
         */
        public function build_title(Operation $operation): string
        {
            $objectID = $operation->operation_object_id;
            $objectType = $operation->operation_type;
            $objectObject = $operation->operation_object;

            if($objectID){

                if($objectObject == 'post'){
                    if(get_post($objectID)){
                        return sprintf('<strong><a href="%s">%s</a></strong>',
                            get_edit_post_link($objectID),
                            get_the_title($objectID));
                    }
                }
                elseif($objectObject == 'term'){
                    if($term = get_term($objectID, $objectType)){
                        return sprintf('<strong><a href="%s">%s</a></strong>',
                            get_edit_term_link($objectID),
                            $term->name
                        );
                    }
                }
            }
            else {
                if ($operation->is_args()) {
                    $args = $operation->get_args();

                    $title = '';
                    $objectLabel = false;

                    if($objectObject == 'post') {
                        $title = $args['post_title'] ?? false;

                        if($title) {
                            $objectLabel = get_post_type_object($objectType)->labels->singular_name;
                        }
                    }
                    elseif($objectObject == 'term') {
                        $title = $args['term_name'] ?? false;

                        if($title){
                            $objectLabel = get_taxonomy($objectType)->labels->singular_name;
                        }
                    }

                    if($title){
                        return sprintf('%s %s: <strong>%s</strong>',
                            __('New', 'oes'),
                            $objectLabel ?? __('Unknown Object', 'oes'),
                            $title
                        );
                    }
                }
            }

            return '';
        }

        /**
         * Build display data for a single operation.
         *
         * @param Operation $operation
         * @return string
         */
        public function build_summary(Operation $operation): string
        {
            $info = [];

            if ($operation->is_args()) {
                foreach ($operation->get_args() ?? [] as $key => $value) {

                    $newValue = (string) $value;
                    $oldValue = $this->get_old_value($operation, $key);

                    if ($newValue === $oldValue) {
                        continue;
                    }

                    $label = $this->resolve_arg_label($key);
                    if($label){
                        $info[] = $this->format_row($label, $newValue);
                    }
                }

                return implode('</br>', $info);
            }

            // Non-args case
            $newValue = (string) $operation->operation_value;
            $oldValue = $this->get_old_meta($operation);

            if ($newValue === $oldValue) {
                return '';
            }

            $label = $this->resolve_meta_label($operation->operation_key);

            if (!$label) {
                return '';
            }

            return $this->format_row($label, $newValue);
        }

        /**
         * Build success message after importing of a single operation.
         *
         * @param Operation $operation
         * @return string
         */
        public function build_success_message(Operation $operation, array &$updatedObjects): string
        {
            $objectID = $operation->operation_object_id;

            if(!$objectID){
                return '';
            }
            elseif(!in_array($objectID, $updatedObjects)) {
                $updatedObjects[] = $objectID;

                $object = $operation->operation_object;

                if($object == 'post' || $object == 'meta'){

                    $post = get_post($objectID);
                    if($post){
                        return sprintf('%s "<strong><a href="%s" target="_blank">%s</a></strong>" %s',
                        __('Post', 'oes'),
                        get_edit_post_link($objectID),
                        $post->post_title,
                            __('updated.', 'oes')
                        );
                    }
                }
                elseif($object == 'term' || $object == 'term_meta'){

                    $term = get_term($objectID, $operation->operation_type);
                    if($term){
                        return sprintf('%s "<strong><a href="%s" target="_blank">%s</a></strong>" %s',
                                __('Term', 'oes'),
                                 get_edit_term_link($objectID),
                                $term->name,
                                __('updated.', 'oes')
                            );
                    }
                }
            }

            return '';
        }

        /**
         * Resolve post args and term args keys.
         *
         * @param string $key
         * @return string
         */
        private function resolve_arg_label(string $key): string
        {
            return self::ARGS_LABELS[$key] ?? '';
        }

        /**
         * Resolve field label.
         *
         * @param string $key
         * @return string|null
         */
        private function resolve_meta_label(string $key): ?string
        {
            $fieldObject = get_field_object($key);
            return $fieldObject['label'] ?? $key;
        }

        /**
         * Format row for display.
         *
         * @param string $label
         * @param string $value
         * @return string
         */
        private function format_row(string $label, string $value): string
        {
            return sprintf('<strong>%s</strong>: <span>%s</span>', $label, $value);
        }

        /**
         * Build display data for a single operation.
         *
         * @param Operation $operation
         * @return array{
         *     general: array{
         *         author: string,
         *         date: string,
         *         status: string,
         *         comment: string
         *     },
         *     changes: array<int, array{
         *         field: string,
         *         new: string,
         *         old: mixed
         *     }>
         * }
         */
        public function build(Operation $operation): array
        {
            $data = [
                'general' => [
                    'id' => $operation->id,
                    'type' => $operation->operation_type,
                    'author' => get_the_author_meta(
                        'display_name',
                        $operation->operation_author
                    ),
                    'date' => date_i18n(
                        'd.m.Y H:i',
                        strtotime($operation->operation_date)
                    ),
                    'status' => $operation->operation_status,
                    'comment' => $operation->operation_comment
                ],
                'changes' => []
            ];

            if ($operation->is_args()) {
                foreach ($operation->get_args() ?? [] as $key => $value) {
                    $data['changes'][] = [
                        'field' => $key,
                        'new'   => (string) $value,
                        'old'   => $this->get_old_value($operation, $key)
                    ];
                }
            } else {
                $data['changes'][] = [
                    'field' => $operation->operation_key,
                    'new'   => (string) $operation->operation_value,
                    'old'   => $this->get_old_meta($operation)
                ];
            }

            return $data;
        }

        /**
         * Get the previous field value from a post or term object.
         *
         * @param Operation $operation
         * @param string $key
         * @return mixed|string
         */
        private function get_old_value(Operation $operation, string $key)
        {
            if (!$operation->operation_object_id) {
                return '';
            }

            if ($operation->operation_object === 'post') {
                $post = get_post($operation->operation_object_id);
                return $post->$key ?? '';
            }

            if ($operation->operation_object === 'term') {
                $term = get_term($operation->operation_object_id);
                return $term->$key ?? '';
            }

            return '';
        }

        /**
         * Get previous meta value from post or term.
         *
         * @param Operation $operation
         * @return mixed|string
         */
        private function get_old_meta(Operation $operation)
        {
            if (!$operation->operation_object_id) {
                return '';
            }

            if ($operation->operation_object === 'post') {
                return get_post_meta(
                    $operation->operation_object_id,
                    $operation->operation_key,
                    true
                );
            }

            if ($operation->operation_object === 'term') {
                return get_term_meta(
                    $operation->operation_object_id,
                    $operation->operation_key,
                    true
                );
            }

            return '';
        }
    }

endif;
