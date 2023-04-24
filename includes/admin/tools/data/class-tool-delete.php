<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Admin\add_oes_notice_after_refresh;

if (!class_exists('Delete')) :

    /**
     * Class Delete
     *
     * Export options to json file.
     */
    class Delete extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
            $this->postbox['name'] = 'Delete';
        }


        //Overwrite
        function html(): void
        {
            /* get all post types */
            $choices = [];
            $postTypes = get_post_types(['public' => true], 'objects');
            if ($postTypes)
                foreach ($postTypes as $postType)
                    if (!in_array($postType->name, ['post', 'attachment']))
                        $choices[$postType->name] = '<i>' . __('Post Type: ', 'oes') . '</i>' .
                            $postType->labels->menu_name;

            /* get all taxonomy */
            $choicesTaxonomies = [];
            $choicesTaxonomiesRelations = [];
            $taxonomies = get_taxonomies(['public' => true], 'objects');
            if ($taxonomies)
                foreach ($taxonomies as $taxonomy)
                    if (!in_array($taxonomy->name, ['post_tag', 'category', 'post_format'])) {
                        $choicesTaxonomies[$taxonomy->name] = '<i>' .
                            __('Taxonomy: ', 'oes') . '</i>' . $taxonomy->label;

                        /* add taxonomy - post relations */
                        $choicesTaxonomiesRelations['pt_' . $taxonomy->name] = '<i>' .
                            __('Taxonomy Relations: ', 'oes') . '</i>' . $taxonomy->label;
                    }

            ?>
            <div id="tools">
                <div>
                    <p><?php _e('Select the post type or taxonomy that you want to delete. All posts or terms of ' .
                    'the chosen post type or taxonomy will be deleted. If you check the checkbox' .
                    '"<strong>Force Delete</strong>" the posts or pages will be deleted permanently instead of being ' .
                    'moved to trash.', 'oes'); ?></p>
                    <p><strong><?php _e('Select Entities', 'oes'); ?></strong></p>
                    <div class="oes-tools-checkboxes">
                        <div class="oes-tools-checkboxes-wrapper oes-toggle-checkbox">
                            <div class="oes-tools-checkbox-single">
                                <span><?php _e('Select All', 'oes') ?></span>
                                <input type="checkbox" onClick="oesToolsDeleteToggleAll(this)" id="delete_all_post_types"/>
                                <label  class="oes-toggle-label" for="delete_all_post_types"></label>
                            </div>
                            <hr><?php

                            /* display radio boxes to select from all custom post types */
                            foreach ($choices as $postTypeName => $postTypeLabel) :?>
                                <div class="oes-tools-checkbox-single" id="oes-delete-posts">
                                <span><?php echo $postTypeLabel; ?></span>
                                <input type="checkbox" id="delete_<?php echo $postTypeName; ?>"
                                       name="post_types_delete[]"
                                       value="delete_<?php echo $postTypeName; ?>">
                                <label class="oes-toggle-label" for="delete_<?php echo $postTypeName; ?>"></label>
                                </div><?php
                            endforeach; ?>
                            <hr><?php

                            /* display radio boxes to select from all custom taxonomy and taxonomies */
                            foreach ($choicesTaxonomies as $postTypeName => $postTypeLabel) :?>
                                <div class="oes-tools-checkbox-single" id="oes-delete-posts">
                                <span><?php echo $postTypeLabel; ?></span>
                                <input type="checkbox" id="delete_<?php echo $postTypeName; ?>"
                                       name="post_types_delete[]"
                                       value="delete_<?php echo $postTypeName; ?>">
                                <label class="oes-toggle-label" for="delete_<?php echo $postTypeName; ?>"></label>
                                </div><?php
                            endforeach; ?>
                            <hr><?php

                            /* display radio boxes to select from all custom taxonomy and taxonomy relations */
                            foreach ($choicesTaxonomiesRelations as $postTypeName => $postTypeLabel) :?>
                                <div class="oes-tools-checkbox-single" id="oes-delete-posts">
                                <span><?php echo $postTypeLabel; ?></span>
                                <input type="checkbox" id="delete_<?php echo $postTypeName; ?>"
                                       name="post_types_delete[]"
                                       value="delete_<?php echo $postTypeName; ?>">
                                <label class="oes-toggle-label" for="delete_<?php echo $postTypeName; ?>"></label>
                                </div><?php
                            endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="oes-tools-checkboxes-wrapper oes-toggle-checkbox">
                    <div class="oes-tools-checkbox-single">
                        <p><strong><?php _e('Force Delete', 'oes'); ?></strong></p>
                        <span><?php _e('If deleting a post or page, force delete it permanently (instead of moving  
                        the post to trash).', 'oes'); ?></span>
                        <input type="checkbox" id="force_delete_post" name="force_delete_post">
                        <label class="oes-toggle-label" for="force_delete_post"></label>
                    </div>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Delete Entities', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //Overwrite
        function admin_post_tool_action(): void
        {
            /* get post types*/
            $postTypes = $_POST['post_types_delete'];

            /* skip if no post type selected */
            if (!$postTypes) {
                add_oes_notice_after_refresh(__('No post types selected.', 'oes'), 'error');
            }
            else{

                /* check if post type */
                $returnMessage = '';
                $errorMessage = '';
                foreach ($postTypes as $postTypeKey) {

                    $count = 0;
                    $countErrors = 0;
                    $isTerm = false;

                    /* get post type from form (post type has the pattern 'delete_'[post_type]) */
                    $postType = substr($postTypeKey, 7);

                    if (post_type_exists($postType)) {

                        /* get all posts */
                        $posts = get_posts([
                            'post_type' => [$postType],
                            'post_status' => get_post_types(''),
                            'numberposts' => -1
                        ]);

                        /* delete all posts */
                        foreach ($posts as $post) {
                            $success = $_POST['force_delete_post'] ?
                                wp_delete_post($post->ID) :
                                wp_trash_post($post->ID);
                            if ($success) $count++;
                            else $countErrors++;
                        }
                    } /* check if taxonomy */
                    elseif (taxonomy_exists($postType)) {

                        $isTerm = true;

                        /* get all tags */
                        $terms = get_terms([
                            'taxonomy' => $postType,
                            'hide_empty' => false
                        ]);

                        /* delete all posts */
                        foreach ($terms as $term) {
                            $success = wp_delete_term($term->term_id, $postType);
                            if (!is_wp_error($success) && $success) $count++;
                            else $countErrors++;
                        }
                    }

                    if ($count) $returnMessage .=
                        sprintf(__('%s %s deleted.%s<br>', 'oes'),
                            $count,
                            $isTerm ? 'terms' : 'posts',
                            !empty($countErrors) ? $countErrors . __(' error(s) occurred.', 'oes') : ''
                        );

                    else $errorMessage .= __('No posts or terms deleted.', 'oes') . '<br>';
                }

                if ($returnMessage) add_oes_notice_after_refresh($returnMessage);
                elseif($errorMessage) add_oes_notice_after_refresh($errorMessage, 'error');
                else add_oes_notice_after_refresh(__('No posts or terms deleted.', 'oes'), 'error');
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Delete', 'delete-posts');

endif;