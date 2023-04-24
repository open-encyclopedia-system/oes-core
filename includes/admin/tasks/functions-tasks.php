<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Render tasks for dashboard.
 * @return void
 */
function oes_dashboard_tasks_html(): void
{
    $taskTable = '';
    $tasks = get_posts([
        'post_type' => 'oes_task',
        'numberposts' => -1
    ]);
    if (!empty($tasks))
        foreach ($tasks as $task) {

            /* get priority */
            $priority = OES\ACF\get_select_field_value('field_oes_task__priority', $task->ID) ?? '';

            /* get status */
            $status = OES\ACF\get_select_field_value('field_oes_task__status', $task->ID) ?? '';

            /* skip closed task */
            if ($status === 'Closed') continue;

            /* create table row */
            $taskTable .= '<tr><td><a href="' .
                get_edit_post_link($task->ID) . '"><span class="dashicons dashicons-edit"></span></a>' . '</td><td>' .
                $task->post_title . '</td><td>' . $priority . '</td><td>' . $status . '</td></tr>';
        }

    /* set label as header */
    $postType = get_post_type_object('oes_task');
    $button = oes_get_html_anchor($postType->labels->add_new,
        'post-new.php?post_type=' . $postType->name,
        false,
        'add-new-' . $postType->name . ' page-title-action'
    );
    $buttonAll = oes_get_html_anchor('All Tasks',
        'edit.php?post_type=' . $postType->name,
        false,
        'page-title-action'
    );

    if (!empty($taskTable)):?>
        <div class="oes-dashboard-postbox-container">
        <p>Display tasks (exclude closed tasks).</p>
        <div class="oes-dashboard-button-group">
            <span><?php echo $button; ?></span>
            <span><?php echo $buttonAll; ?></span>
        </div>
        <table class="wp-list-table widefat striped table-view-list">
            <thead>
            <tr>
                <th></th>
                <th>Title</th>
                <th>Priority</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody><?php echo $taskTable; ?>
            </tbody>
        </table>
        </div><?php
    endif;
}