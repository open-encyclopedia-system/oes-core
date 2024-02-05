<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Data Model', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-4" aria-label="Secondary menu"><?php

        /* prepare tabs*/
        $tabs['status'] = 'Status';
        if (\OES\Rights\user_is_oes_admin()) {
            $tabs['model'] = 'Config';
            $tabs['factory'] = 'Factory';
        }
        $tabs['info'] = 'Info';

        foreach ($tabs as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_tools_model&tab=' . $tab),
            ((($_GET['tab'] ?? 'status') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body">
    <?php

    switch ($_GET['tab'] ?? 'status') {

        case 'status':
            oes_get_view('view-tools-model-status');
            break;

        case 'model':
            \OES\Admin\Tools\display('model');
            break;

        case 'factory':
            \OES\Admin\Tools\display('factory');
            break;

        case 'info':
            oes_get_view('view-tools-model-info');
            break;
    } ?>
</div>