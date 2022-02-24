<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Admin')) :

    /**
     * Class Admin
     *
     * Implement the config tool for admin configurations.
     */
    class Admin extends Config
    {
        //Overwrite parent
        function data_html(): string
        {
            $showOESObjects = '<tr><th><strong>' . __('Show OES Objects') . '</strong>' .
                '<div>' . __('Show the post type "OES Objects". This post type stores the post type ' .
                    'configurations.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('checkbox',
                    'oes_admin-show_oes_objects',
                    'oes_admin-show_oes_objects',
                    get_option('oes_admin-show_oes_objects')) .
                '</td></tr>';


            $suspendDatamodel = '<tr><th><strong>' . __('Suspend OES datamodel registration') . '</strong>' .
                '<div>' . __('Techy option, will temporarily skip the OES datamodel (data will not be touched, I ' .
                    'promise). Suspend datamodel registration to operate on post type "OES Objects" or execute ' .
                    'delete options.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('checkbox',
                    'oes_admin-suspend_datamodel',
                    'oes_admin-suspend_datamodel',
                    get_option('oes_admin-suspend_datamodel')) .
                '</td></tr>';

            $hideVersionTab = '<tr><th><strong>' . __('Hide versioning tab') . '</strong>' .
                '<div>' . __('Hide the post fields that hold version information.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('checkbox',
                    'oes_admin-hide_version_tab',
                    'oes_admin-hide_version_tab',
                    get_option('oes_admin-hide_version_tab')) .
                '</td></tr>';


            return '<table class="oes-option-table oes-config-table oes-replace-select2-inside wp-list-table widefat fixed table-view-list">' .
                '<thead><tr><th colspan="2"><strong>' .
                __('Admin Options', 'oes') . '</strong></th></tr></thead><tbody>' .
                $showOESObjects .
                $suspendDatamodel .
                $hideVersionTab .
                '</tbody></table>';
        }


        //Implement parent
        function admin_post_tool_action()
        {
            /* add or delete option */
            $options = ['oes_admin-suspend_datamodel', 'oes_admin-show_oes_objects', 'oes_admin-hide_version_tab'];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option, isset($_POST[$option]));
                else update_option($option, isset($_POST[$option]));
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin', 'admin');

endif;