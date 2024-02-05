<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Admin Options', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-8" aria-label="Secondary menu"><?php

        foreach ([
                     'admin-features' => __('Features', 'oes'),
                     'admin' => __('Visibility', 'oes'),
                     'info' => __('Info', 'oes')
                 ] as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_admin&tab=' . $tab),
            ((($_GET['tab'] ?? 'admin-features') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php

    if (isset($_GET['tab']) && $_GET['tab'] == 'info'):?>
        <p><?php
        _e('So, you are an OES admin. There is some extra stuff that you can see and administrate. But ' .
            'beware of the consequences, not every configuration option will be visible after you exit admin mode.',
            'oes'); ?>
        </p><?php
    else: \OES\Admin\Tools\display($_GET['tab'] ?? 'admin-features');
    endif;
    ?>
</div>