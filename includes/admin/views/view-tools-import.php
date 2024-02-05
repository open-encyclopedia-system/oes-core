<?php

/* Display errors after first h2-header. */
settings_errors();

/* get tool messages */
do_action('admin_notices');

?>
<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('Import', 'oes'); ?></h1>
    </div>
</div>
<div class="oes-page-body">
    <?php echo \OES\Admin\Tools\get_link_to_wordpress_tools(); ?>
    <?php \OES\Admin\Tools\display('import'); ?>
</div>