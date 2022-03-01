<?php

use function OES\ACF\get_select_field_value;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* Register the OES post type "Task". */
add_action('init', 'oes_register_tasks');


/**
 * Register the OES post type "Task"
 */
function oes_register_tasks()
{

    /* check if post type 'oes_task' is already registered */
    if (!post_type_exists('oes_task')) {

        register_taxonomy('t_oes_task_components', ['oes_task'], [
            'labels' => [
                'name' => __('Components', 'oes'),
                'singular_name' => __('Component', 'oes'),
                'search_items' => __('Search Components', 'oes'),
                'all_items' => __('All Components', 'oes'),
                'parent_item' => __('Parent Component', 'oes'),
                'parent_item_colon' => __('Parent Component:', 'oes'),
                'edit_item' => __('Edit Component', 'oes'),
                'update_item' => __('Update Component', 'oes'),
                'add_new_item' => __('Add New Component', 'oes'),
                'new_item_name' => __('New Component Name', 'oes'),
                'menu_name' => __('Components', 'oes'),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true
        ]);

        register_post_type(
            'oes_task',
            [
                'label' => 'Tasks',
                'description' => 'internal use only',
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_icon' => plugins_url(OES()->basename . '/assets/images/oes_cubic_18x18_second.png'),
                'hierarchical' => true,
                'show_in_nav_menus' => false,
                'menu_position' => 57,
                'supports' => ['title', 'page-attributes', 'editor', 'comments'],
                'taxonomies' => ['t_oes_task_components']
            ]
        );

        /* register field group */
        if (function_exists('acf_add_local_field_group'))
            acf_add_local_field_group([
                'key' => 'group_oes_tasks',
                'title' => 'Task',
                'fields' => [
                    [
                        'key' => 'field_oes_task__status',
                        'label' => 'Status',
                        'name' => 'status',
                        'type' => 'select',
                        'instructions' => 'Set the task status',
                        'choices' => [
                            'open' => 'Open',
                            'progress' => 'In Progress',
                            'review' => 'In Review',
                            'closed' => 'Closed'
                        ],
                        'default_value' => 'open'
                    ],
                    [
                        'key' => 'field_oes_task__priority',
                        'label' => 'Priority',
                        'name' => 'priority',
                        'type' => 'select',
                        'instructions' => 'Set the task priority',
                        'choices' => [
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                            'critical' => 'Critical'
                        ],
                        'default_value' => 'low'
                    ],
                    [
                        'key' => 'field_oes_task__assigned_to',
                        'label' => 'Assigned To',
                        'name' => 'assigned_to',
                        'type' => 'user',
                        'instructions' => 'Choose an assignee',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'role' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                        'return_format' => 'array',
                    ],
                    [
                        'key' => 'field_oes_task__deadline',
                        'label' => 'Deadline',
                        'name' => 'deadline',
                        'type' => 'date_picker',
                        'instructions' => 'Set a deadline',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'display_format' => 'd/m/Y',
                        'return_format' => 'd/m/Y',
                        'first_day' => 1,
                    ],
                    [
                        'key' => 'field_oes_task__comment',
                        'label' => 'Comment',
                        'name' => 'comment',
                        'type' => 'textarea',
                        'instructions' => 'Add internal comment',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'default_value' => '',
                        'placeholder' => '',
                        'maxlength' => '',
                        'rows' => '',
                        'new_lines' => '',
                    ],
                    [
                        'key' => 'field_oes_task__date',
                        'label' => 'Date',
                        'name' => 'date',
                        'type' => 'date_picker',
                        'instructions' => 'Set the creation date. (Will be set as current date if left empty).',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'display_format' => 'd/m/Y',
                        'return_format' => 'd/m/Y',
                        'first_day' => 1,
                    ]
                ],
                'location' => [[[
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'oes_task'
                ]]],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ]);
    }
}


/* Hide permalink for this post type. */
add_filter('get_sample_permalink_html', function (string $return, int $post_id) {
    $post = get_post($post_id);
    if ($post->post_type === 'oes_task') return '';
    return $return;
}, 10, 2);


/**
 * Render tasks for dashboard.
 */
function oes_dashboard_tasks_html()
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


/* set current date for date field if left empty. */
add_filter('acf/update_value/key=field_oes_task__date', function ($value, $post_id, $field, $original) {
    return empty($value) ? date('Ymd') : $value;
}, 10, 4);


/* prepare filter ----------------------------------------------------------------------------------------------------*/
add_action('oes/datamodel_registered', 'oes_task_initialize_filter');


/**
 * Prepare filter for column display, sorting and filtering.
 *
 * TODO move to columns.php?
 */
function oes_task_initialize_filter()
{
    add_filter('manage_oes_task_posts_columns', 'oes_task_add_post_column');
    add_action('manage_oes_task_posts_custom_column', 'oes_task_display_post_column_value', 10, 2);
    //add_filter('manage_edit-oes_task_sortable_columns', 'oes_task_make_columns_sortable');
}


/**
 * Add extra columns to the list display of posts in the wp admin area.
 *
 * @param array $columns The columns for the post type that is being displayed.
 * @return array Returns the columns to be displayed.
 */
function oes_task_add_post_column(array $columns): array
{
    $columns['status'] = 'Status';
    $columns['assigned_to'] = 'Assigned To';
    return $columns;
}


/**
 * Display values for column in the list display of post types in the wp admin area.
 *
 * @param string $column The column name.
 * @param string $post_id The post ID.
 */
function oes_task_display_post_column_value(string $column, string $post_id)
{

    switch($column){

        default:

            /* acf field */
            if ($value = oes_get_field($column, $post_id)) {

                /* check type */
                $fieldObject = oes_get_field_object($column);
                if ($fieldObject && !empty($value)) {

                    switch ($fieldObject['type']) {

                        case 'radio' :
                        case 'select' :
                            $newValue = get_select_field_value($column, $post_id);
                            break;

                        case 'user' :
                            $newValue = $fieldObject['value']['nickname'] ?? '';
                            break;

                        case 'post_object':
                            $postObject = get_post($value);
                            $newValue = $postObject ?
                                sprintf('<span class="oes-column-post">' .
                                    '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                    '</a></span>',
                                    $postObject->ID,
                                    $postObject->post_title) :
                                $value;
                            break;

                        case 'relationship' :
                            if (is_array($value)) {
                                $newValueTemp = [];
                                foreach ($value as $post) {
                                    if (get_post($post)) {
                                        $postID = ($fieldObject['return_format'] == 'object') ? $post->ID : $post;
                                        $newValueTemp[] = sprintf('<span class="oes-column-post">' .
                                            '<a href="post.php?post=%s&action=edit" class="oes-admin-link">%s' .
                                            '</a></span>',
                                            $postID,
                                            get_the_title($postID)
                                        );
                                    }
                                }
                                $newValue = implode('', $newValueTemp);
                            } else {
                                $newValue = get_post($value) ? oes_get_display_title($value) : $value;
                            }
                            break;

                        case 'taxonomy' :
                            if (is_array($value)) {
                                $newValueTemp = [];
                                foreach ($value as $valueID) {
                                    $term = get_term($valueID);
                                    if ($term) $newValueTemp[] = sprintf('<span class="oes-column-taxonomy">' .
                                        '<a href="edit.php?post_type=%s&%s=%s">%s</a></span>',
                                        $_GET['post_type'] ?? '',
                                        $column,
                                        $valueID,
                                        $term->name
                                    );
                                }
                                $newValue = implode(', ', $newValueTemp);
                            } else {
                                $newValue = get_term($value)->name ?? $value;
                            }
                            break;
                    }
                }

                echo $newValue ?? $value;
            } /* taxonomy */
            elseif (get_taxonomy($column)) {

                $terms = get_the_terms($post_id, $column);

                $termStringArray = [];
                if ($terms) {
                    foreach ($terms as $term)
                        $termStringArray[] = sprintf(
                            '<span class="oes-column-tag"><a href="edit.php?post_type=%s&%s=%s">%s</a></span>',
                            $_GET['post_type'] ?? '',
                            $column,
                            $term->slug,
                            $term->name);
                }
                if (!empty($termStringArray)) echo implode('', $termStringArray);
            }
            break;
    }
}


/**
 * Make columns sortable in the list display of post types in the wp admin area.
 *
 * @param array $columns The columns which are to be displayed.
 * @return array Return columns.
 */
function oes_task_make_columns_sortable(array $columns): array
{
    $columns['status'] = 'status';
    $columns['assigned_to'] = 'assigned_to';
    return $columns;
}