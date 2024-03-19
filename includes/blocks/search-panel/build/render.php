<div id="oes-search-panel-trigger" <?php echo get_block_wrapper_attributes(); ?> >
    <a href="javascript:void(0);" onClick="oesTriggerById('oes-search-panel')"><?php
        echo oes_language_label_html(array_merge(['default' => 'Search'], $attributes['labels'] ?? [])); ?>
    </a>
</div>
<div id="oes-search-panel" style="display:none;"><?php
    echo render_block_core_template_part([
        'slug' => 'search-panel',
        'theme' => wp_get_theme()->get_stylesheet()]); ?>
</div>
