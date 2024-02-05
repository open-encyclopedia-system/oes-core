<table class="oes-config-table oes-replace-select2-inside striped wp-list-table widefat fixed table-view-list"><?php

    /* prepare table with data */
    $oes = OES();

    /* collect all data */
    $dataPerType = [];
    foreach (['post_types', 'taxonomies'] as $component)
        foreach ($oes->$component as $objectKey => $objectData) {
            $type = $objectData['type'] ?? 'index';
            $objectLabel = ($objectData['label'] ?? $objectKey);
            $objectData['key'] = $objectKey;
            $objectData['component'] = $component;
            $dataPerType[$type][$objectLabel . $objectKey] = $objectData;
        }

    $oesTypes = \OES\Model\get_schema_types();
    foreach ($oesTypes as $type => $label)
        if (isset($dataPerType[$type]) && !empty($dataPerType[$type])) {

            $data = $dataPerType[$type];
            ksort($data);
            $dataHTML = '';
            foreach ($data as $objectData) {
                $objectKey = $objectData['key'] ?? '';

                $dataHTML .= '<tr>' .
                    '<th><strong>' .
                    oes_get_html_anchor('<strong>' . ($objectData['label'] ?? $objectKey) . '</strong>',
                        admin_url('admin.php?page=oes_settings_schema&tab=schema&type=oes'.
                            '&component=' . $objectData['component'] .
                            '&object=' . $objectKey)
                    ) .
                    '</strong>' .
                    '<code class="oes-object-identifier">' . $objectKey . '</code>' .
                    '</th>' .
                    '</tr>';
            }

            if (!empty($dataHTML))
                echo '<thead>' .
                    '<tr class="oes-config-table-separator">' .
                    '<th><strong>' . ($type == 'other' ? '[Default]' : $label) . '</strong></th>' .
                    '</tr>' .
                    '</thead>' .
                    '<tbody>' .
                    $dataHTML .
                '</tbody>';
        }
?></table>