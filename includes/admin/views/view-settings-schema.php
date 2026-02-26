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
        foreach ($schemaLinks ?? [] as $schemaLinksType) {
            foreach ($schemaLinksType['data'] ?? [] as $objectDataKey => $objectData) {
                $key      = $objectData['key'] ?? $objectDataKey;
                $label    = $objectData['label'] ?? $objectDataKey;
                $url      = $objectData['url'] ?? '';
                $selected = ($key === $object) ? 'selected' : '';

                $optionsHTML .= sprintf(
                    '<option value="%s" %s>%s</option>',
                    esc_url($url),
                    esc_attr($selected),
                    esc_html($label)
                );
            }
        }

        $headerHTML  = esc_html__('Schema', 'oes') . ' ';
        $headerHTML .= '<select id="schema-links" onchange="oesGoToAdminPage(this)">' . $optionsHTML . '</select>';
        if ($object) {
            $headerHTML .= ' <code class="oes-object-identifier">' . esc_html($object) . '</code>';
        }
        ?>
        <h1><?php echo $headerHTML; ?></h1>
    </div>

    <?php if ($object): ?>
    <ul class="subsubsub">
            <?php

            $tabs = [
                'oes'         => __('General', 'oes'),
                'oes_single'  => __('Single', 'oes'),
                'oes_archive' => __('Archive', 'oes'),
            ];

            // Add LoD tabs if present @oesDevelopment call this from API classes?
            if ($oes->post_types[$object]['lod'] ?? false) {
                foreach ($oes->apis as $apiKey => $api) {
                    if (!empty($api->config_options['properties']['options'])) {
                        $tabs[$apiKey] = $api->label;
                    }
                }
            }

            /**
             * Filters the tabs for the OES schema.
             *
             * @param array  $tabs      The tabs for the OES schema.
             * @param string $object    The current object.
             * @param string $component The current component.
             * @param string $oesType   The type of the OES item.
             */
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
        <div style="clear: both;"></div>
        <hr>
    <?php endif; ?>
</div>

<div class="oes-page-body">
    <?php

    if ($type):
        \OES\Admin\Tools\display('schema-' . $type);
    else: ?>
            <?php
            foreach ($schemaLinks ?? [] as $type => $schemaLinksType) {

                if(empty($schemaLinksType['data'] ?? '')){
                    continue;
                }

                $label = $schemaLinksType['label'] ?? $type;
                echo '<h2>' . esc_html($type === 'other' ? '[Default]' : $label) . '</h2>';

                foreach ($schemaLinksType['data'] ?? [] as $objectDataKey => $objectData) {
                    $label = $objectData['label'] ?? $objectDataKey;
                    $url   = admin_url($objectData['url'] ?? '');
                    $key   = $objectData['key'] ?? '';

                    $link = oes_get_html_anchor(
                        esc_html($label),
                        esc_url($url)
                    );

                    printf(
                        '<p>%s <code class="oes-object-identifier">%s</code></p>',
                        $link,
                        esc_html($key)
                    );
                }
            }
            ?>
    <?php endif; ?>
</div>
