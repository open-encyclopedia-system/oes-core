<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_all_object_fields;
use function OES\ACF\oes_get_field;
use function OES\Admin\add_oes_notice_after_refresh;

if (!class_exists('Update')) :

    /**
     * Class Delete
     *
     * Export options to json file.
     */
    class Update extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
            $this->postbox['name'] = 'Update';
        }


        //Implement parent
        function html(): void
        {
            /* get all post types */
            $choices = [];
            $postTypes = get_post_types(['public' => true], 'objects');
            if ($postTypes)
                foreach ($postTypes as $postType)
                    if ($postType->name != 'post')
                        $choices[$postType->name] = $postType->labels->menu_name;

            ?>
            <div id="tools">
                <div>
                    <p><?php _e('Select the post type and field type you would like to bulk update.',
                            'oes'); ?></p>
                    <p><strong><?php _e('Select Action', 'oes'); ?></strong></p><?php
                    echo oes_html_get_form_element(
                            'checkbox',
                            'update_relationship',
                            'update_relationship',
                            false,
                            ['label' => 'Relationship Fields']) . '<br>';
                    echo oes_html_get_form_element(
                            'checkbox',
                            'update_terms',
                            'update_terms',
                            false,
                            ['label' => 'Taxonomy Fields']) . '<br>';
                    echo oes_html_get_form_element(
                        'checkbox',
                        'update_repeater',
                        'update_repeater',
                        false,
                        ['label' => 'Repeater Fields (ACF Pro)']) . '<br>';
                    echo oes_html_get_form_element(
                            'checkbox',
                            'update_terms_force',
                            'update_terms_force',
                            false,
                            ['label' => 'Update connected Terms from Taxonomy fields']);

                    ?>
                    <p><strong><?php _e('Select Post Type', 'oes'); ?></strong></p>
                    <label for="post_type_update"></label><select name="post_type_update" id="post_type_update"><?php

                        /* display radio boxes to select from all custom post types */
                        foreach ($choices as $postTypeName => $postTypeLabel) :?>
                            <option value="<?php echo $postTypeName; ?>"><?php echo $postTypeLabel; ?></option><?php
                        endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Update Posts', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //Overwrite
        //@oesDevelopment Error handling and feedback.
        function admin_post_tool_action(): void
        {
            /* get post types*/
            $postType = $_POST['post_type_update'];

            /* skip if no post type selected */
            if (!$postType) {
                add_oes_notice_after_refresh(__('No post type selected.', 'oes'), 'error');
            } else {

                /* check for field types */
                $fieldTypes = [];
                if (isset($_POST['update_relationship']) && $_POST['update_relationship'] == 'on')
                    $fieldTypes = ['relationship', 'post_object'];
                if ((isset($_POST['update_terms']) && $_POST['update_terms'] == 'on') ||
                    (isset($_POST['update_terms_force']) && $_POST['update_terms_force'] == 'on')) {
                    $fieldTypes[] = 'taxonomy';
                    $fieldTypes[] = 'repeater';
                }
                if (isset($_POST['update_repeater']) && $_POST['update_repeater'] == 'on')
                    $fieldTypes[] = 'repeater';

                if (!empty($fieldTypes)) {

                    /* get all posts and update relationship fields */
                    $posts = oes_get_wp_query_posts(['post_type' => $postType, 'fields' => 'ids']);
                    $fields = get_all_object_fields($postType, $fieldTypes);

                    if (!empty($posts) && !empty($fields))
                        foreach ($posts as $postID)
                            foreach ($fields as $fieldKey => $field) {
                                $value = oes_get_field($fieldKey, $postID);

                                if (isset($_POST['update_terms_force']) && $_POST['update_terms_force'] == 'on') {

                                    if ($field['type'] == 'taxonomy' &&
                                        $field['save_terms'] &&
                                        !empty($field['taxonomy'])) {
                                        if ($value)
                                            wp_set_object_terms($postID, $value, $field['taxonomy'], true);
                                        //@oesDevelopment Improve for delete.
                                    } elseif ($field['type'] == 'repeater') {
                                        foreach ($field['sub_fields'] as $subFieldID => $subField)
                                            if ($subField['type'] == 'taxonomy' &&
                                                $subField['save_terms'] &&
                                                !empty($subField['taxonomy']) &&
                                                is_array($value)) {
                                                foreach ($value as $rowValue) {
                                                    $subFieldValue = $rowValue[$subField['key']];
                                                    if ($subFieldValue)
                                                        wp_set_object_terms($postID, $subFieldValue, $subField['taxonomy'], true);
                                                    //@oesDevelopment Improve for delete.
                                                }
                                            }
                                    }
                                } else {
                                    update_field($fieldKey, $value, $postID);
                                }
                            }
                }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Update', 'update-posts');

endif;