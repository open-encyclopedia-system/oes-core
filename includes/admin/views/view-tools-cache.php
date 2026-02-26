<div class="wrap">
    <div class="oes-page-header-wrapper">
        <div class="oes-page-header">
            <h1><?php _e('Cache', 'oes'); ?></h1>
            <h2 class="oes-display-none"></h2>
        </div>
    </div>
    <div class="oes-page-body"><?php

        $enabled = \OES\Admin\get_feature('cache');

        if (!$enabled) {
            echo '<div class="notice notice-warning"><p>' .
                __('Caching Feature is not enabled. Set Feature in ', 'oes') .
                oes_get_html_anchor(__('OES / Admin', 'oes'),
                    esc_url(admin_url('admin.php?page=oes_admin'))) . '.</p></div>';
        }

        if (!empty($_GET['cache_updated'])) {
            echo '<div class="notice notice-success is-dismissible"><p>'
                . esc_html__('Cache deletion completed.', 'oes')
                . '</p></div>';
        }

        require_once OES_CORE_PLUGIN . '/includes/cache/class-cache-list-table.php';
        $listTable = new \Cache_List_Table([
            'singular' => 'Cache',
            'plural' => 'Cache',
            'columns' => [
                'cb' => ' ',
                'name' => __('Name', 'oes'),
                'id' => __('Transient Key', 'oes'),
                'parts' => __('Parts', 'oes'),
                'size' => __('Size (KB)', 'oes'),
                'timestamp' => __('Timestamp', 'oes')
            ]
        ]);

        $listTable->process_bulk_action();
        $listTable->prepare_items();

        echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=oes_tools_cache')) . '">';
        echo '<input type="hidden" name="page" value="admin_oes_cache" />';
        $listTable->display();
        echo wp_nonce_field('oes_cache_bulk_action');
        echo '</form>';

        ?>
    </div>
</div>
