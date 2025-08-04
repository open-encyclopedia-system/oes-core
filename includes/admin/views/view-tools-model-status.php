<?php
global $oes;

// Helpers
function oes_count_non_ui_fields(array $fields): int {
    $count = 0;
    foreach ($fields as $field) {
        if (isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'], true)) {
            $count++;
        }
    }
    return $count ?? 0;
}

function oes_render_object_rows(array $objects, string $type): string {
    ksort($objects);
    $rows = '';
    foreach ($objects as $key => $object) {
        $count = oes_count_non_ui_fields($object['field_options'] ?? []);
        $label = $object['label'] ?? $key;
        $rows .= '<tr><th><strong>' . esc_html($label) .
            '<code class="oes-object-identifier">' . esc_html($key) . '</code>' .
            ($count ? '<span class="oes-config-data-model-field-count">' . intval($count) . ' ' . __('Fields', 'oes') . '</span>' : '') .
            '</strong></th></tr>';
    }
    return $rows;
}

// Render post types
$postTypesRows = oes_render_object_rows($oes->post_types, 'post_type');
if (!empty($postTypesRows)) : ?>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
        <thead>
        <tr class="oes-config-table-separator">
            <th><strong><?php _e('Post Types', 'oes'); ?></strong></th>
        </tr>
        </thead>
        <tbody><?php echo $postTypesRows; ?></tbody>
    </table>
<?php endif;

// Render taxonomies
$taxonomyRows = oes_render_object_rows($oes->taxonomies, 'taxonomy');
if (!empty($taxonomyRows)) : ?>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
        <thead>
        <tr class="oes-config-table-separator">
            <th><strong><?php _e('Taxonomies', 'oes'); ?></strong></th>
        </tr>
        </thead>
        <tbody><?php echo $taxonomyRows; ?></tbody>
    </table>
<?php endif;

// Empty state message
if (empty($postTypesRows) && empty($taxonomyRows)) : ?>
    <div><?php echo esc_html__(
            'There is no data registered. Try to reload the data model by using the tool in the next tab ("OES Tools" / "Data Model" / "Config"), use "Reload from Plugin Config".',
            'oes'
        ); ?></div>
<?php endif;

// Bidirectional Fields
$bidirectionalRows = '';
foreach ($oes->post_types as $postTypeKey => $postType) {

    $fields = oes_get_all_object_fields($postTypeKey);
    $bidirectionalFields = array_filter($fields, function ($field) {
        return in_array($field['type'], ['relationship', 'post_object'], true) &&
            !in_array($field['name'], [
                'field_connected_parent',
                'field_oes_versioning_posts',
                'field_oes_versioning_current_post',
                'field_oes_versioning_parent_post'
            ], true);
    });

    if (empty($bidirectionalFields)) {
        continue;
    }

    $bidirectionalRows .= '<tr><th colspan="2"><strong>' .
        esc_html($postType['label'] ?? $postTypeKey) .
        '</strong><code class="oes-object-identifier">' . esc_html($postTypeKey) . '</code></th></tr>';

    foreach ($bidirectionalFields as $fieldKey => $fieldData) {

        $fieldPostTypes = (array) ($fieldData['post_type'] ?? []);
        $targetFields = [];

        foreach ($fieldData['bidirectional_target'] ?? [] as $targetKey) {
            $targetPostType = '';

            // Check if field exists in related post type
            foreach ($fieldPostTypes as $pt) {
                if (!empty($oes->post_types[$pt]['field_options'][$targetKey])) {
                    $targetPostType = $oes->post_types[$pt]['label'] ?? $pt;
                }
            }

            // Fallback if not found
            if (empty($targetPostType)) {
                $fieldObj = oes_get_field_object($targetKey);
                if ($fieldObj) {
                    $postTypeLabels = [];
                    if (!empty($fieldObj['parent']) && ($group = get_post($fieldObj['parent']))) {
                        $locations = unserialize($group->post_content);
                        foreach ($locations['location'][0] ?? [] as $rule) {
                            $postTypeLabels[] = $oes->post_types[$rule['value']]['label'] ?? $rule['value'];
                        }
                    }
                    $targetPostType = implode(', ', $postTypeLabels) . ' <span class="oes-highlighted">[' . __('Post types do not match', 'oes') . ']</span>';
                } else {
                    $targetPostType = '<span class="oes-highlighted">[' . __('Field does not exist', 'oes') . ']</span>';
                }
            }

            if ($targetKey === $fieldKey) {
                $targetPostType .= ' (' . __('Self reference', 'oes') . ')';
            }

            $targetFields[] = '<div>' . $targetPostType . ', <strong>' .
                esc_html(get_field_object($targetKey)['label'] ?? $targetKey) . '</strong>' .
                '<div><code class="oes-object-identifier">' . esc_html($targetKey) . '</code></div></div>';
        }

        if (empty($targetFields)) {
            $targetFields[] = '<span class="oes-highlighted">[' . __('No fields connected', 'oes') . ']</span>';
        }

        $connectedPostTypes = array_map(function ($pt) use ($oes) {
            return esc_html($oes->post_types[$pt]['label'] ?? $pt);
        }, $fieldPostTypes);
        if (empty($connectedPostTypes)) {
            $connectedPostTypes[] = '<span class="oes-highlighted">[' . __('All post types', 'oes') . ']</span>';
        }

        $bidirectionalRows .= sprintf(
            '<tr><th>%s, <strong>%s</strong><div>%s</div><div><code class="oes-object-identifier">%s</code></div></th><td>%s</td></tr>',
            esc_html($postType['label'] ?? $postTypeKey),
            esc_html($fieldData['label'] ?? $fieldKey),
            __('Connects to:', 'oes') . ' ' . implode(', ', $connectedPostTypes),
            esc_html($fieldKey),
            implode('', $targetFields)
        );
    }
}

if (!empty($bidirectionalRows)) : ?>
    <h2><?php _e('Bidirectional Fields', 'oes'); ?></h2>
    <table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list">
        <thead>
        <tr class="oes-config-table-separator">
            <th><strong><?php _e('Field', 'oes'); ?></strong></th>
            <th><strong><?php _e('Target', 'oes'); ?></strong></th>
        </tr>
        </thead>
        <tbody><?php echo $bidirectionalRows; ?></tbody>
    </table>
<?php endif; ?>
