<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Theme_Labels')) :

    /**
     * Class Theme_Labels
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Labels extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                sprintf(__('Your website will render pages and post objects according to your selected theme. The theme ' .
                    'includes templates that define display and the appearance of your encyclopedia. You can ' .
                    'customize the theme using the %sWordPress customization options%s (e.g. add navigation menus).',
                    'oes'),
                    '<a href="' . admin_url('themes.php') . '">',
                    '</a>'
                ) . '<br>' .
                __('If you are using an OES theme you can define labels for the templates that will be rendered ' .
                    'on certain part of the pages or for specific languages if you are using the OES feature ' .
                    '<b>Bilingualism</b>. Most of the labels are defined by your OES project plugin.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $this->table_title = __('Labels', 'oes');
            $this->prepare_language_form();
            $this->prepare_theme_label_general_form();
            $this->prepare_theme_label_form();
        }


        /**
         * Prepare form for language configuration.
         */
        function prepare_language_form()
        {

            /* get global OES instance */
            $oes = OES();

            $tableDataHead = ['',
                '<strong>' . __('Language name', 'oes') .
                '</strong><div>' . __('The name as displayed in theme', 'oes') . '</div>'];

            $tableBody = [];
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language)
                    $tableBody[] = [
                        '<code>' . $languageKey . '</code>' .
                        ($languageKey === 'language0' ? __(' (Primary Language)', 'oes') : ''),
                        oes_html_get_form_element('text',
                            'oes_config[languages][' . $languageKey . '][label]',
                            'oes_config-languages-' . $languageKey . '-label',
                            $language['label']
                        )
                    ];

            $this->table_data[] = [
                'title' => __('Languages', 'oes'),
                'table' => [[
                    'thead' => $tableDataHead,
                    'tbody' => $tableBody
                ]]
            ];
        }


        /**
         * Prepare form for general theme label configuration.
         */
        function prepare_theme_label_general_form()
        {

            /* get global OES instance */
            $oes = OES();

            /* prepare table head */
            $tableData = [];
            $tableDataHead = [
                __('Name', 'oes')
            ];
            foreach ($oes->languages as $language) $tableDataHead[] = '<strong>' . $language['label'] . '</strong>';


            /* get general theme labels */
            if (!empty($oes->theme_labels)) {

                $tableDataRows = [];
                foreach ($oes->theme_labels as $key => $label) {

                    /* prepare table body */
                    $tableDataRow = [
                        '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                        __('Location: ', 'oes') . '</em>' . $label['location'] . '</div>'
                    ];

                    $languages = array_keys($oes->languages);
                    foreach ($languages as $language)
                        $tableDataRow[] = oes_html_get_form_element('text',
                            'oes_config[theme_labels][' . $key . '][' . $language . ']',
                            'oes_config-theme_labels-' . $key . '-' . $language,
                            $label[$language] ?? '');

                    $tableDataRows[] = $tableDataRow;
                }

                /* add to return value */
                $tableData[] = [
                    'header' => __('General', 'oes'),
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRows
                ];
            }

            $this->table_data[] = [
                'title' => __('General', 'oes'),
                'table' => $tableData
            ];
        }


        /**
         * Prepare form for theme label configuration.
         */
        function prepare_theme_label_form()
        {

            /* get global OES instance */
            $oes = OES();

            /* get theme labels for post types -----------------------------------------------------------------------*/
            foreach ($oes->post_types as $postTypeKey => $postType)
                $this->add_table('post_types', $postTypeKey, $postType);


            /* get theme labels for taxonomies -----------------------------------------------------------------------*/
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                $this->add_table('taxonomies', $taxonomyKey, $taxonomy);


            /* get theme labels for media ----------------------------------------------------------------------------*/

            /* prepare table head */
            $tableDataHead = [''];
            foreach ($oes->languages as $language) $tableDataHead[] = '<strong>' . $language['label'] . '</strong>';

            /* get image fields */
            $fields = array_merge([
                'title' => ['key' => 'title', 'label' => 'Title'],
                'alt' => ['key' => 'alt', 'label' => 'Alternative Text'],
                'caption' => ['key' => 'caption', 'label' => 'Caption'],
                'description' => ['key' => 'description', 'label' => 'Description'],
                'date' => ['key' => 'date', 'label' => 'Publication Date']
            ], $oes->media_groups['image']['fields'] ?? []);

            /* get acf group image fields */
            $tableDataRows = [];
            foreach ($fields as $field) {

                /* prepare table body */
                $tableDataRow = [
                    '<strong>' . ($field['label'] ?? '') . '</strong>' .
                    '<code class="oes-object-identifier">' . $field['key'] . '</code>'
                ];

                $languages = array_keys($oes->languages);
                foreach ($languages as $language)
                    $tableDataRow[] = oes_html_get_form_element('text',
                        'oes_config[media][image][' . $field['key'] . '][labels][' . $language . ']',
                        'oes_config-media-image-' . $field['key'] . '-labels-' . $language,
                        $field['labels'][$language] ?? ''
                    );
                $tableDataRows[] = $tableDataRow;
            }

            /* add to return value */
            $this->table_data[] = [
                'title' => __('Media', 'oes'),
                'table' => [[
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRows
                ]]
            ];
        }


        /**
         * Add table for object.
         *
         * @param string $identifier Post types or taxonomies.
         * @param string $objectKey The post type key or the taxonomy key.
         * @param array $object The post type object or the taxonomy object.
         */
        function add_table(string $identifier, string $objectKey, array $object)
        {

            $oes = OES();

            /* prepare table head */
            $tableDataHead = [
                __('Name', 'oes')
            ];
            foreach ($oes->languages as $language) $tableDataHead[] = $language['label'];

            /* prepare table body */
            $tableDataRowsGeneral = $tableDataRowsSingle = $tableDataRowsArchive = $tableDataRowsFields = [];

            /* add post type label */
            $objectLabelRow = [];
            $objectLabelRow[] = '<div><strong>' . __('Label', 'oes') . '</strong>';

            $languages = array_keys($oes->languages);
            foreach ($languages as $language)
                $objectLabelRow[] = oes_html_get_form_element('text',
                    $identifier . '[' . $objectKey . '][oes_args][label_translations][' . $language . ']',
                    $identifier . '-' . $objectKey . '-oes_args-label_translations-' . $language,
                    $object['label_translations'][$language] ?? '');
            $tableDataRowsGeneral[] = $objectLabelRow;


            /* add field labels */
            if (isset($object['field_options']) && !empty($object['field_options']))
                foreach ($object['field_options'] as $fieldKey => $field)
                    if (isset($field['type']) && !in_array($field['type'], ['tab', 'message'])) {

                        $tableDataRow = ['<div><strong>' . ($field['label'] ?? $fieldKey) . '</strong>' .
                            '<code class="oes-object-identifier">' . $fieldKey . '</code></div>'];

                        $languages = array_keys($oes->languages);
                        foreach ($languages as $language)
                            $tableDataRow[] = oes_html_get_form_element('text',
                                'fields[' . $objectKey . '][' . $fieldKey . '][label_translation_' .
                                $language . ']',
                                'fields-' . $objectKey . '-' . $fieldKey . '-label_translation_' . $language,
                                $field['label_translation_' . $language] ?? ($field['label'] ?? $fieldKey)
                            );

                        /* add to related table */
                        $tableDataRowsFields[] = $tableDataRow;
                    }


            /* add other labels */
            if (isset($object['theme_labels']) && !empty($object['theme_labels']))
                foreach ($object['theme_labels'] as $key => $label) {

                    $tableDataRow = [
                        '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                        __('Location: ', 'oes') . '</em>' . $label['location'] . '</div>'
                    ];


                    $languages = array_keys($oes->languages);
                    foreach ($languages as $language)
                        $tableDataRow[] = oes_html_get_form_element('text',
                            $identifier . '[' . $objectKey . '][oes_args][theme_labels][' . $key . '][' .
                            $language . ']',
                            $identifier . '-' . $objectKey . '-oes_args-theme_labels-' . $key . '-' . $language,
                            $label[$language] ?? '');

                    /* add to related table */
                    if (oes_starts_with($key, 'archive__')) $tableDataRowsArchive[] = $tableDataRow;
                    elseif (oes_starts_with($key, 'single__')) $tableDataRowsSingle[] = $tableDataRow;
                    else $tableDataRowsGeneral[] = $tableDataRow;
                }

            /* prepare table */
            $table = [];
            if (!empty($tableDataRowsGeneral))
                $table[] = [
                    'header' => __('General', 'oes'),
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRowsGeneral
                ];
            if (!empty($tableDataRowsSingle))
                $table[] = [
                    'header' => __('Single View', 'oes'),
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRowsSingle
                ];
            if (!empty($tableDataRowsArchive))
                $table[] = [
                    'header' => __('Archive View', 'oes'),
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRowsArchive
                ];
            if (!empty($tableDataRowsFields))
                $table[] = [
                    'header' => __('Fields', 'oes'),
                    'thead' => $tableDataHead,
                    'tbody' => $tableDataRowsFields
                ];

            /* add to return value */
            $this->table_data[] = [
                'type' => 'accordion',
                'title' => $object['label'] . '<code class="oes-object-identifier">' . $objectKey . '</code>',
                'table' => $table
            ];

        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels', 'theme-labels');
endif;