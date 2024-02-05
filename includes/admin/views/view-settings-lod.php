<?php

/* prepare tabs */
$tabs['info'] = __('Info', 'oes');
$oes = OES();
if(!empty($oes->apis)) foreach($oes->apis as $apiKey => $apiData) $tabs[$apiKey] = $apiData->label;

?><div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Linked Open Data Settings', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-<?php echo sizeof($tabs); ?>" aria-label="Secondary menu"><?php

        foreach ($tabs as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings_lod&tab=' . $tab),
            ((($_GET['tab'] ?? 'info') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php

    if(!isset($_GET['tab']) || $_GET['tab'] == 'info'):?>
        <p><?php _e('Linked open data (LOD) describes structured data that is interlinked with other data and is ' .
                'used through semantic queries. The OES feature <b>Linked Open Data</b> enables the search in ' .
                'databases, e.g. authority files such as GND and GeoNames, that store LOD data.', 'oes');?></p>
    <?php
    else: \OES\Admin\Tools\display($_GET['tab'] ?? 'lod');
    endif;

    ?>
</div>