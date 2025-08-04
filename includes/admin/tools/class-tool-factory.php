<?php

/**
 * @file
 * @todoReview Review for 2.4.x
 */

namespace OES\Admin\Tools;

use function OES\Admin\get_admin_user_only_message;
use function OES\Factory\get_factory_posts;
use function OES\Factory\import_model_from_factory;
use function OES\Factory\import_model_to_factory;
use function OES\Factory\reset_factory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Factory')) :

    /**
     * Class Factory
     *
     * Implement the tool for data Factory import and export.
     */
    class Factory extends Tool
    {

        private bool $factory_mode = false;


        /** @inheritdoc */
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
            $this->factory_mode = get_option('oes_admin-factory_mode');
        }


        //** @inheritdoc */
        function html(): void
        {
            $adminOnly = get_admin_user_only_message();
            if (!empty($adminOnly)): echo $adminOnly;
            else:

                /* check if factory mode but factory items missing.*/
                if ($this->factory_mode):
                    $factoryPosts = get_factory_posts(false);
                    if (sizeof($factoryPosts) < 1):?>
                        <div class="notice notice-info">
                        <p><?php echo __('No factory items exist.', 'oes'); ?></p>
                        </div><?php
                    endif;
                endif;

                ?>
                <div class="oes-configuration-header">
                <p><?php
                    _e('You can load the data model as factory and edit post types, taxonomies and ' .
                        'their field groups. You can then choose to use the modified data as data model ' .
                        'instead of the json-File.', 'oes'); ?></p>
                <p><?php
                    _e('Importing the OES data model to the factory will immediately replace the data model.' .
                        ' Any changes you make to the factory ' .
                        'model will be present in the OES data model and its representation in the OES theme. ' .
                        'If you want to go back to the OES data model you can discard the data model by ' .
                        'resetting the factory. ' .
                        'If you want to use the factory data model instead of the OES data model you should ' .
                        'import the factory model.', 'oes'); ?></p>
                <p><?php
                    printf(__('If you modify data objects that affect the permalink structure (such as creating ' .
                        'or renaming a post type), you should save the new permalink structure %shere%s '.
                        'after leaving the factory mode (even if you are not changing any settings).', 'oes'),
                        '<a href="' . admin_url('options-permalink.php') . '">',
                        '</a>'
                    ); ?>
                </p>
                </div><?php

                if (!$this->factory_mode):?>
                    <span>
                    <input type="submit" name="import_to_factory" id="import_to_factory" class="button"
                           value="Import OES Data Model to Factory">
                </span><?php

                else:?>
                    <span>
                    <input type="submit" name="import_factory" id="import_factory" class="button"
                           value="Import Factory to OES Data Model" onClick="return confirm('<?php
                    _e('You are about to overwrite the current configurations. ' .
                        'It is recommended that you export the configuration first. Do you want to proceed?',
                        'oes');
                    ?>');">
                </span>
                    <div class="oes-configuration-header">
                        <p><?php
                            _e('or', 'oes'); ?>
                        </p>
                    </div>
                    <span>
                    <input type="submit" name="reset_factory" id="reset_factory" class="button"
                           value="Reset Factory" onClick="return confirm('<?php
                    _e('You are about to delete the factory. Do you want to proceed?', 'oes');
                    ?>');">
                    </span><?php
                endif;
            endif;
        }


        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            if (isset($_POST['import_factory'])) import_model_from_factory();
            elseif (isset($_POST['import_to_factory'])) import_model_to_factory();
            elseif (isset($_POST['reset_factory'])) reset_factory();
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Factory', 'factory');

endif;