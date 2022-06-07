<h1><?php _e('OES Cache Settings', 'oes'); ?></h1>
<p><?php _e('The OES feature <b>Cache</b> allows you to store data so that future request on ' .
        'archive pages (pages that list all posts of a specific post type or taxonomy) or the index page ' .
        'can ve served faster. ' .
        'The table below shows the timestamp of the current cache entries. You can see the most ' .
        'recent object of the post type or taxonomy and the corresponding timestamp. ' .
        'You can update the cache manually or by setting up a scheduler.', 'oes');?></p>
<div><?php echo \OES\Admin\Tools\oes_get_cache_info_html();?></div>
<div class="oes-form-wrapper-small"><?php \OES\Admin\Tools\display_tool('cache-update'); ?></div>
<div class="oes-form-wrapper-small"><?php \OES\Admin\Tools\display_tool('cache-empty'); ?></div>
<div class="oes-form-wrapper"><?php \OES\Admin\Tools\display_tool('cache-scheduler'); ?></div>