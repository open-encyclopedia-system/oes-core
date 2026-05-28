<?php
global $oes;

if (empty($oes->post_types) && empty($oes->taxonomies)) : ?>
    <div><?php echo esc_html__(
            'There is no data registered. Try to reload the data model by using the tool in the next tab ("OES Tools" / "Data Model" / "Config"), use "Reload from Plugin Config".',
            'oes'
        ); ?></div>
<?php
return;
endif;

function oes_prepare_ui_fields(array $fields): array {
    $count = 0;
    $tableFields = '';
    foreach ($fields as $fieldKey => $field) {
        if (isset($field['type'])) {

            if(!in_array($field['type'], ['tab', 'accordion'], true)) {
                $count++;
                $tableFields .= sprintf('<tr><td>%s</td><td><code>%s</code></td><td>%s</td><td>%s</td></tr>',
                        $field['label'],
                        $fieldKey,
                        $field['type'],
                        $field['language_dependent'] ? 'true' : ''
                );
            }
            else {
                $type = mb_strtoupper(mb_substr($field['type'], 0, 1)) . mb_substr($field['type'], 1);
                $tableFields .= sprintf('<tr><td colspan="4" style="padding-top:.5rem"><strong>%s</strong></td></tr>',
                        $type . ' ' . $field['label']
                );
            }
        }
    }
    return [$count ?? 0, $tableFields];
}

//TODO styling
function oes_render_object_rows(array $objects): void
{
    ksort($objects);
    foreach ($objects as $key => $object) {
        [$count, $table] = oes_prepare_ui_fields($object['field_options'] ?? []);
        $label = $object['label'] ?? $key;

        if($count > 0){
            echo '<details class="oes-details">
<summary><strong>' . esc_html($label) . '</strong>' .
                    ' <code class="oes-object-identifier">' . esc_html($key) . '</code>' .
                    ($count ? ' <span class="oes-config-data-model-field-count">' . intval($count) . ' ' . __('Fields', 'oes') . '</span>' : '') .
                    '</summary>
<div class="oes-config-data-model-fields" style="margin: 1rem">
<table class="table-view-list">
<thead>
<tr>
<th>' . __('Field Name', 'oes') . '</th>
<th>' . __('Key', 'oes') . '</th>
<th>' . __('Type', 'oes') . '</th>
<th>' . __('Language Dependent', 'oes') . '</th>
</tr>
</thead>
<tbody>
' . $table . '
</tbody>
</table>
</div>
</details>';
        }
        else {
            echo '<div><strong>' . esc_html($label) . '</strong>' .
                    ' <code class="oes-object-identifier">' . esc_html($key) . '</code></div>';
        }
    }
}

echo '<h2>' . __('Post Types', 'oes') . '</h2>';
oes_render_object_rows($oes->post_types);

echo '<h2>' . __('Taxonomies', 'oes') . '</h2>';
oes_render_object_rows($oes->taxonomies);