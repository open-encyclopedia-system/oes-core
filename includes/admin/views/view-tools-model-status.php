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


/* prepare display of bidirectional fields */
$bidirectionalFieldRows = '';
foreach ($postTypes as $postTypeKey => $postType) {

    /* display post type */
    $bidirectionalFieldRows .= sprintf('<tr>' .
        '<th colspan="2"><strong>%s</strong><code class="oes-object-identifier">%s</code></th>' .
        '</tr>',
        $postType['label'] ?? $postTypeKey,
        $postTypeKey,
    );

    /* loop through fields */
    $fields = oes_get_all_object_fields($postTypeKey);
    foreach ($fields as $fieldKey => $fieldData)
        if (in_array($fieldData['type'], ['relationship', 'post_object']) &&
            !in_array($fieldData['name'], [
                'field_connected_parent',
                'field_oes_versioning_posts',
                'field_oes_versioning_current_post',
                'field_oes_versioning_parent_post'])) {


            $fieldPostTypes = is_array($fieldData['post_type'] ?? false) ? $fieldData['post_type'] : [];
            $targetFields = [];
            if ($fieldData['bidirectional_target'] ?? false) {
                foreach ($fieldData['bidirectional_target'] as $innerFieldKey) {

                    /* check post type */
                    $innerFieldPostType = '';
                    foreach ($fieldPostTypes as $fieldPostType)
                        if ($oes->post_types[$fieldPostType]['field_options'][$innerFieldKey] ?? false)
                            $innerFieldPostType = $oes->post_types[$fieldPostType]['label'] ?? $fieldPostType;

                    if (empty($innerFieldPostType)) {

                        /* check if field exists */
                        $innerFieldData = oes_get_field_object($innerFieldKey);

                        if ($innerFieldData) {

                            /* get connected field group */
                            $wrongPostTypes = [];
                            if (isset($innerFieldData['parent'])) {
                                if ($fieldGroup = get_post($innerFieldData['parent'])) {
                                    $location = unserialize($fieldGroup->post_content);
                                    foreach ($location['location'][0] ?? [] as $singleFieldGroup)
                                        $wrongPostTypes[] = $oes->post_types[$singleFieldGroup['value']]['label'] ??
                                            $singleFieldGroup['value'];
                                }
                            }

                            $innerFieldPostType = implode(', ', $wrongPostTypes) .
                                ' <span class="oes-highlighted">[' .
                                __('Post types do not match', 'oes') . ']</span>';
                        } else
                            $innerFieldPostType = '<span class="oes-highlighted">[' .
                                __('Field does not exists', 'oes') . ']</span>';
                    }

                    if ($innerFieldKey === $fieldKey)
                        $innerFieldPostType .= ' (' . __('Self reference', 'oes') . ')';

                    $targetFields[] = sprintf('<div>%s, <strong>%s</strong>' .
                        '<div><code class="oes-object-identifier">%s</code></div></div>',
                        $innerFieldPostType,
                        get_field_object($innerFieldKey)['label'] ?? $innerFieldKey,
                        $innerFieldKey
                    );
                }
            }

            /* no target fields */
            if (empty($targetFields))
                $targetFields[] = '<span class="oes-highlighted">[' .
                    __('No fields connected', 'oes') . ']</span>';

            $connectedPostTypes = [];
            foreach ($fieldPostTypes as $connectedPostType)
                $connectedPostTypes[] = $oes->post_types[$connectedPostType]['label'] ?? $connectedPostType;
            if (empty($connectedPostTypes)) $connectedPostTypes[] = '<span class="oes-highlighted">[' .
                __('All post types', 'oes') . ']</span>';

            $bidirectionalFieldRows .= sprintf('<tr>' .
                '<th>%s, <strong>%s</strong><div>%s</div>' .
                '<div><code class="oes-object-identifier">%s</code></div></th>' .
                '</th>' .
                '<td>%s</td></tr>',
                $postType['label'] ?? $postTypeKey,
                $fieldData['label'] ?? $fieldKey,
                __('Connects to: ', 'oes') . implode(', ', $connectedPostTypes),
                $fieldKey,
                implode('', $targetFields)
            );
        }
}

if (!empty($bidirectionalFieldRows)) :
    ?>
    <h2><?php _e('Bidirectional Fields', 'oes') ?></h2>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
    <thead>
    <tr class="oes-config-table-separator">
        <th><strong><?php _e('Field', 'oes'); ?></strong></th>
        <th><strong><?php _e('Target', 'oes'); ?></strong></th>
    </tr>
    </thead>
    <tbody><?php echo $bidirectionalFieldRows; ?></tbody>
    </table><?php
endif;