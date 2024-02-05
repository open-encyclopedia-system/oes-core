<div class="oes-factory-notice notice notice-warning">
    <p><?php
        printf(__('If you want to use double quotes use the unicode notation &#8220; (%s) or &#8222; (%s).', 'oes'),
            htmlspecialchars('&#8220;'),
            htmlspecialchars('&#8222;')) ?></p>
</div>
<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Labels', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-7" aria-label="Secondary menu"><?php

        foreach ([
                     'theme-labels-general' => __('General', 'oes'),
                     'theme-labels-media' => __('Media', 'oes'),
                     'theme-labels-objects' => __('Objects', 'oes'),
                     'admin-info' => __('Info', 'oes')
                 ] as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings_labels&tab=' . $tab),
            ((($_GET['tab'] ?? 'theme-labels-general') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php
    if (isset($_GET['tab']) && $_GET['tab'] == 'admin-info'):?>
        <div class="oes-tool-information-wrapper"><p><?php
                print(__('If you are using an OES theme you can define labels for the templates that will be rendered ' .
                    'on certain part of the pages or for specific languages if you are using the OES feature ' .
                    '<b>Bilingualism</b>. Most of the labels are defined by your OES project plugin.', 'oes'));
                ?></p></div>
        <div class="oes-tool-information-wrapper"><p><?php
            print(__('The following labels will affect the labels of custom post types, custom taxonomies and their ' .
                'fields inside the frontend layer. Some labels will overwrite the labels defined for the editorial ' .
                'layer (admin labels).',
                'oes')
            ); ?></p></div><?php
    else: \OES\Admin\Tools\display($_GET['tab'] ?? 'theme-labels-general');
    endif;
    ?>
</div>