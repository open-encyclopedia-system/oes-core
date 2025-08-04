<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

use function OES\Model\export_model_to_json;
use function OES\Model\import_model_from_json;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Model')) :

    /**
     * Class Model
     *
     * Implement the tool for data model import and export.
     */
    class Model extends Tool
    {

        /** @inheritdoc */
        function initialize_parameters(array $args = []): void
        {
            $this->form_action = admin_url('admin-post.php');
        }

        /** @inheritdoc */
        function html(): void
        {
            if (\OES\Rights\user_is_oes_admin()):?>
                <div class="oes-configuration-header">
                    <p><?php
                        _e('The data model configuration defined via the editorial layer (OES settings) overrides the plugin-based configuration. You can restore the original plugin configuration using the button below, or export your current configuration for backup or migration.', 'oes'); ?>
                    </p>
                </div>
                <span>
                    <input type="submit" name="import_json" id="import_json" class="button"
                           value="Reload from Plugin Config" onClick="return confirm('<?php
                    _e('You are about to overwrite the local configurations. It is recommended that you export the configuration first. Do you want to proceed?', 'oes');
                    ?>');">
                </span>
                <span>
                    <input type="submit" name="export_json" id="export_json" class="button"
                           value="Export Config from DB">
                </span><?php
            endif;
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            if (isset($_POST['import_json'])) {
                import_model_from_json();
            }
            elseif (isset($_POST['export_json'])) {
                $this->redirect = !(export_model_to_json());
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Model', 'model');

endif;