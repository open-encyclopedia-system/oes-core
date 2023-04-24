<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


add_action('admin_init', '\OES\Admin\Tools\include_tools');


/**
 * Include tools.
 * @return void
 */
function include_tools(): void
{

    foreach ([
                 'admin_menu',
                 'admin_editor',
                 'admin_columns',
                 'admin_inheritance',
                 'admin_pattern',
                 'admin_labels',
                 'theme_languages',
                 'theme_notes',
                 'theme_single_view',
                 'theme_archive_view',
                 'theme_index_pages',
                 'theme_search',
                 'theme_media',
                 'theme_colors',
                 'theme_logos',
                 'theme_labels',
                 'project'
             ] as $setting)
        oes_include('/includes/admin/tools/config/class-config-' . $setting . '.php');


    //@oesDevelopment oes_include('/includes/admin/tools/config-export_formats.class.php');
    oes_include('/includes/admin/tools/data/class-tool-import.php');
    oes_include('/includes/admin/tools/data/class-tool-operations.php');
    oes_include('/includes/admin/tools/data/class-tool-export.php');

    if (oes_user_is_oes_admin()) {

        oes_include('/includes/admin/tools/config/class-config-admin.php');

        oes_include('/includes/admin/tools/cache/class-cache_update.php');
        oes_include('/includes/admin/tools/cache/class-cache_empty.php');
        oes_include('/includes/admin/tools/cache/hooks-cache.php');
        oes_include('/includes/admin/tools/config/class-config-cache_scheduler.php');

        oes_include('/includes/admin/tools/data/class-tool-data_model.php');

        oes_include('/includes/admin/tools/data/class-tool-update.php');
        oes_include('/includes/admin/tools/data/class-tool-delete.php');
    }
}