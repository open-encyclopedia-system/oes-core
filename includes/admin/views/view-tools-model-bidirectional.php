<?php
global $oes;

if (empty($oes->post_types) && empty($oes->taxonomies)) : ?>
    <div>
        <?php echo esc_html__(
            'There is no data registered. Try to reload the data model by using the tool in the next tab ("OES Tools" / "Data Model" / "Config"), use "Reload from Plugin Config".',
            'oes'
        ); ?>
    </div>
    <?php
    return;
endif;

function oes_get_target_post_type_label($targetKey, $fieldPostTypes, $oes)
{
    $targetPostType = '';

    // Check if field exists in related post type
    foreach ($fieldPostTypes as $pt) {
        if (!empty($oes->post_types[$pt]['field_options'][$targetKey])) {
            $targetPostType = esc_html($oes->post_types[$pt]['label'] ?? $pt);
        }
    }

    // Fallback if not found
    if (empty($targetPostType)) {
        $fieldObj = oes_get_field_object($targetKey);
        if ($fieldObj !== false) {
            $postTypeLabels = [];
            if (!empty($fieldObj['parent']) && ($group = get_post($fieldObj['parent']))) {
                $locations = maybe_unserialize($group->post_content);
                foreach ($locations['location'][0] ?? [] as $rule) {
                    $postTypeLabels[] = esc_html($oes->post_types[$rule['value']]['label'] ?? $rule['value']);
                }
            }
            $targetPostType = implode(', ', $postTypeLabels)
                . ' <span class="oes-highlighted">[' . esc_html__('Post types do not match', 'oes') . ']</span>';
        } else {
            $targetPostType = '<span class="oes-highlighted">[' . esc_html__('Field does not exist', 'oes') . ']</span>';
        }
    }

    return $targetPostType;
}

function oes_render_target_field($fieldKey, $targetKey, $fieldPostTypes)
{
    $targetPostType = oes_get_target_post_type_label($targetKey, $fieldPostTypes, $GLOBALS['oes']);

    if ($targetKey === $fieldKey) {
        $targetPostType .= ' (' . esc_html__('Self reference', 'oes') . ')';
    }

    $fieldObj = get_field_object($targetKey);
    $fieldLabel = $fieldObj['label'] ?? $targetKey;

    return '<div>' . $targetPostType . ', <strong>' . esc_html($fieldLabel) . '</strong>' .
        '<div><code class="oes-object-identifier">' . esc_html($targetKey) . '</code></div></div>';
}

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

    $postTypeLabel = esc_html($postType['label'] ?? $postTypeKey);
    $postTypeCode  = esc_html($postTypeKey);

    echo '<h2>' . $postTypeLabel . ' <code class="oes-object-identifier">' . $postTypeCode . '</code></h2>';
    ?>
    <table class="oes-config-table wp-list-table fixed table-view-list widefat striped">
        <thead>
        <tr class="oes-config-table-separator">
            <th><strong><?php esc_html_e('Source Field', 'oes'); ?></strong></th>
            <th><strong>→ <?php esc_html_e('Target Field(s)', 'oes'); ?></strong></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($bidirectionalFields as $fieldKey => $fieldData) {

            $fieldPostTypes = !empty($fieldData['post_type']) ? (array)$fieldData['post_type'] : [];
            $targets = $fieldData['bidirectional_target'] ?? [];

            $targetFields = [];

            if (!empty($targets)) {
                foreach ($targets as $targetKey) {
                    $targetFields[] = oes_render_target_field($fieldKey, $targetKey, $fieldPostTypes);
                }
            }

            if (empty($targetFields)) {
                $targetFields[] = '<span class="oes-highlighted">[' . esc_html__('No fields connected', 'oes') . ']</span>';
            }

            printf(
                '<tr>
                        <td><strong>%s</strong><div><code class="oes-object-identifier">%s</code></div></td>
                        <td>%s</td>
                    </tr>',
                esc_html($fieldData['label'] ?? $fieldKey),
                esc_html($fieldKey),
                implode('', $targetFields)
            );
        }
        ?>
        </tbody>
    </table>
    <?php
}
