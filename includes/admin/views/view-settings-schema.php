<?php
if(($_GET['type'] ?? false) === 'oes_single'):?>
    <div class="oes-factory-notice notice notice-warning">
        <p><?php
            printf(__('If you want to use double quotes use the unicode notation &#8220; (%s) or &#8222; (%s).', 'oes'),
                htmlspecialchars('&#8220;'),
                htmlspecialchars('&#8222;')) ?></p>
    </div>
<?php
endif;
?><div class="oes-page-header-wrapper">
    <div class="oes-page-header">
        <h1><?php echo __('Schema', 'oes'); ?></h1>
    </div>
    <nav class="oes-tabs-wrapper hide-if-no-js tab-count-8" aria-label="Secondary menu"><?php

        $oes = OES();
        $type = isset($_GET['type']) && isset($oes->admin_tools['schema-' . $_GET['type']]) && $_GET['type'];
        $tab = $_GET['tab'] ?? false;

        printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings_schema'),
            ($tab ? '' : 'active'),
            __('Status', 'oes')
        );

        printf('<a href="%s" class="oes-tab %s">%s</a>',
            admin_url('admin.php?page=oes_settings_schema&tab=info'),
            (($tab == 'info') ? 'active' : ''),
            __('Info', 'oes')
        );
        ?>
    </nav>
</div>
<div class="oes-page-body"><?php

    if ($tab == 'info'):
    echo '<p>' .
        __('The OES schema defines the data schema and its representation of text objects, their properties ' .
            'and relationships ' .
            'between objects. The defined schema option for an object are used for the frontend representation.',
            'oes') .
        '</p>';
        echo '<h2>' . __('Types', 'oes') . '</h2>';
        echo '<p>' .
            __('The OES schema is divided into different object types: <b>Content</b> are post objects with ' .
                'scientific texts ' .
                'whose text bodies are enriched and classified by metadata. <b>Contributors</b> are post object types ' .
                'that represent the authorship of articles. <b>Index</b> elements are post objects that form the ' .
                'index of the collected articles. Other post objects that are only used for the searchability ' .
                'or structuring of other objects are referred to as <b>internal</b> elements.', 'oes') .
            '</p>';
        echo '<h3>' . __('Single', 'oes') . '</h3>';
        echo '<div class="oes-tool-information-wrapper"><p>' .
            __('A single post object can be displayed as a single page.', 'oes') .
            '</p><p>' .
            __('You can choose the field that will be displayed as title of a post object with the OES feature ' .
                '<b>Titles</b> (The field to be displayed as title on the single page).', 'oes') . '<br>' .
            __('The single view of a post object includes a table of metadata. You can define which post data ' .
                'is to be considered as metadata in the OES feature <b>Metadata</b>.', 'oes') .
            '</p></div>';
        echo '<h3>' . __('Archive', 'oes') . '</h3>';
        echo '<div class="oes-tool-information-wrapper"><p>' .
            __('All post objects of an object type can be displayed as an archive on a single page.', 'oes') .
            '</p><p>' .
            __('You can choose the field that will be displayed as title of a post object with the OES feature ' .
                '<b>Titles</b>. You can also choose which field will be used for sorting the list of post objects ' .
                'alphabetically.', 'oes') . '<br>' .
            __('The post type parameter <b>Has Archive</b> enables the archive view inside the frontend layer.',
                'oes') . '<br>' .
            __('When the OES feature <b>Display archive as list</b> is enabled the archive will not be displayed ' .
                'as list of posts linking to the single view but instead as list of all posts including the post ' .
                'content without the single view option.', 'oes') . '<br>' .
            __('You can define data to be included on the archive page in a ' .
                'dropdown table in the OES feature <b>Archive</b>. The OES feature <b>Archive Filter</b> ' .
                'enables considered facet filters for the archive page.', 'oes') .
            '</p></div>';
    elseif ($type): \OES\Admin\Tools\display('schema-' . $_GET['type']);
    else: oes_get_view('view-settings-schema-status');
    endif; ?>
</div>