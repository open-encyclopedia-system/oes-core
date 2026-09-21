<?php
global $oes;

$object = $_GET['object'] ?? false;
$type = $_GET['type'] ?? false;
$component = $_GET['component'] ?? false;
$oesType = $oes->$component[$object]['type'] ?? 'other';

?>
<div class="wrap">
    <div class="oes-page-header-wrapper">
        <div class="oes-page-header">
            <?php

            $schemaLinks = \OES\Model\get_schema_links();

            $optionsHTML = '<option value="admin.php?page=oes_settings_schema">' . esc_html__('Overview', 'oes') . '</option>';
            foreach ($schemaLinks ?? [] as $objectDataKey => $objectData) {
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

                    if ($object == 'global') {
                        $tabs = [
                                'oes' => __('Publisher', 'oes')
                        ];
                    } else {
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
        else:
            require_once OES_CORE_PLUGIN . '/includes/admin/lists/class-schema_list_table.php';

            $listTable = new Schema_List_Table([
                    'singular' => __('name', 'oes'),
                    'plural' => __('names', 'oes'),
                    'columns' => [
                            'title' => __('Name', 'oes'),
                            'description' => __('Description', 'oes'),
                            'format' => __('Format', 'oes'),
                            'template' => __('Template', 'oes')
                    ]
            ]);

            $listTable->prepare_items();

            echo '<form method="get">';
            echo '<input type="hidden" name="page" value="oes_settings_features" />';
            $listTable->display();
            echo '</form>';

        endif; ?>
    </div>
</div>
