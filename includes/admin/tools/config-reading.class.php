<?php

namespace OES\Admin\Tools;

use function OES\ACF\get_all_object_fields;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Reading')) :

    /**
     * Class Reading
     *
     * Implement the config tool for theme configurations.
     */
    class Reading extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Notes</b> enables the display of the notes header in the table of contents ' .
                    'inside the frontend display of a post object.', 'oes') . '<br>' .
                __('You can administrate the OES feature <b>Index</b> ' .
                    'by defining an index page on which post types will be displayed that are connected to the main ' .
                    'post type (usually a type of article). The index post types will include a display of their ' .
                    'connection inside the encyclopedia on the frontend single display.', 'oes') . '<br>' .
                __('You can choose which field will be displayed as title of a post object with the OES feature ' .
                    '<b>Titles</b>.', 'oes') . '<br>' .
                __('The single view of a post object includes a table of metadata. You can define which post data ' .
                    'is to be considered as metadata in the OES feature <b>Metadata</b>.', 'oes') . '<br>' .
                __('When displaying an archive view of a post type you can define data to be included in a ' .
                    'dropdown table in the OES feature <b>Archive</b>.', 'oes') . '<br>' .
                __('The OES feature <b>Media</b> allows you to define the display behaviour of images in the ' .
                    'frontend.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $this->table_title = __('General', 'oes');
            $this->prepare_notes_form();
            $this->prepare_index_form();
            $this->prepare_title_form();
            $this->prepare_metadata_and_archive_form();
            $this->prepare_search_form();
            $this->prepare_image_form();
            $this->prepare_language_form();
        }


        /**
         * Prepare form for notes configuration.
         */
        function prepare_notes_form()
        {

            /* get global OES instance */
            $oes = OES();

            $tableData = [];
            if (!empty($oes->notes)) {

                $tableData[] = [
                    'header' => 'Notes',
                    'transpose' => true,
                    'thead' => [
                        '<strong>' . __('Exclude from Table of Contents', 'oes') .
                        '</strong><div>' . __('Do not include the heading in the table of contents.', 'oes') . '</div>',
                        '<strong>' . __('Add Number to Heading', 'oes') .
                        '</strong><div>' . __('Add number in front of heading (automatically incremented).', 'oes') .
                        '</div>'
                    ],
                    'tbody' => [[
                        oes_html_get_form_element('checkbox',
                            'oes_config[notes][exclude_from_toc]',
                            'oes_config-notes-exclude_from_toc',
                            $oes->notes['exclude_from_toc'] ?? ''),
                        oes_html_get_form_element('checkbox',
                            'oes_config[notes][add_number]',
                            'oes_config-notes-add_number',
                            $oes->notes['add_number'] ?? '')
                    ]]
                ];
            }

            $this->table_data[] = [
                'title' => __('Notes', 'oes'),
                'table' => $tableData
            ];
        }


        /**
         * Prepare form for index configuration.
         */
        function prepare_index_form()
        {

            /* get global OES instance */
            $oes = OES();

            $tableData = [];
            if (!empty($oes->theme_index)) {

                $elementOptions = $objectOptions = ['none' => '-'];
                foreach ($oes->post_types as $key => $postType) {
                    $elementOptions[$key] = $postType['label'];
                    $objectOptions[$key] = $postType['label'];
                }
                foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                $tableData[] = [
                    'header' => 'Index',
                    'transpose' => true,
                    'thead' => [
                        '<strong>' . __('Label', 'oes') .
                        '</strong><div>' . __('Label of the Index Page', 'oes') . '</div>',
                        '<strong>' . __('Considered Object', 'oes') .
                        '</strong><div>' . __('Considered Object for Index (Nachweis)).', 'oes') . '</div>',
                        '<strong>' . __('Page Slug', 'oes') .
                        '</strong><div>' .
                        sprintf(__('Slug for the Index Page (relative to %s/).', 'oes'), get_site_url()) . '</div>',
                        '<strong>' . __('Objects', 'oes') .
                        '</strong><div>' . __('Objects on the Index Page.', 'oes') . '</div>'
                    ],
                    'tbody' => [[
                        oes_html_get_form_element('text',
                            'oes_config[theme_index][label]',
                            'oes_config-theme-index-label',
                            $oes->theme_index['label'] ?? ''),
                        oes_html_get_form_element('select',
                            'oes_config[theme_index][element]',
                            'oes_config-theme-index-element',
                            $oes->theme_index['element'] ?? '',
                            ['options' => $elementOptions]),
                        oes_html_get_form_element('text',
                            'oes_config[theme_index][slug]',
                            'oes_config-theme-index-slug',
                            $oes->theme_index['slug'] ?? 'index'),
                        oes_html_get_form_element('select',
                            'oes_config[theme_index][objects]',
                            'oes_config-theme-index-objects',
                            $oes->theme_index['objects'] ?? [],
                            ['options' => $objectOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                'reorder' => true])
                    ]]
                ];
            }

            $this->table_data[] = [
                'title' => __('Index', 'oes'),
                'table' => $tableData
            ];
        }


        /**
         * Prepare form for title configuration.
         */
        function prepare_title_form()
        {

            /* get global OES instance */
            $oes = OES();

            $tableDataHead = [__('', 'oes'),
                '<strong>' . __('Title for single display', 'oes') .
                '</strong><div>' . __('The field to be displayed as title on the single page', 'oes') . '</div>',
                '<strong>' . __('Title for list display', 'oes') .
                '</strong><div>' . __('The field to be displayed as title on archive pages', 'oes') . '</div>',
                '<strong>' . __('Sorting title for list display', 'oes') .
                '</strong><div>' . __('The field to be sorted after on archive pages', 'oes') . '</div>'];

            $tableBody = [];

            /* post types --------------------------------------------------------------------------------------------*/
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* get all fields for this post type */
                $allFields = get_all_object_fields($postTypeKey, false, false);

                /* prepare html for title options */
                $titleOptions = $titleOptionsNull = [];
                $titleOptionsNull['default'] = __('Same as Title', 'oes');
                $titleOptions['wp-title'] = __('Post Title (WordPress)', 'oes');
                $archiveFilterOptions['alphabet'] = __('Alphabet', 'oes');
                foreach ($allFields as $fieldKey => $singleField)
                    if ($singleField['type'] == 'text') $titleOptions[$fieldKey] = $singleField['label'];

                $tableBody[] = [
                    '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                    '<code class="oes-object-identifier">' . $postTypeKey . '</code>',
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][oes_args][display_titles][title_display]',
                        'post_types-' . $postTypeKey . '-oes_args-display_titles-title_display',
                        $postTypeData['display_titles']['title_display'] ?? 'default',
                        ['options' => $titleOptions]),
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][oes_args][display_titles][title_archive_display]',
                        'post_types-' . $postTypeKey . '-oes_args-display_titles-title_archive_display',
                        $postTypeData['display_titles']['title_archive_display'] ?? 'default',
                        ['options' => array_merge($titleOptionsNull, $titleOptions)]),
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][oes_args][display_titles][title_sorting_display]',
                        'post_types-' . $postTypeKey . '-oes_args-display_titles-title_sorting_display',
                        $postTypeData['display_titles']['title_sorting_display'] ?? 'default',
                        ['options' => array_merge($titleOptionsNull, $titleOptions)])
                ];
            }


            $this->table_data[] = [
                'title' => __('Titles', 'oes'),
                'table' => [[
                    'thead' => $tableDataHead,
                    'tbody' => $tableBody
                ]]
            ];
        }


        /**
         * Prepare form for metadata and archive configuration.
         */
        function prepare_metadata_and_archive_form()
        {

            /* get global OES instance */
            $oes = OES();

            $thead = $tbodyMetadata = $tbodyArchive = [];
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* get post type object */
                $postTypeObject = get_post_type_object($postTypeKey);

                /* prepare options */
                $options = [];
                $postTypesRelationships = [];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message'])) {
                            $options[$fieldKey] = $field['label'];
                            if (in_array($field['type'], ['relationship', 'post_object'])) {
                                $checkForPostTypes = get_field_object($fieldKey)['post_type'] ?? [];
                                if(is_string($checkForPostTypes)) $checkForPostTypes = [$checkForPostTypes];
                                if (!empty($checkForPostTypes))
                                    foreach ($checkForPostTypes as $singlePostType)
                                        $postTypesRelationships['post_type__' . $singlePostType] =
                                            __('Post Type: ', 'oes') .
                                            (isset($oes->post_types[$singlePostType]['label']) ?
                                                $oes->post_types[$singlePostType]['label'] :
                                                $singlePostType);
                            }
                        }
                if(!empty($postTypesRelationships)) $options = array_merge($options, $postTypesRelationships);


                /* add taxonomies */
                if (get_post_type_object($postTypeKey)->taxonomies)
                    foreach (get_post_type_object($postTypeKey)->taxonomies as $taxonomy)
                        $options['taxonomy__' . $taxonomy] = __('Taxonomy: ', 'oes') .
                            ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);

                /* add parent options */
                if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                    isset($oes->post_types[$postTypeData['parent']]['field_options']) &&
                    !empty($oes->post_types[$postTypeData['parent']]['field_options']))
                    foreach ($oes->post_types[$postTypeData['parent']]['field_options'] as $parentFieldKey => $parentField)
                        if (isset($parentField['type']) && !in_array($parentField['type'], ['tab', 'message']))
                            $options['parent__' . $parentFieldKey] = __('Parent: ', 'oes') . $parentField['label'];

                /* add parent taxonomies */
                if (isset($postTypeData['parent']) && $postTypeData['parent'] &&
                    get_post_type_object($postTypeData['parent'])->taxonomies)
                    foreach (get_post_type_object($postTypeData['parent'])->taxonomies as $taxonomy)
                        $options['parent_taxonomy__' . $taxonomy] = __('Parent Taxonomy: ', 'oes') .
                            ($oes->taxonomies[$taxonomy]['label'] ?? $taxonomy);

                /* add alphabet for archive filter */
                $optionsArchiveFilter = array_merge(['alphabet' => 'Alphabet'], $options);

                $thead[] = '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                    '<code class="oes-object-identifier">' . $postTypeKey . '</code>';
                $tbodyMetadata[] = oes_html_get_form_element('select',
                    'post_types[' . $postTypeKey . '][oes_args][metadata]',
                    'post_types-' . $postTypeKey . '-oes_args-metadata',
                    $postTypeData['metadata'] ?? [],
                    ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2', 'reorder' => true]);


                $tbodyArchive[] = [
                    'header' => '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                        '<code class="oes-object-identifier">' . $postTypeKey . '</code>',
                    'transpose' => true,
                    'thead' => [
                        '<strong>' . __('Has Archive', 'oes') .
                        '</strong><div>' . __('Archive is available in frontend', 'oes') . '</div>',
                        '<strong>' . __('Display archive as list', 'oes') .
                        '</strong><div>' .
                        __('Display the archive as list, post type has no single pages (eg. glossary)', 'oes') . '</div>',
                        '<strong>' . __('Archive Data', 'oes') .
                        '</strong><div>' . __('Additional information on archive list view', 'oes') . '</div>',
                        '<strong>' . __('Archive Filter', 'oes') .
                        '</strong><div>' . __('Elements that will be considered for the archive filter', 'oes') . '</div>'
                    ],
                    'tbody' => [[
                        oes_html_get_form_element('checkbox',
                            'post_types[' . $postTypeKey . '][register_args][has_archive]',
                            'post_types-' . $postTypeKey . '-register_args-has_archive',
                            $postTypeObject->has_archive ?? false,
                            ['hidden' => true]),
                        oes_html_get_form_element('checkbox',
                            'post_types[' . $postTypeKey . '][oes_args][archive_on_single_page]',
                            'post_types-' . $postTypeKey . '-oes_args-archive_on_single_page',
                            $postTypeData['archive_on_single_page'] ?? false,
                            ['hidden' => true]),
                        oes_html_get_form_element('select',
                            'post_types[' . $postTypeKey . '][oes_args][archive]',
                            'post_types-' . $postTypeKey . '-oes_args-archive',
                            $postTypeData['archive'] ?? [],
                            ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2',
                                'reorder' => true, 'hidden' => true]),
                        oes_html_get_form_element('select',
                            'post_types[' . $postTypeKey . '][oes_args][archive_filter]',
                            'post_types-' . $postTypeKey . '-oes_args-archive_filter',
                            $postTypeData['archive_filter'] ?? [],
                            ['options' => $optionsArchiveFilter, 'multiple' => true, 'class' => 'oes-replace-select2',
                                'reorder' => true, 'hidden' => true])
                    ]]
                ];
            }

            $this->table_data[] = [
                'title' => __('Metadata', 'oes'),
                'table' => [[
                    'transpose' => true,
                    'thead' => $thead,
                    'tbody' => [$tbodyMetadata]
                ]]
            ];

            $this->table_data[] = [
                'type' => 'accordion',
                'title' => __('Archive', 'oes'),
                'table' => $tbodyArchive
            ];
        }


        /**
         * Prepare form for metadata and archive configuration.
         */
        function prepare_search_form()
        {
            /* get global OES instance */
            $oes = OES();

            /* add options for page */
            $thead = ['<strong>' . __('Page', 'oes') . '</strong>'];
            $tbody = [
                oes_html_get_form_element('select',
                    'oes_config[search][postmeta_fields][page]',
                    'oes_config-search-postmeta_fields-page',
                    $oes->search['postmeta_fields']['page'] ?? [],
                    ['options' => ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'],
                        'multiple' => true,
                        'class' => 'oes-replace-select2',
                        'reorder' => true])
            ];

            /* add options for post types */
            foreach ($oes->post_types as $postTypeKey => $postTypeData) {

                /* prepare options */
                $options = ['title' => 'Title (Display Title)', 'content' => 'Content (Editor)'];
                if (isset($postTypeData['field_options']) && !empty($postTypeData['field_options']))
                    foreach ($postTypeData['field_options'] as $fieldKey => $field)
                        if (isset($field['type']) && !in_array($field['type'], ['tab', 'message']))
                            $options[$fieldKey] = $field['label'];

                $thead[] = '<strong>' . ($postTypeData['label'] ?? $postTypeKey) . '</strong>' .
                    '<code class="oes-object-identifier">' . $postTypeKey . '</code>';
                $tbody[] = oes_html_get_form_element('select',
                    'oes_config[search][postmeta_fields][' . $postTypeKey . ']',
                    'oes_config-search-postmeta_fields-' . $postTypeKey,
                    $oes->search['postmeta_fields'][$postTypeKey] ?? [],
                    ['options' => $options, 'multiple' => true, 'class' => 'oes-replace-select2', 'reorder' => true]);
            }

            $this->table_data[] = [
                'title' => __('Search', 'oes'),
                'table' => [[
                    'transpose' => true,
                    'thead' => $thead,
                    'tbody' => [$tbody]
                ]]
            ];
        }


        /**
         * Prepare form for image configuration.
         */
        function prepare_image_form()
        {

            /* get global OES instance */
            $oes = OES();

            /* prepare image fields options */
            $fields = array_merge([
                'title' => ['key' => 'title', 'label' => 'Title'],
                'alt' => ['key' => 'alt', 'label' => 'Alternative Text'],
                'caption' => ['key' => 'caption', 'label' => 'Caption'],
                'description' => ['key' => 'description', 'label' => 'Description'],
                'date' => ['key' => 'date', 'label' => 'Publication Date']
            ], $oes->media_groups['fields'] ?? []);

            $fieldOptions = [];
            foreach ($fields as $key => $field) {
                $fieldKey = $field['key'] ?? $key;
                $fieldOptions[$fieldKey] = $field['label'] ?? $key;
            }


            /* add credit option */
            $table[] = [
                'header' => '<strong>' . __('General', 'oes') . '</strong>',
                'transpose' => true,
                'thead' => [
                    '<strong>' . __('Credit Label', 'oes') .
                    '</strong><div>' . __('The credit label is displayed beneath the image', 'oes') . '</div>',
                    '<strong>' . __('Credit field', 'oes') .
                    '</strong><div>' . __('The credit field and is displayed after the credit label', 'oes') . '</div>',
                    /*'<strong>' . __('Show in Subtitle', 'oes') .
                    '</strong><div>' . __('Fields that are shown in subtitle', 'oes') . '</div>',*/
                    '<strong>' . __('Show in Panel', 'oes') .
                    '</strong><div>' . __('Fields that are shown in panel', 'oes') . '</div>'
                ],
                'tbody' => [[
                    oes_html_get_form_element('text',
                        'oes_config[media][credit_text]',
                        'oes_config-media-credit_text',
                        $oes->media_groups['credit_text'] ?? ''
                    ),
                    oes_html_get_form_element('select',
                        'oes_config[media][credit_label]',
                        'oes_config-media-credit_label',
                        $oes->media_groups['credit_label'] ?? 'none',
                        ['options' => array_merge(['none' => '-'], $fieldOptions)]),
                    /*oes_html_get_form_element('select',
                        'oes_config[media][show_in_subtitle]',
                        'oes_config-media-show_in_subtitle',
                        $oes->media_groups['show_in_subtitle'] ?? [],
                        ['options' => $fieldOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                            'reorder' => true]),*/
                    oes_html_get_form_element('select',
                        'oes_config[media][show_in_panel]',
                        'oes_config-media-show_in_panel',
                        $oes->media_groups['show_in_panel'] ?? [],
                        ['options' => $fieldOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                            'reorder' => true])
                ]]
            ];

            $this->table_data[] = [
                'title' => __('Media', 'oes'),
                'table' => $table
            ];
        }


        /**
         * Prepare form for language configuration (Choose a website language).
         */
        function prepare_language_form()
        {

            /* get global OES instance */
            $oes = OES();

            /* prepare languages */
            $languageOptions = [];
            if (!empty($oes->languages))
                foreach ($oes->languages as $languageKey => $language)
                    $languageOptions[$languageKey] = $language['label'];


            $tableData[] = [
                'transpose' => true,
                'thead' => [
                    '<strong>' . __('Main Language', 'oes') .
                    '</strong><div>' . __('OES can be displayed in different languages. ' .
                        'Navigation elements will be displayed in the chosen language. Other elements might be ' .
                        'displayed in a different language depending on implemented language switches ' .
                        '(e.g. language switch for articles).', 'oes') . '</div>'
                ],
                'tbody' => [[
                    oes_html_get_form_element('select',
                        'oes_config[main_language]',
                        'oes_config-main_language',
                        $oes->main_language ?? 'language0',
                        ['options' => $languageOptions]
                    )
                ]]
            ];

            $this->table_data[] = [
                'title' => __('Language', 'oes'),
                'table' => $tableData
            ];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Reading', 'reading');
endif;