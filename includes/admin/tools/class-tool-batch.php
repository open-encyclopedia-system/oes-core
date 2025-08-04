<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Query;

if (!class_exists('Batch')) :

    /**
     * Class Batch
     *
     * Tool for batch updating post.
     */
    class Batch extends Tool
    {
        /** @inheritdoc */
        protected string $ajax_action = 'oes_tool_batch_process';

        /** @inheritdoc */
        protected string $menu_slug = 'oes_tools_batch';

        /** @inheritdoc */
        function display(): void
        {
            ?>
            <div class="wrap" id="tools">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="oes_batch_object_type"><?php _e('Post Type or Taxonomy', 'oes'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="oes_batch_object_type" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="oes_batch_function"><?php _e('Function', 'oes'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="oes_batch_function" class="regular-text" required>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <button id="oes-tool-batch-start-processing" class="button button-primary"><?php
                    _e('Start Processing', 'oes'); ?>
                </button>
                <div id="oes-tool-batch-progress" style="margin-top:1em;"></div>
            </div>
            <script>
                (function ($) {
                    let offset = 0;
                    const batchSize = 20;

                    $('#oes-tool-batch-start-processing').on('click', function () {

                        const objectType = $('#oes_batch_object_type').val().trim();
                        const objectFunction = $('#oes_batch_function').val().trim();

                        if (!objectType || !objectFunction) {
                            alert('Please enter a post type or taxonomy and function before starting the process.');
                            return; // Stop if empty
                        }


                        $('#oes-tool-batch-progress').html('Starting...');
                        processBatch(offset);

                        function processBatch(currentOffset) {
                            $.post(ajaxurl, {
                                action: 'oes_tool_batch_process',
                                offset: currentOffset,
                                batch_size: batchSize,
                                object_type: objectType,
                                object_function: objectFunction,
                                _ajax_nonce: '<?php echo wp_create_nonce('oes_tool_batch_process_nonce'); ?>'
                            }, function (response) {
                                if (response.success) {
                                    $('#oes-tool-batch-progress').html(response.data.message);
                                    if (!response.data.finished) {
                                        processBatch(currentOffset + batchSize);
                                    }
                                } else {
                                    $('#oes-tool-batch-progress').html('Error: ' + response.data);
                                }
                            });
                        }
                    });
                })(jQuery);
            </script>
            <?php
        }

        /** @inheritdoc */
        public function handle_ajax(): void
        {
            check_ajax_referer($this->ajax_action . '_nonce');

            $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
            $batchSize = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 20;
            $objectType = isset($_POST['object_type']) ? sanitize_text_field($_POST['object_type']) : '';
            $function = isset($_POST['object_function']) ? stripslashes(sanitize_text_field($_POST['object_function'])) : '';

            global $oes;
            if (!isset($oes->post_types[$objectType]) && !isset($oes->taxonomies[$objectType])){
                wp_send_json_error(__('Invalid post type.', 'oes'));
            }

            if(isset($oes->post_types[$objectType])) {

                $query = new WP_Query([
                    'post_type' => $objectType,
                    'post_status' => 'publish',
                    'posts_per_page' => $batchSize,
                    'offset' => $offset,
                    'fields' => 'all',
                ]);

                if (!$query->have_posts()) {
                    wp_send_json_success([
                        'message' => __('Processing complete.', 'oes'),
                        'finished' => true
                    ]);
                }

                foreach ($query->posts as $singlePost) {
                    if (function_exists($function)) {
                        call_user_func($function, $singlePost);
                    } else {
                        wp_send_json_error(__('Invalid function.', 'oes'));
                    }
                }
            }
            elseif(isset($oes->taxonomies[$objectType])){

                $terms = get_terms([
                    'taxonomy' => $objectType,
                    'hide_empty' => false,
                    'offset' => $offset,
                    'number' => $batchSize,
                    'fields' => 'all',
                ]);

                if (is_wp_error($terms) || empty($terms)) {
                    wp_send_json_success([
                        'message' => __('Processing complete.', 'oes'),
                        'finished' => true
                    ]);
                }

                foreach ($terms as $term) {
                    if (function_exists($function)) {
                        call_user_func($function, $term);
                    } else {
                        wp_send_json_error(__('Invalid function.', 'oes'));
                    }
                }
            }

            wp_send_json_success([
                'message' => "Processed entries {$offset} to " . ($offset + $batchSize - 1) . "...",
                'finished' => false
            ]);
        }
    }

// initialize
    register_tool('\OES\Admin\Tools\Batch', 'batch');

endif;