<?php

/* prepare table with data */
$oes = OES();

/* post types */
$tablePostTypesRows = '';
$postTypes = $oes->post_types;
ksort($postTypes);
foreach ($postTypes as $postTypeKey => $postType) {

    $size = 0;
    foreach ($postType['field_options'] ?? [] as $field)
        if (isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'])) $size++;

    $tablePostTypesRows .= '<tr>' .
        '<th><strong>' . ($postType['label'] ?? $postTypeKey) .
        '<code class="oes-object-identifier">' . $postTypeKey . '</code>' .
        ($size ? ('<span class="oes-config-data-model-field-count">' . $size . '  ' . __('Fields') . '</span>') : '') .
        '</strong></th>' .
        '</tr>';
}

if (!empty($tablePostTypesRows)) :
    ?>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
    <thead>
    <tr class="oes-config-table-separator">
        <th><strong><?php _e('Post Types', 'oes'); ?></strong></th>
    </tr>
    </thead>
    <tbody><?php echo $tablePostTypesRows; ?></tbody>
    </table><?php
endif;

/* taxonomies */
$tableTaxonomyRows = '';
$taxonomies = $oes->taxonomies;
ksort($taxonomies);
foreach ($taxonomies as $taxonomyKey => $taxonomy) {

    /* add to table */
    $size = 0;
    foreach ($taxonomy['field_options'] ?? [] as $field)
        if (isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'])) $size++;

    $tableTaxonomyRows .= '<tr>' .
        '<th><strong>' . ($taxonomy['label'] ?? $taxonomyKey) .
        '<code class="oes-object-identifier">' . $taxonomyKey . '</code>' .
        ($size ? ('<span class="oes-config-data-model-field-count">' . $size . ' ' .
            __('Fields', 'oes') . '</span>') : '') .
        '</strong></th>' .
        '</tr>';
}


if (!empty($tableTaxonomyRows)) :
    ?>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
    <thead>
    <tr class="oes-config-table-separator">
        <th><strong><?php _e('Taxonomies', 'oes'); ?></strong></th>
    </tr>
    </thead>
    <tbody><?php echo $tableTaxonomyRows; ?></tbody>
    </table><?php
endif;

if (empty($tablePostTypesRows) && empty($tableTaxonomyRows)):
    ?>
    <div><?php
    _e('There is no data registered. Try to reload the data model by using the tool in the next tab ' .
        '("OES Tools" / "Data Model" / "Config"), use "Reload from Plugin Config".', 'oes');
    ?></div><?php
endif;