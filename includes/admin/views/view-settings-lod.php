<h1><?php _e('OES Linked Open Data Settings', 'oes'); ?></h1>
<p><?php _e('Linked open data (LOD) describes structured data that is interlinked with other data and is ' .
        'used through semantic queries. The OES feature <b>Linked Open Data</b> enables the search in ' .
        'databases, e.g. authority files such as GND and GeoNames, that store LOD data.', 'oes');?></p>
<div class="oes-settings-nav-tabs-container">
    <ul class="oes-settings-nav-tabs"><?php

        $navtabs = [
            'lod' => 'General'
        ];

        $oes = OES();
        if(!empty($oes->apis))
            foreach($oes->apis as $apiKey => $apiData) $navtabs[$apiKey] = $apiData->label;

        foreach($navtabs as $navtabSlug => $navTabLabel)
            echo '<li><a href="' .  admin_url('admin.php?page=oes_settings_lod&select=' . $navtabSlug) .
                '" class="' . ((isset($_GET['select']) || $navtabSlug !== 'lod') ? '': 'active') . '">' . $navTabLabel . '</a></li>';
        ?>
    </ul>
</div>
<div class="oes-settings-nav-tabs-panel"><div class="oes-pb-0-5 oes-form-wrapper-small"><?php
        \OES\Admin\Tools\display_tool($_GET['select'] ?? 'lod');
        ?></div></div>