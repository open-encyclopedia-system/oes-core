<?php
global $oes;

$object = $_GET['object'] ?? false;
$type = $_GET['type'] ?? false;
$component = $_GET['component'] ?? false;
$oesType = $oes->$component[$object]['type'] ?? 'other';

?>
<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <?php

        $schemaLinks = \OES\Model\get_schema_links();

        $optionsHTML = '<option value="admin.php?page=oes_settings_schema">' . esc_html__('Overview', 'oes') . '</option>';
        foreach ($schemaLinks ?? []  as $objectDataKey => $objectData) {
                $key = $objectData['key'] ?? $objectDataKey;
                $label = $objectData['label'] ?? $objectDataKey;
                $url = $objectData['url'] ?? '';
                $selected = ($key === $object) ? 'selected' : '';

                $optionsHTML .= sprintf(
                        '<option value="%s" %s>%s</option>',
                        esc_url($url),
                        esc_attr($selected),
                        esc_html($label)
                );
        }

        $headerHTML = esc_html__('Schema', 'oes') . ' ';
        $headerHTML .= '<select id="schema-links" onchange="oesGoToAdminPage(this)">' . $optionsHTML . '</select>';
        if ($object && $object != 'global') {
            $headerHTML .= ' <code class="oes-object-identifier">' . esc_html($object) . '</code>';
        }
        ?>
        <h1><?php echo $headerHTML; ?></h1>
    </div>

    <?php if ($object): ?>
        <div class="oes-page-navigation">
            <ul class="subsubsub">
                <?php

                if($object == 'global') {
                    $tabs = [
                            'oes' => __('Publisher', 'oes')
                    ];
                }
                else{
                    $tabs = [
                            'oes' => __('General', 'oes'),
                            'mapping' => __('Schema Mapping', 'oes'),
                            'display' => __('Display', 'oes'),
                    ];

                    if ($oes->post_types[$object]['lod'] ?? false) {
                        foreach ($oes->apis as $apiKey => $api) {
                            if (!empty($api->config_options['properties']['options'])) {
                                $tabs[$apiKey] = $api->label;
                            }
                        }
                    }
                }

                $tabs = apply_filters('oes/schema_tabs', $tabs, $object, $component, $oesType);

                foreach ($tabs as $tabType => $label) {
                    $link = admin_url('admin.php?page=oes_settings_schema&tab=schema' .
                            '&type=' . urlencode($tabType) .
                            '&component=' . urlencode($component) .
                            '&object=' . urlencode($object));

                    $class = ($type === $tabType) ? 'current' : '';

                    printf(
                            '<li class="%s"><a href="%s" class="oes-tab %s">%s</a></li>',
                            esc_html($tabType),
                            esc_url($link),
                            esc_attr($class),
                            esc_html($label)
                    );
                }


                ?>
            </ul>
        </div>
        <div style="clear: both;"></div>
        <hr>
    <?php endif; ?>
</div>

<div class="oes-page-body">
    <?php

    $type = isset($type) ? sanitize_key($type) : '';

    if ($type):
        \OES\Admin\Tools\display('schema-' . $type);
    else: ?>
        <div class="oes-schema-card">
            <a href="<?php echo esc_url(admin_url('admin.php?page=oes_settings_schema&tab=schema&type=oes&component=global&object=global')); ?>">
                <?php _e('Global', 'oes'); ?>
            </a><br>
            <div><?php _e('site-wide parameter, e.g. publisher', 'oes'); ?></div>
        </div>
        <table class="form-table oes-form-table table-view-list">
            <thead>
            <tr>
                <th><?php _e('Name', 'oes'); ?></th>
                <th><?php _e('Format', 'oes'); ?></th>
                <th><?php _e('Template', 'oes'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($schemaLinks ?? [] as $objectDataKey => $objectData):

                if($objectDataKey == 'global') {
                    continue;
                }

                $label = $objectData['label'] ?? $objectDataKey;
                $url = admin_url($objectData['url'] ?? '');
                $key = $objectData['key'] ?? null;

                $link = oes_get_html_anchor(
                        esc_html($label),
                        esc_url($url)
                );

                if ($key === 'global') {
                    $link = '<h2>' . $link . '</h2>';
                    $code = '';
                } else {
                    $code = '<br><code class="oes-object-identifier">' . esc_html($key) . '</code>';
                }

                $schema = $objectData['schema'] ?? '';
                if (in_array($schema, ['none', 'other'], true)) {
                    $schema = '-';
                }

                $objectType = $objectData['type'] ?? '';
                if (in_array($objectType, ['none', 'other'], true)) {
                    $objectType = '-';
                }

                ?>
                <tr>
                    <td><?php echo $link . $code; ?></td>
                    <td><?php echo esc_html($schema); ?></td>
                    <td><?php echo esc_html($objectType); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
