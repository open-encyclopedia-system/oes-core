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


function oes_count_non_ui_fields(array $fields): int {
    $count = 0;
    foreach ($fields as $field) {
        if (isset($field['type']) && !in_array($field['type'], ['tab', 'accordion'], true)) {
            $count++;
        }
    }
    return $count ?? 0;
}

function oes_render_object_rows(array $objects) {
    ksort($objects);
    foreach ($objects as $key => $object) {
        $count = oes_count_non_ui_fields($object['field_options'] ?? []);
        $label = $object['label'] ?? $key;
        echo '<p><strong>' . esc_html($label) . '</strong>' .
            ' <code class="oes-object-identifier">' . esc_html($key) . '</code>' .
            ($count ? ' <span class="oes-config-data-model-field-count">' . intval($count) . ' ' . __('Fields', 'oes') . '</span>' : '') .
            '</p>';
    }
}

echo '<h2>' . __('Post Types', 'oes') . '</h2>';
oes_render_object_rows($oes->post_types);

echo '<h2>' . __('Taxonomies', 'oes') . '</h2>';
oes_render_object_rows($oes->taxonomies);