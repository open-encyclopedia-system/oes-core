<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Export_Formats')) :

    /**
     * Class Export_Formats
     *
     * TODO @nextRelease : in development
     *
     * Implement the config tool for theme configurations.
     */
    class Export_Formats extends Config
    {

        const DUBLIN_PROPERTIES = [
            'abstract' => ['label' => 'Abstract'],
            'accessRights' => ['label' => 'Access Rights'],
            'accrualMethod' => ['label' => 'Accrual Method'],
            'accrualPeriodicity' => ['label' => 'Accrual Periodicity'],
            'accrualPolicy' => ['label' => 'Accrual Policy'],
            'alternative' => ['label' => 'Alternative Title'],
            'audience' => ['label' => 'Audience'],
            'available' => ['label' => 'Date Available'],
            'bibliographicCitation' => ['label' => 'Bibliographic Citation'],
            'conformsTo' => ['label' => 'Conforms To'],
            'contributor' => ['label' => 'Contributor'],
            'coverage' => ['label' => 'Coverage'],
            'created' => ['label' => 'Date Created'],
            'creator' => ['label' => 'Creator'],
            'date' => ['label' => 'Date'],
            'dateAccepted' => ['label' => 'Date Accepted'],
            'dateCopyrighted' => ['label' => 'Date Copyrighted'],
            'dateSubmitted' => ['label' => 'Date Submitted'],
            'description' => ['label' => 'Description'],
            'educationLevel' => ['label' => 'Audience Education Level'],
            'extent' => ['label' => 'Extent'],
            'format' => ['label' => 'Format'],
            'hasFormat' => ['label' => 'Has Format'],
            'hasPart' => ['label' => 'Has Part'],
            'hasVersion' => ['label' => 'Has Version'],
            'identifier' => ['label' => 'Identifier'],
            'instructionalMethod' => ['label' => 'Instructional Method'],
            'isFormatOf' => ['label' => 'Is Format Of'],
            'isPartOf' => ['label' => 'Is Part Of'],
            'isReferencedBy' => ['label' => 'Is Referenced By'],
            'isReplacedBy' => ['label' => 'Is Replaced By'],
            'isRequiredBy' => ['label' => 'Is Required By'],
            'issued' => ['label' => 'Date Issued'],
            'isVersionOf' => ['label' => 'Is Version Of'],
            'language' => ['label' => 'Language'],
            'license' => ['label' => 'License'],
            'mediator' => ['label' => 'Mediator'],
            'medium' => ['label' => 'Medium'],
            'modified' => ['label' => 'Date Modified'],
            'provenance' => ['label' => 'Provenance'],
            'publisher' => ['label' => 'Publisher'],
            'references' => ['label' => 'References'],
            'relation' => ['label' => 'Relation'],
            'replaces' => ['label' => 'Replaces'],
            'requires' => ['label' => 'Requires'],
            'rights' => ['label' => 'Rights'],
            'rightsHolder' => ['label' => 'Rights Holder'],
            'source' => ['label' => 'Source'],
            'spatial' => ['label' => 'Spatial Coverage'],
            'subject' => ['label' => 'Subject'],
            'tableOfContents' => ['label' => 'Table Of Contents'],
            'temporal' => ['label' => 'Temporal Coverage'],
            'title' => ['label' => 'Title'],
            'type' => ['label' => 'Type'],
            'valid' => ['label' => 'Date Valid'],
            'DCMIType' => ['label' => 'DCMI Type Vocabulary'],
            'DDC' => ['label' => 'DDC'],
            'IMT' => ['label' => 'IMT'],
            'LCC' => ['label' => 'LCC'],
            'LCSH' => ['label' => 'LCSH'],
            'MESH' => ['label' => 'MeSH'],
            'NLM' => ['label' => 'NLM'],
            'TGN' => ['label' => 'TGN'],
            'UDC' => ['label' => 'UDC'],
            'Box' => ['label' => 'DCMI Box'],
            'ISO3166' => ['label' => 'ISO 3166'],
            'ISO639-2' => ['label' => 'ISO 639-2'],
            'ISO639-3' => ['label' => 'ISO 639-3'],
            'Period' => ['label' => 'DCMI Period'],
            'Point' => ['label' => 'DCMI Point'],
            'RFC1766' => ['label' => 'RFC 1766'],
            'RFC3066' => ['label' => 'RFC 3066'],
            'RFC4646' => ['label' => 'RFC 4646'],
            'RFC5646' => ['label' => 'RFC 5646'],
            'URI' => ['label' => 'URI'],
            'W3CDTF' => ['label' => 'W3C-DTF'],
            'Agent' => ['label' => 'Agent'],
            'AgentClass' => ['label' => 'Agent Class'],
            'BibliographicResource' => ['label' => 'Bibliographic Resource'],
            'FileFormat' => ['label' => 'File Format'],
            'Frequency' => ['label' => 'Frequency'],
            'Jurisdiction' => ['label' => 'Jurisdiction'],
            'LicenseDocument' => ['label' => 'License Document'],
            'LinguisticSystem' => ['label' => 'Linguistic System'],
            'Location' => ['label' => 'Location'],
            'LocationPeriodOrJurisdiction' => ['label' => 'Location, Period, or Jurisdiction'],
            'MediaType' => ['label' => 'Media Type'],
            'MediaTypeOrExtent' => ['label' => 'Media Type or Extent'],
            'MethodOfAccrual' => ['label' => 'Method of Accrual'],
            'MethodOfInstruction' => ['label' => 'Method of Instruction'],
            'PeriodOfTime' => ['label' => 'Period of Time'],
            'PhysicalMedium' => ['label' => 'Physical Medium'],
            'PhysicalResource' => ['label' => 'Physical Resource'],
            'Policy' => ['label' => 'Policy'],
            'ProvenanceStatement' => ['label' => 'Provenance Statement'],
            'RightsStatement' => ['label' => 'Rights Statement'],
            'SizeOrDuration' => ['label' => 'Size or Duration'],
            'Standard' => ['label' => 'Standard'],
            'Collection' => ['label' => 'Collection'],
            'Dataset' => ['label' => 'Dataset'],
            'Event' => ['label' => 'Event'],
            'Image' => ['label' => 'Image'],
            'InteractiveResource' => ['label' => 'Interactive Resource'],
            'MovingImage' => ['label' => 'Moving Image'],
            'PhysicalObject' => ['label' => 'Physical Object'],
            'Service' => ['label' => 'Service'],
            'Software' => ['label' => 'Software'],
            'Sound' => ['label' => 'Sound'],
            'StillImage' => ['label' => 'Still Image'],
            'Text' => ['label' => 'Text'],
            'domainIncludes' => ['label' => 'Domain Includes'],
            'memberOf' => ['label' => 'Member Of'],
            'rangeIncludes' => ['label' => 'Range Includes'],
            'VocabularyEncodingScheme' => ['label' => 'Vocabulary Encoding Scheme']
        ];

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('Describe', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $this->table_title = __('XML', 'oes');
            $this->prepage_general_properties();
            $this->prepare_theme_label_general_form();
            $this->prepare_theme_label_form();
        }


        /**
         * Prepare form for language configuration.
         */
        function prepage_general_properties()
        {

            /* get global OES instance */
            $oes = OES();


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
                'title' => __('General', 'oes'),
                'table' => [[
                    'thead' => [],
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
            if (!empty($oes->Export_Formats)) {

                $tableDataRows = [];
                foreach ($oes->Export_Formats as $key => $label) {

                    /* prepare table body */
                    $tableDataRow = [
                        '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                        __('Location: ', 'oes') . '</em>' . $label['location'] . '</div>'
                    ];

                    $languages = array_keys($oes->languages);
                    foreach ($languages as $language)
                        $tableDataRow[] = oes_html_get_form_element('text',
                            'oes_config[Export_Formats][' . $key . '][' . $language . ']',
                            'oes_config-Export_Formats-' . $key . '-' . $language,
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
            if (isset($object['Export_Formats']) && !empty($object['Export_Formats']))
                foreach ($object['Export_Formats'] as $key => $label) {

                    $tableDataRow = [
                        '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                        __('Location: ', 'oes') . '</em>' . $label['location'] . '</div>'
                    ];


                    $languages = array_keys($oes->languages);
                    foreach ($languages as $language)
                        $tableDataRow[] = oes_html_get_form_element('text',
                            $identifier . '[' . $objectKey . '][oes_args][Export_Formats][' . $key . '][' .
                            $language . ']',
                            $identifier . '-' . $objectKey . '-oes_args-Export_Formats-' . $key . '-' . $language,
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
    register_tool('\OES\Admin\Tools\Export_Formats', 'export-formats');
endif;