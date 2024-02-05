<div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php _e('Open Encyclopedia System', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-7" aria-label="Secondary menu"><?php

        foreach ([
                     'general' => __('General', 'oes'),
                     'support' => __('Support', 'oes'),
                     'configuration' => __('Configuration', 'oes')
                 ] as $tab => $label) printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings&tab=' . $tab),
            ((($_GET['tab'] ?? 'general') == $tab) ? 'active' : ''),
            $label
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php

    if (!isset($_GET['tab']) || $_GET['tab'] == 'general'):?>
        <div class="oes-tool-information-wrapper">
        <p><?php
            _e('The Open Encyclopedia System (OES) is an open source online platform for building and maintaining ' .
                'online encyclopaedias in the fields of humanities and social sciences that provide readers with free and ' .
                'unrestricted online access to scientific content (Open Access). OES is a digital framework that hosts a ' .
                'collection of customisable workflow management and editing tools to create, publish and maintain academic ' .
                'reference works. Our claim is to create a system for the users, in which collaboration and editorial workflow ' .
                'management is easy and web-based. The OES software is customisable, flexible and adaptable to context ' .
                'applications as well as simple to operate. Ultimately, we aim to set a standard for a ' .
                'sustainable framework for this publication type.', 'oes'); ?>
        </p>
        <p><?php printf(__('For more information please visit our %swebsite%s.', 'oes'),
                '<a href="http://www.open-encyclopedia-system.org/" target="_blank">', '</a>'); ?>
        </p>
        </div><?php
    elseif ($_GET['tab'] == 'support'):?>
        <div class="oes-tool-information-wrapper">
        <p><?php printf(__('OES is a free and open source software. The current stable version of OES is 2.0 ' .
                '(release date: 31.01.2022). You can download the sources from our %swebsite%s. ' .
                'The sources include an installation guide.', 'oes'),
                '<a href="http://www.open-encyclopedia-system.org/about-OES/using_oes/index.html" target="_blank">',
                '</a>'); ?>
        </p>
        <p><?php printf(__('Support is currently provided via our email help desk %s. We answer questions related to ' .
                'the OES plugin and its usage. For further information about online encyclopaedias and possible ' .
                'customisations please visit our %swebsite%s.', 'oes'),
                '<a href="mailto:info@open-encyclopedia-system.org">info@open-encyclopedia-system.org</a>',
                '<a href="http://www.open-encyclopedia-system.org/" target="_blank">',
                '</a>'); ?>
        </p>
        </div><?php
    elseif ($_GET['tab'] == 'configuration'):?>
        <div class="oes-tool-information-wrapper">
        <p><?php _e('OES provides functionalities for custom WordPress post types and taxonomies that are part of an ' .
                'OES Project Plugin. OES post types can be configured in the backend or the editorial layer ' .
                '(WordPress administration GUI, /wp-admin). A configuration in the backend is to be preferred, ' .
                'but in some circumstances you might prefer a configuration via the editorial layer, for instance ' .
                'if you want to enable OES features; design, test or export configurations without editing the source code itself. ' .
                'No programming skills are required to do this. You might, however, need some understanding of ' .
                'WordPress and post types (or do it the old trial-and-error way). A complete user guide is coming soon.',
                'oes'); ?>
        </p>
        <p><?php
            printf(
                    __('The OES settings extend the WordPress settings. Look %shere%s for the WordPress settings.',
                        'oes'),
                '<a href="' . admin_url('options-general.php') . '">',
                '</a>'
            );
            ?>
        </p>
        </div><?php
    endif; ?></div>