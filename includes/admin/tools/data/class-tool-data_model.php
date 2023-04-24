<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Data_Model')) :

    /**
     * Class Data_Model
     *
     * Implement the config tool for writing configurations.
     */
    class Data_Model extends Tool
    {

        //Overwrite parent
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }


        ///Overwrite parent
        function html(): void
        {
            if (oes_user_is_oes_admin()):?>
                <div class="oes-configuration-header">
                    <p><?php
                        _e('The data model configuration via the editorial layer (as in the OES settings) ' .
                            'overwrites the plugin configurations. You can restore the plugin configurations via the ' .
                            'button below or export you current configurations.', 'oes'); ?>
                    </p>
                </div>
                <span>
                    <input type="submit" name="reload_json" id="reload_json" class="button"
                           value="Reload from Plugin Config" onClick="return confirm('<?php
                    _e('You are about to overwrite the local configurations. ' .
                        'It is recommended that you export the configuration first. Do you want to proceed?', 'oes');
                    ?>');">
                </span>
                <span>
                <input type="submit" name="export" id="export" class="button" value="Export Config from DB">
                </span><?php
            endif;
        }


        //Overwrite parent
        function admin_post_tool_action(): void
        {
            /* check action : "reload from plugin" the param will be parsed when executing the OES init hook. */

            /* check action : "export configs" */
            if (isset($_POST['export'])) {
                $this->redirect = false;
                export_data_model();
            }
        }

    }

    // initialize
    register_tool('\OES\Admin\Tools\Data_Model', 'data-model');


    /**
     * Export the data model configs to json file.
     * @return void
     */
    function export_data_model(): void
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
            foreach ($posts as $post)
                if ($post->post_excerpt !== 'language_group') {
                    if (oes_starts_with($post->post_name, '_t_')) $offset = 3;
                    elseif (oes_starts_with($post->post_name, '__t_')) $offset = 4;
                    elseif (oes_starts_with($post->post_name, '___')) $offset = 3;
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
            oes_export_json_data('model_' . OES()->basename_project . '_' . date('Y-m-d') . '.json', $exportData);
    }

endif;