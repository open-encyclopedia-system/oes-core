<h1><?php _e('OES Writing Settings', 'oes'); ?></h1>
<p><?php
    printf(__("This is a settings page generated by the OES Core Plugin where you can administrate settings for " .
        "OES features. To administrate WordPress writing settings go %shere%s.", 'oes'),
        '<a href="' . admin_url('options-writing.php') . '">',
        '</a>'
    ); ?>
</p>
<div class="oes-settings-nav-tabs-container">
    <ul class="oes-settings-nav-tabs"><?php

        foreach ([
                     'admin-menu' => 'Menu',
                     'admin-editor' => 'Edit Screen',
                     'admin-columns' => 'Columns',
                     'admin-inheritance' => 'Inheritance',
                     'admin-pattern' => 'Pattern',
                     'admin-labels' => 'Labels [Admin]'
                 ] as $tabSlug => $tabLabel)
            echo sprintf('<li><a href="%s" %s>%s</a></li>',
                admin_url('admin.php?page=oes_settings_writing&select=' . $tabSlug),
                ((isset($_GET['select']) || $tabSlug !== 'admin-menu') ? '' : 'class="active"'),
                $tabLabel);
        ?>
    </ul>
</div>
<div class="oes-settings-nav-tabs-panel">
    <div class="oes-pb-0-5 oes-form-wrapper-small"><?php
        \OES\Admin\Tools\display_tool($_GET['select'] ?? 'admin-menu');
        ?></div>
</div>