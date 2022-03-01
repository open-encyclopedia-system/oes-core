<?php

namespace OES\Admin\Tools;


use function OES\ACF\get_all_object_fields;
use function OES\ACF\oes_get_field;
use function OES\Admin\add_oes_notice_after_refresh;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Delete')) :

    /**
     * Class Delete
     *
     * Export options to json file.
     */
    class Update extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = [])
        {
            $this->form_action = admin_url('admin-post.php');
            $this->postbox['name'] = 'Update';
        }


        //Implement parent
        function html()
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
                    <p><?php _e('Select the post type you would like to bulk update all relationship fields.',
                            'oes'); ?></p>
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
        function admin_post_tool_action()
        {
            /* get post types*/
            $postType = $_POST['post_type_update'];

            /* skip if no post type selected */
            if (!$postType) {
                add_oes_notice_after_refresh(__('No post type selected.', 'oes'), 'error');
            } else {

                /* get all posts and update relationship fields */
                $posts = oes_get_wp_query_posts(['post_type' => $postType]);
                $fields = get_all_object_fields($postType, ['relationship', 'post_object']);

                if (!empty($posts))
                    foreach ($posts as $post)
                        foreach ($fields as $fieldKey => $field) {
                            $value = oes_get_field($fieldKey, $post->ID);
                            update_field($fieldKey, $value, $post->ID);
                        }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Update', 'update-posts');

endif;