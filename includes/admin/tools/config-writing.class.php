<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Writing')) :

    /**
     * Class Writing
     *
     * Implement the config tool for writing configurations.
     */
    class Writing extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The general writing configurations aim to improve the writing experience for the editorial team.<br>' .
                    'This includes the configuration of the OES feature <b>Container</b> which allows you to organize ' .
                    'admin menu items into a top menu with sub menu items and display currently worked on post ' .
                    'objects.<br>' .
                    'You can add or remove columns for the admin list views of post objects with the OES feature ' .
                    '<b>Admin Columns</b> and display information that helps you administrating and organizing your ' .
                    'post objects.<br>' .
                    'The OES feature <b>Inheritance</b> allows to define bidirectional relationships between post ' .
                    'objects ' .
                    '(e.g. if you connect an article object with an author the author object will also be connected ' .
                    'to the same article.)', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $this->table_title = __('General', 'oes');
            $this->prepare_container_form();
            $this->prepare_admin_columns_form();
            $this->prepare_inheritance();
        }


        /**
         * Prepare form for container configuration.
         */
        function prepare_container_form()
        {

            $tableDataHead = [
                '<strong>' . __('Name', 'oes') .
                '</strong><div>' . __('Name of the container as displayed in menu', 'oes') . '</div>',
                '<strong>' . __('Position', 'oes') .
                '</strong><div>' . __('Change the position in the editorial layer menu. Default on empty or 0 is ' .
                    'between 26 and 59.', 'oes') . '</div>',
                '<strong>' . __('Icon', 'oes') .
                '</strong><div>' .
                __('The menu icon. Valid options are: "default", "parent", "second", "admin" or a path.', 'oes') .
                '</div>',
                '<strong>' . __('Elements', 'oes') .
                '</strong><div>' . __('Elements included as submenu in this container.', 'oes') . '</div>',
                '<strong>' . __('Info Page Elements', 'oes') .
                '</strong><div>' . __('Elements displayed on an info page. Only elements that are set in the ' .
                    '"Elements" option can be displayed.', 'oes') . '</div>',
                '<strong>' . __('Info Page Text', 'oes') .
                '</strong><div>' . __('The menu name of the info page (if info page elements are set)', 'oes') . '</div>'
            ];

            /* get theme label configurations */
            $tableData = [];
            $oes = OES();
            if (!empty($oes->admin_pages['container']))
                foreach ($oes->admin_pages['container'] as $containerKey => $container) {

                    $containerName = $container['page_args']['menu_title'] ?? 'Recently worked on';

                    /* prepare elements */
                    $objectOptions = ['none' => '-'];
                    foreach ($oes->post_types as $key => $postType) {
                        $elementOptions[$key] = $postType['label'];
                        $objectOptions[$key] = $postType['label'];
                    }
                    foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                    $tableData[] = [
                        'header' => $containerName,
                        'transpose' => true,
                        'thead' => $tableDataHead,
                        'tbody' => [[
                            oes_html_get_form_element('text',
                                'oes_config[container][' . $containerKey . '][page_args][menu_title]',
                                'oes_config-container-' . $containerKey . '-page_args-menu_title',
                                $containerName),
                            oes_html_get_form_element('number',
                                'oes_config[container][' . $containerKey . '][page_args][position]',
                                'oes_config-container-' . $containerKey . '-page_args-position',
                                $container['page_args']['position'] ?? 20,
                                ['min' => 1, 'max' => 101]),
                            oes_html_get_form_element('text',
                                'oes_config[container][' . $containerKey . '][page_args][icon_url]',
                                'oes_config-container-' . $containerKey . '-page_args-icon_url',
                                $container['page_args']['icon_url'] ?? 'default'),
                            oes_html_get_form_element('select',
                                'oes_config[container][' . $containerKey . '][sub_pages]',
                                'oes_config-container-' . $containerKey . '-sub_pages',
                                $container['sub_pages'] ?? [],
                                ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                    'reorder' => true]),
                            oes_html_get_form_element('select',
                                'oes_config[container][' . $containerKey . '][info_page][elements]',
                                'oes_config-container-' . $containerKey . '-info_page-elements',
                                $container['info_page']['elements'] ?? [],
                                ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                    'reorder' => true]),
                            oes_html_get_form_element('text',
                                'oes_config[container][' . $containerKey . '][info_page][label]',
                                'oes_config-container-' . $containerKey . '-info_page-label',
                                $container['info_page']['label'] ?? 'Title missing')
                        ]]
                    ];
                }

            $this->table_data[] = [
                'type' => 'accordion',
                'title' => __('Container', 'oes'),
                'table' => $tableData
            ];


        }


        /**
         * Prepare form for admin columns configuration.
         */
        function prepare_admin_columns_form()
        {

            /* get global OES instance */
            $oes = OES();

            $thead = $tbody = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* prepare options */
                $options = ['cb' => 'Checkbox', 'title' => 'Title', 'date' => 'Date',
                    'date_modified' => 'Modified Date', 'parent' => 'Parent'];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message']))
                            $options[$fieldKey] = __('Field: ', 'oes') . $field['label'];

                /* check for taxonomies */
                foreach (get_post_type_object($postTypeKey)->taxonomies ?? [] as $taxonomyKey)
                    $options['taxonomy-' . $taxonomyKey] = $oes->taxonomies[$taxonomyKey]['label'];

                $thead[] = '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                    '<code class="oes-object-identifier">' . $postTypeKey . '</code>';
                $tbody[] = oes_html_get_form_element('select',
                    'post_types[' . $postTypeKey . '][oes_args][admin_columns]',
                    'post_types-' . $postTypeKey . '-oes_args-admin_columns',
                    $postTypeData['admin_columns'] ?? [],
                    ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2', 'reorder' => true]);
            }

            $this->table_data[] = [
                'title' => __('Admin Columns', 'oes'),
                'table' => [[
                    'header' => 'Admin Columns',
                    'transpose' => true,
                    'thead' => $thead,
                    'tbody' => [$tbody]
                ]]
            ];
        }


        /**
         * Prepare form for metadata and archive configuration.
         */
        function prepare_inheritance()
        {

            /* get global OES instance */
            $oes = OES();

            $table = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* loop through fields */
                $tbody = [];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['inherit_to_options']) && !empty($field['inherit_to_options']))
                            $tbody[] = [
                                '<div><strong>' . ($field['label'] ?? $fieldKey) . '</strong>' .
                                '<code class="oes-object-identifier">' . $fieldKey . '</code></div>',
                                oes_html_get_form_element('select',
                                    'fields[' . $postTypeKey . '][' . $fieldKey . '][inherit_to]',
                                    'fields-' . $postTypeKey . '-' . $fieldKey . '-inherit_to',
                                    $field['inherit_to'] ?? [],
                                    ['options' => $field['inherit_to_options'], 'multiple' => true,
                                        'class' => 'oes-replace-select2', 'reorder' => true]),
                            ];

                $table[] = [
                    'header' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                        '<code class="oes-object-identifier">' . $postTypeKey . '</code>',
                    'thead' => ['', __('<strong>inherits to:</strong>', 'oes')],
                    'tbody' => $tbody
                ];
            }

            $this->table_data[] = [
                'type' => 'accordion',
                'title' => __('Inheritance', 'oes'),
                'table' => $table
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Writing', 'writing');

endif;