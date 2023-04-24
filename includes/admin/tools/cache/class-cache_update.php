<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Admin\add_oes_notice_after_refresh;
use function oes_update_archive_cache;

if (!class_exists('Cache_Update')) :

    /**
     * Class Cache_Update
     *
     * Update archive cache.
     */
    class Cache_Update extends Tool
    {

        //Overwrite
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }


        //Implement parent
        function html(): void
        {
            /* get all post types and taxonomies */
            global $oes;
            $choices = ['all' => __('All', 'oes')];
            foreach ($oes->post_types as $singlePostTypeKey => $singlePostType)
                $choices[$singlePostTypeKey] = $singlePostType['label'];
            foreach ($oes->taxonomies as $singleTaxonomyKey => $singleTaxonomy)
                $choices[$singleTaxonomyKey] = $singleTaxonomy['label'];
            $choices['index'] = __('Index', 'oes');

            ?>
            <div id="tools">
                <div>
                    <p><?php _e('Select the object for which you would like to update the cache.',
                            'oes'); ?></p>
                    <label for="object_cache"></label><select name="object_cache" id="object_cache"><?php

                        /* display radio boxes to select from all custom post types */
                        foreach ($choices as $objectName => $objectLabel) :?>
                            <option value="<?php echo $objectName; ?>"><?php echo $objectLabel; ?></option><?php
                        endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="oes-settings-submit">
                <p class="submit"><?php
                    submit_button(__('Update Cache', 'oes')); ?>
                </p>
            </div>
            <?php
        }


        //Overwrite
        function admin_post_tool_action(): void
        {
            /* get object */
            $object = $_POST['object_cache'];

            /* skip if no object selected */
            if (!$object) add_oes_notice_after_refresh(__('No object selected.', 'oes'), 'error');
            else oes_update_archive_cache($object);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Cache_Update', 'cache-update');

endif;