<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('OES Reading Settings (Theme)', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-8" aria-label="Secondary menu"><?php

        $tabs = [
            'theme-languages' => __('Languages', 'oes'),
            'theme-index-pages' => __('Index', 'oes'),
            'theme-search' => __('Search', 'oes'),
            'theme-date' => __('Date Format', 'oes'),
            'theme-media' => __('Media', 'oes')
        ];

        if(!OES()->block_theme){
            $tabs['theme-colors'] = __('Colors', 'oes');
            $tabs['theme-logos'] = __('Logos', 'oes');
        }

        $tabs['info'] = __('Info', 'oes');

        foreach ($tabs as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings_reading&tab=' . $tab),
            ((($_GET['tab'] ?? 'theme-languages') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php

    if(isset($_GET['tab']) && $_GET['tab'] == 'info'):?>
        <p><?php
            printf(__("This is a settings page generated by the OES Core Plugin where you can administer settings for " .
                "OES features. To administer WordPress reading settings go %shere%s.", 'oes'),
                '<a href="' . admin_url('options-reading.php') . '">',
                '</a>'
            ); ?>
        </p>
        <p><?php printf(__('The reading settings manage the display and behaviour of the frontend (your website as ' .
                'displayed by the selected theme, learn more about themes in the %sWordPress manual%s). ' .
                'Most of the settings affect only the OES theme or a derivative of it and will have no impact ' .
                'on other themes. ', 'oes'),
                '<a href="https://developer.wordpress.org/themes/getting-started/what-is-a-theme/" target="_blank">',
                '</a>');
            echo '<br>';
            _e('A post object can be displayed according to its post type on the frontend as a <b>single page</b>, as a ' .
                '<b>search result</b> or as ' .
                'part of a WordPress <b>archive</b> listing all posts of a specific post type. ' .
                'For instance, the post type “article” ' .
                'can be displayed in full on one page (the single view), '.
                'can be referred to as the result of a search (the search view), or can be listed in an ' .
                'overview of all articles of a reference work (the archive view).', 'oes'); ?></p>
    <?php
    else: \OES\Admin\Tools\display($_GET['tab'] ?? 'theme-languages');
    endif;

    ?>
</div>