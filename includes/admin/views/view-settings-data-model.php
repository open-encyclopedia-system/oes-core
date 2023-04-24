<?php
if (empty(OES()->post_types)) :
    ?>
    <div class="notice notice-info">
    <p><?php _e('There are no custom post types registered.', 'oes'); ?></p>
    </div><?php
endif; ?>
<h1><?php _e('OES Data Model', 'oes'); ?></h1>
<div class="oes-settings-nav-tabs-panel">
    <div class="oes-tool-information-wrapper"><p><?php

            $manualLinkPostTypes = sprintf('<a href="%s" target="_blank">%s</a>',
                'https://developer.wordpress.org/reference/functions/register_post_type/',
                'custom post types');

            $manualLinkTaxonomies = sprintf('<a href="%s" target="_blank">%s</a>',
                'https://developer.wordpress.org/reference/functions/register_taxonomy/',
                'custom taxonomies');
            echo
                __('The OES feature <b>Data Model</b> allows you to configure the appearance and behaviour of the ' .
                    'objects defined in your OES project plugin.', 'oes') . '</p><p>' .
                sprintf(__('The objects represent different content types that include fields, relationships and text editor ' .
                    'options. In WordPress they are called <b>custom post types</b> and <b>custom taxonomies</b>. ' .
                    'An instance of a custom post type is called <b>post</b>, an instance of a custom taxonomy is ' .
                    'called <b>term</b>. ' .
                    'You can find a full description of options in the WordPress manual for %s and %s ' .
                    '(not all options are configurable via the OES tools, some advanced options require code editing ' .
                    'inside your OES project plugin).', 'oes'),
                    $manualLinkPostTypes,
                    $manualLinkTaxonomies
                ) . '</p><p>' .
                __('Note that none of the configurations will have any impact on the data itself! ' .
                    'This is WordPress logic and ' .
                    'OES bows to their fundamental conception of editors\' needs regarding data consistency. ' .
                    'The configurations affect only the presentation and the availability of the data. ' .
                    'This means that if you add additional data to your post type (e.g. add taxonomies, enable the ' .
                    'block editor or include page ' .
                    'attributes), this data will be stored in the database until you actively ' .
                    'remove it by e.g. deleting the field value, deleting the relation or deleting the post itself.',
                    'oes'). '</p><p>' .
                sprintf(__("You can see a list of the current registered post types and taxonomies below. If there are no " .
                    "items listed you need to check the data model file in your OES project plugin or check on " .
                    "the %sOES admin settings%s.", 'oes'),
                    '<a href="' . admin_url('admin.php?page=oes_admin') . '">',
                    '</a>'
                ); ?></p></div><?php

    /* prepare table with data */
    $oes = OES();

    /* post types ----------------------------------------------------------------------------------------------------*/
    $tablePostTypesRows = '';
    foreach ($oes->post_types as $postTypeKey => $postType) {

        $size = 0;
        foreach($postType['field_options'] ?? [] as $field)
            if(isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'])) $size++;

        $tablePostTypesRows .= '<tr>' .
            '<th><strong>' . ($postType['label'] ?? $postTypeKey) .
            '<code class="oes-object-identifier">' . $postTypeKey . '</code>' .
            ($size ? ('<span class="oes-config-data-model-field-count">' . $size . '  Fields</span>') : '') .
            '</strong></th>' .
            '</tr>';
    }

    if(!empty($tablePostTypesRows)) :
    ?>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
        <thead>
        <tr class="oes-config-table-separator"><th><strong>Post Types</strong></th></tr>
        </thead>
        <tbody><?php echo $tablePostTypesRows;?></tbody>
    </table><?php
    endif;

    /* taxonomies ----------------------------------------------------------------------------------------------------*/
    $tableTaxonomyRows = '';
    foreach ($oes->taxonomies as $taxonomyKey => $taxonomy) {

        /* Add to Table --------------------------------------------------------------------------------------*/
        $size = 0;
        foreach($taxonomy['field_options'] ?? [] as $field)
            if(isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'])) $size++;

        $tableTaxonomyRows .= '<tr>' .
            '<th><strong>' . ($taxonomy['label'] ?? $taxonomyKey) .
            '<code class="oes-object-identifier">' . $taxonomyKey . '</code>' .
            ($size ? ('<span class="oes-config-data-model-field-count">' . $size . '  Fields</span>') : '') .
            '</strong></th>' .
            '</tr>';
    }


    if(!empty($tableTaxonomyRows)) :
        ?>
        <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
        <thead>
        <tr class="oes-config-table-separator"><th><strong>Taxonomies</strong></th></tr>
        </thead>
        <tbody><?php echo $tableTaxonomyRows;?></tbody>
        </table><?php
    endif;

    if (oes_user_is_oes_admin()):?>
        <div class="oes-pb-0-5 oes-form-wrapper-small"><?php \OES\Admin\Tools\display_tool('data-model'); ?></div><?php
    endif; ?>
</div>