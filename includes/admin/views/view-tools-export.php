<?php

/* Display errors after first h2-header. */
settings_errors();

/* get tool messages */
do_action('admin_notices');

?>
<h1><?php _e('Export', 'oes'); ?></h1>
<?php echo \OES\Admin\Tools\get_link_to_wordpress_tools(); ?>
<div class="oes-pb-0-5 oes-form-wrapper-small"><?php \OES\Admin\Tools\display_tool('export'); ?></div>