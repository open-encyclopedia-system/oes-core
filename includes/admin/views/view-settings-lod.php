<h1><?php _e('OES Linked Open Data Settings', 'oes'); ?></h1>
<p><?php _e('Linked open data (LOD) describes structured data that is interlinked with other data and is ' .
        'used through semantic queries. The OES feature <b>Linked Open Data</b> enables the search in ' .
        'databases, e.g. authority files such as GND and GeoNames, that store LOD data.', 'oes');?></p>
<div class="oes-form-wrapper-small"><?php \OES\Admin\Tools\display_tool('lod'); ?></div>
<div class="oes-form-wrapper-samll"><?php \OES\Admin\Tools\display_tool('lod-options'); ?></div>