<div class="wrap">
    <div class="oes-page-header-wrapper">
        <h1><?php _e('Data Model', 'oes'); ?></h1>
        <h2 class="oes-display-none"></h2>
        <div class="oes-page-navigation">
            <ul class="subsubsub"><?php

                $tabs = [
                    'status' => __('Status', 'oes'),
                    'bidirectional' => __('Bidirectional Fields', 'oes')
                ];

                if (\OES\Rights\user_is_oes_admin()) {
                    $tabs['model'] = __('Config', 'oes');
                    $tabs['factory'] = __('Factory', 'oes');
                }

                foreach ($tabs as $tab => $label) {
                    printf('<li class="%s"><a href="%s" class="oes-tab %s">%s</a></li>',
                        $tab,
                        admin_url('admin.php?page=oes_tools_model&tab=' . $tab),
                        ((($_GET['tab'] ?? 'status') == $tab) ? 'current' : ''),
                        $label
                    );
                }

                ?>
            </ul>
            <div style="clear: both;"></div>
            <hr>
        </div>
    </div>
    <div class="oes-page-body">
        <?php

        switch ($_GET['tab'] ?? 'status') {

            case 'status':
                oes_get_view('view-tools-model-status');
                break;

            case 'bidirectional':
                oes_get_view('view-tools-model-bidirectional');
                break;

            case 'model':
                \OES\Admin\Tools\display('model');
                break;

            case 'factory':
                \OES\Admin\Tools\display('factory');
                break;
        } ?>
    </div>
</div>