<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


if (!class_exists('Export_Datamodel')) :

    /**
     * Class Export_Datamodel
     *
     * Implement the config tool for writing configurations.
     */
    class Export_Datamodel extends Tool
    {

        //Overwrite parent
        function initialize_parameters(array $args = [])
        {
            $this->form_action = admin_url('admin-post.php');
        }


        ///Overwrite parent
        function html()
        {
            if (oes_user_is_oes_admin()):?>
                <div class="oes-configuration-header">
                    <h2><?php _e('Admin Tools', 'oes'); ?></h2>
                    <p><?php
                        _e('The datamodel configuration via the editorial layer (as in the tools above) ' .
                            'overwrites the plugin configurations. You can restore the plugin configurations via the ' .
                            'button below or export you current configurations.', 'oes'); ?>
                    </p>
                </div>
                <span class="oes-tooltip">
                    <input type="submit" name="reload_json" id="reload_json" class="button"
                           value="Reload from Plugin Config">
                    <span class="oes-tooltip-text"><?php
                        _e('Restore the OES objects from the file inside the OES Project Plugin.', 'oes');
                        ?></span>
                </span>
                <span class="oes-tooltip">
                <input type="submit" name="export" id="export" class="button" value="Export Config from DB">
                    <span class="oes-tooltip-text"><?php
                        _e('Export OES object to json file.', 'oes'); ?></span>
                </span><?php
            endif;
        }


        //Overwrite parent
        function admin_post_tool_action()
        {
            /* check action : "reload from plugin" the param will be parsed when executing the OES init hook. */

            /* check action : "export configs" */
            if (isset($_POST['export'])) {
                $this->redirect = false;
                export_datamodel();
            }
        }

    }

    // initialize
    register_tool('\OES\Admin\Tools\Export_Datamodel', 'export-datamodel');


    /**
     * Export the datamodel configs to json file.
     */
    function export_datamodel()
    {
        /* prepare return data */
        $exportData = [];

        /* loop through all config options posts */
        $posts = get_posts([
            'post_type' => 'oes_object',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'title']);
        if ($posts)
            foreach ($posts as $post) {
                if (oes_starts_with($post->post_name, '_t_')) $offset = 3;
                elseif (oes_starts_with($post->post_name, '__t_')) $offset = 4;
                elseif (oes_starts_with($post->post_name, '__')) $offset = 2;
                elseif ($post->post_name === 'oes_config') $offset = 0;
                else $offset = 1;

                $postTypeName = substr($post->post_name, $offset);
                $exportData[$postTypeName] =
                    array_merge($exportData[$postTypeName] ?? [], json_decode($post->post_content, true));

                /* Clean data: remove all keys for fields (legacy) */
                if (isset($exportData[$postTypeName]['acf_add_local_field_group']['fields']))
                    $exportData[$postTypeName]['acf_add_local_field_group']['fields'] =
                        array_values($exportData[$postTypeName]['acf_add_local_field_group']['fields']);
            }

        if (!empty($exportData))
            oes_export_json_data('datamodel_' . date('Y-m-d') . '.json', $exportData);
    }

endif;