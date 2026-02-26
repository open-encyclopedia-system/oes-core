<div class="wrap">
    <div class="oes-page-header-wrapper">
        <div class="oes-page-header">
            <h1><?php _e('Open Encyclopedia System', 'oes'); ?></h1>
            <h2 class="oes-display-none"></h2>
        </div>
    </div>
    <div class="oes-page-body">
        <div class="oes-tool-information-wrapper">
            <p>
                <strong><?php _e('Welcome to the OES dashboard! ', 'oes'); ?></strong><?php
                _e('Here you can manage your application, monitor tasks, and access key tools and guidelines. ', 'oes');
                printf(__('For more information, see the Help tab or visit our %swebsite%s.', 'oes'),
                    '<a href="http://www.open-encyclopedia-system.org/" target="_blank">', '</a>'); ?>
            </p>
        </div>
        <div class="metabox-holder">
            <div class="postbox-content"><?php

                do_meta_boxes('toplevel_page_oes_settings', 'normal', null);
                ?>
                <div class="oes-dashboard-grid">
                    <div class="oes-dashboard-column">
                <?php

                do_meta_boxes('toplevel_page_oes_settings', 'advanced', null);
                ?>
                    </div>
                    <div class="oes-dashboard-column">                <?php

                        do_meta_boxes('toplevel_page_oes_settings', 'side', null);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>