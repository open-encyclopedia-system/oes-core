<h1><?php _e('OES Admin Options', 'oes'); ?></h1>
<p><?php _e('So, you are an OES admin. There is some extra stuff that you can see and administrate. But ' .
    'beware of the consequences, not every configuration option will be visible after you exit admin mode.', 'oes'); ?>
</p>
<div class="oes-pt-2"><?php \OES\Admin\Tools\display_tool('admin'); ?></div>