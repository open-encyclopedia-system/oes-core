<?php
if (empty(OES()->post_types)) :
    ?>
    <div class="notice notice-info">
    <p><?php _e('There are no custom post types registered.', 'oes'); ?></p>
    </div><?php
endif; ?>
<h1><?php _e('OES Datamodel', 'oes'); ?></h1>
<div class="oes-form-wrapper"><?php \OES\Admin\Tools\display_tool('datamodel'); ?></div>
<div class="oes-form-wrapper"><?php \OES\Admin\Tools\display_tool('export-datamodel'); ?></div>