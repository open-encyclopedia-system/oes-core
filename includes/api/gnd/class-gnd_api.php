<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('GND_API')) {

    /**
     * Class GND_API
     * Dienst des Hochschulbibliothekenzentrum des Landes NRW
     * https://github.com/hbz/lobid-gnd
     *
     * query terms like this : 'http://lobid.org/gnd/search?q=london'
     * https://lobid.org/gnd/api
     *
     */
    class GND_API extends Rest_API
    {

        /** @var string The url to the lobid api. */
        public string $url = 'http://lobid.org/gnd/';

        /** @var string|bool The html representation of an image (depiction) for a gnd entry. */
        public $imageHTML = false;

        /** @var array $types The GND types */
        public array $types = [];


        /** @inheritdoc */
        function get_request_url(string $url, array $args): string
        {
            $this->searchTerm = $args['search_term'] ?? '';

            /* prepare request url */
            if (!empty($this->searchTerm)) {

                /* add field filter */
                $fieldFilter = (($args['field'] && $args['field'] != 'any') ? $args['field'] . ':' : '');
                $url .= 'search?q=' . $fieldFilter . $this->searchTerm;

                /* prepare options */
                $options = [];
                if (isset($args['oes-gnd-size']))
                    $options['size'] = $args['oes-gnd-size'];
                if (isset($args['oes-gnd-type']) && $args['oes-gnd-type'] != 'all')
                    $options['filter'] = 'type:' . $args['oes-gnd-type'];

                /* add more filter */
                if (!empty($options)) foreach ($options as $key => $option) $url .= '&' . $key . '=' . $option;
            } elseif (isset($args['lodid'])) $url .= $args['lodid'] . '.json';

            return $url;
        }


        /** @inheritdoc */
        function transform_data($args = [])
        {

            /* exit early if no data */
            if (!$this->data || $this->request_error) return $this->request_error ?? 'Response seems to be empty.';

            $transformedData = [];
            foreach ($this->data->member ?? [$this->data] as $entryKey => $entry) {

                /* loop through data and prepare for display */
                $transformedDataEntry = [];
                $propertyLabel = GND_Interface::PROPERTIES;
                foreach ($entry as $propertyKey => $property) {

                    /* skip context  */
                    if ($propertyKey === '@context') continue;

                    /* add types */
                    if ($propertyKey === 'type') $this->types = $property;

                    /* add image */
                    if ($propertyKey === 'depiction') {

                        /* take first image @oesDevelopment Can there be more images? */
                        if ($property[0]->thumbnail)
                            $this->imageHTML = '<img src="' . $property[0]->thumbnail . '" alt="Depiction"/>';
                        continue;
                    }

                    /* prepare property position */
                    $position = 10000 + ($propertyLabel[$propertyKey]['position'] ?? 8000);
                    $value = '';
                    $raw = null;

                    if ($propertyKey === 'describedBy' && $property->license->id) {
                        $label = $this->language === 'german' ? 'Lizenz' : 'license';
                        $value = oes_get_html_anchor($property->license->label ?? $property->license->id,
                            $property->license->id, false, false, '_blank');
                        $raw = $property->license->label ?? $property->license->id;
                    } else {

                        /* get label */
                        if ($propertyKey == 'id') {
                            $label = $this->language === 'german' ? 'GND-ID' : 'GND ID';
                            $position = 10011;
                        } elseif ($propertyKey == 'type') {
                            $label = $this->language === 'german' ? 'Typ' : 'Type';
                            $position = 10030;
                        } elseif ($propertyKey == 'sameAs') {
                            $label = $this->language === 'german' ? 'Siehe auch' : 'See also';
                            $position = 18001;
                        } elseif ($propertyKey == 'hasParent') {
                            $label = $this->language === 'german' ? 'Eltern' : 'Parents';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasGrandParent') {
                            $label = $this->language === 'german' ? 'Großeltern' : 'Grand Parents';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasChild') {
                            $label = $this->language === 'german' ? 'Kinder' : 'Children';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasGrandChild') {
                            $label = $this->language === 'german' ? 'Enkel:innen' : 'Grand Children';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasSibling') {
                            $label = $this->language === 'german' ? 'Geschwister' : 'Siblings';
                            $position = 11220;
                        } else $label = $propertyLabel[$propertyKey]['label'][$this->language] ?? $propertyKey;

                        /* get value */
                        if (is_string($property)) {
                            $value = $raw = $property;
                            if ($propertyKey == 'id')
                                $value = oes_get_html_anchor($value, $value, false, false, '_blank');
                        } elseif (is_array($property)) {
                            $value = '<ul class="oes-field-value-list">';
                            foreach ($property as $singleProperty) {

                                if (is_string($singleProperty)) {
                                    if ($propertyKey == 'type') {
                                        $typeLabels = GND_Interface::TYPES;
                                        $value .= '<li>' .
                                        $typeLabels[$singleProperty]['labels'][$this->language] ?? $singleProperty .
                                        '</li>';
                                        $raw[] = $typeLabels[$singleProperty]['labels'][$this->language] ??
                                            $singleProperty;
                                    } else {
                                        $value .= '<li>' . $singleProperty . '</li>';
                                        $raw[] = $singleProperty;
                                    }
                                } elseif (is_object($singleProperty)) {
                                    $singleLabel = $singleProperty->label ?? false;
                                    if ($url = $singleProperty->url ?? false) {
                                        $value .= '<li>' .
                                            oes_get_html_anchor(($singleProperty->label ??
                                                ($singleProperty->id ?? $url)),
                                                $url, false, false, '_blank') . '</li>';
                                        $raw[] = $singleProperty->label ?? ($singleProperty->id ?? $url);
                                    } elseif ($singleLink = $singleProperty->id ?? false) {
                                        $icon = !empty($singleProperty->collection->icon) ?
                                            ('<img class="oes-gnd-sameas-links" src="' . $singleProperty->collection->icon . '" alt="oes-link-icon">') :
                                            '';
                                        $value .= '<li>' .
                                            $icon .
                                            oes_get_html_anchor(
                                                ($singleProperty->collection->name ?? ($singleLabel ?? $singleLink)),
                                                $singleLink, false, false, '_blank') . '</li>';
                                        $raw[] = $singleProperty->collection->name ?? ($singleLabel ?? $singleLink);
                                    } elseif ($singleLabel) {
                                        $value .= '<li>' . $singleLabel . '</li>';
                                        $raw[] = $singleLabel;
                                    } else {
                                        $prepareValue = [];
                                        foreach ($singleProperty as $singlePropertyPart)
                                            if (is_array($singlePropertyPart))
                                                $prepareValue[] = implode(' ', $singlePropertyPart);
                                        $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                                        $raw[] = $prepareValue;
                                    }
                                } elseif (is_array($singleProperty)) {
                                    $prepareValue = [];
                                    foreach ($singleProperty as $singlePropertyPart)
                                        $prepareValue = implode(' ', $singlePropertyPart);
                                    $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                                    $raw = $prepareValue;
                                } else {
                                    $value .= $singleProperty;
                                    $raw = $singleProperty;
                                }
                            }
                            $value .= '</ul>';
                        } elseif (is_object($property)) {
                            $prepareValue = [];
                            foreach ($property as $propertyPart) $prepareValue[] = implode(' ', $propertyPart);
                            $value = implode(' ', $prepareValue);
                            $raw = $prepareValue;
                        }
                    }

                    /* prepare position */
                    $transformedDataEntry[$propertyKey] = [
                        'label' => $label,
                        'value' => $value,
                        'raw' => $raw,
                        'position' => $position
                    ];
                }

                /* sort after position */
                $col = array_column($transformedDataEntry, 'position');
                array_multisort($col, SORT_ASC, $transformedDataEntry);

                /* prepare types */
                $entryID = $transformedDataEntry['gndIdentifier']['value'] ?? false;
                $entryTypesArray = [];
                if ($entryTypes = $transformedDataEntry['type']['raw'] ?? false)
                    foreach ($entryTypes as $entryType)
                        if ($entryType !== 'Authority Resource' && $entryType !== 'Normdatenressource')
                            $entryTypesArray[] = $entryType;

                $transformedData[$entryKey] = [
                    'entry' => $transformedDataEntry,
                    'id' => $entryID,
                    'name' => $transformedDataEntry['preferredName']['value'] ?? false,
                    'type' => implode(', ', $entryTypesArray),
                    'link' => '<a class="oes-admin-link oes-gnd-external" href="https://d-nb.info/gnd/' . $entryID .
                        '" target="_blank"></a>',
                    'link_frontend' => '<a href="https://d-nb.info/gnd/' . $entryID . '" target="_blank">' .
                        ($transformedDataEntry['preferredName']['value'] ?? $entryID) . '</a>'
                ];
            }

            return $this->transformed_data = $transformedData;
        }


        /** @inheritdoc */
        function get_data_for_display_modify_table_data($entry)
        {

            $entryData = $entry['entry'];

            /**
             * Filter the considered properties for the display of GND entry.
             *
             * @param array $entryData The considered entry.
             * @param array $types The gnd types.
             */
            if (has_filter('oes/api_gnd_display_table'))
                $entryData = apply_filters('oes/api_gnd_display_table', $entryData, $this->types);
            else {

                $modifiedEntries = [];

                /* @oesDevelopment Add preferred name and name variants (max 10) for all types.
                 * if (isset($entryData['preferredName']))
                 * $modifiedEntries[] = [
                 * 'label' => ...
                 * 'value' => $entryData['preferredName']['value']
                 * ];*/

                if (isset($entryData['variantName'])) {

                    /* resize array */
                    $variantNames = $entryData['variantName']['raw'];
                    if (isset($entryData['variantName']['raw']) && sizeof($entryData['variantName']['raw']) > 10) {
                        $variantNames = array_slice($entryData['variantName']['raw'], 0, 10);
                        $variantNames[] = '& more';
                    }

                    $modifiedEntries[] = [
                        'label' => ($this->language === 'german' ? 'Namensvariante(n)' : 'variant name(s)'),
                        'value' => '<ul class="oes-field-value-list"><li>' .
                            implode('</li><li>', $variantNames) . '</li></ul>'
                    ];
                }


                /**
                 * Person:
                 * date + place of birth
                 * date + place of death
                 * profession or occupation
                 */
                if (in_array('Person', $this->types)) {

                    if (isset($entryData['dateOfBirth']) || isset($entryData['placeOfBirth']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Geboren' : 'birth'),
                            'value' => (isset($entryData['dateOfBirth']['value']) ?
                                    (strpos($entryData['dateOfBirth']['raw'][0], '-') > 0 ?
                                        oes_convert_date_to_formatted_string($entryData['dateOfBirth']['raw'][0]) :
                                        $entryData['dateOfBirth']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfBirth']['value']) ?
                                    (',<span class="oes-lod-table-additional">' . $entryData['placeOfBirth']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['dateOfDeath']) || isset($entryData['placeOfDeath']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Verstorben' : 'death'),
                            'value' => (isset($entryData['dateOfDeath']['value']) ?
                                (strpos($entryData['dateOfDeath']['raw'][0], '-') > 0 ?
                                    oes_convert_date_to_formatted_string($entryData['dateOfDeath']['raw'][0]) :
                                    $entryData['dateOfDeath']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfDeath']['value']) ?
                                    (',<span class="oes-lod-table-additional">' . $entryData['placeOfDeath']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['professionOrOccupation']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Beruf oder Beschäftigung' :
                                'profession or occupation'),
                            'value' => $entryData['professionOrOccupation']['value']
                        ];
                } /**
                 * Place:
                 * latitude + longitude
                 */
                elseif (in_array('PlaceOrGeographicName', $this->types)) {

                    if (isset($entryData['hasGeometry']['raw'][0][0])) {
                        preg_match('#\((.*?)\)#', $entryData['hasGeometry']['raw'][0][0], $coordinates);
                        if (isset($coordinates[1]))
                            $modifiedEntries[] = [
                                'label' => ($this->language === 'german' ?
                                    'Geographischer Bezug' :
                                    'place or geographic name'),
                                'value' => oes_convert_coordinates_decimal_to_degree(trim($coordinates[1]))
                            ];
                    }
                } /**
                 * Conference:
                 * date + place of conference
                 * topic
                 */
                elseif (in_array('ConferenceOrEvent', $this->types)) {

                    if (isset($entryData['dateOfConferenceOrEvent']) || isset($entryData['placeOfConferenceOrEvent']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Datum' : 'date'),
                            'value' => (!empty($entryData['dateOfConferenceOrEvent']['value'] ?? '') ?
                                oes_convert_date_to_formatted_string($entryData['dateOfConferenceOrEvent']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfConferenceOrEvent']['value']) ?
                                    (',<span class="oes-lod-table-additional">' .
                                        $entryData['placeOfConferenceOrEvent']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['topic']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Themen' : 'topic'),
                            'value' => $entryData['topic']['value']
                        ];

                } /**
                 * Work:
                 * category
                 * topic
                 */
                elseif (in_array('Work', $this->types)) {

                    if (isset($entryData['gndSubjectCategory']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'GND-Sachgruppe' : 'GND subject category'),
                            'value' => $entryData['gndSubjectCategory']['value']
                        ];

                } /**
                 * Institution:
                 * abbreviation
                 * location
                 * superior
                 * homepage
                 */
                elseif (in_array('CorporateBody', $this->types)) {

                    if (isset($entryData['abbreviatedNameForTheCorporateBody']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Abgekürzter Name der Körperschaft	' :
                                'abbreviated name for the corporate body'),
                            'value' => $entryData['abbreviatedNameForTheCorporateBody']['value']
                        ];

                    if (isset($entryData['hierarchicalSuperiorOfTheCorporateBody']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Administrative Überordnung der Körperschaft' :
                                'hierarchical superior of the corporate body'),
                            'value' => $entryData['hierarchicalSuperiorOfTheCorporateBody']['value']
                        ];

                    if (isset($entryData['placeOfBusiness']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Sitz' : 'place of business'),
                            'value' => $entryData['placeOfBusiness']['value']
                        ];

                    if (isset($entryData['homepage']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Homepage' : 'homepage'),
                            'value' => $entryData['homepage']['value']
                        ];

                } /**
                 * Conference:
                 * date + place of conference
                 * topic
                 */
                elseif (in_array('SubjectHeading', $this->types)) {

                    if (isset($entryData['gndSubjectCategory']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'GND-Sachgruppe' : 'GND subject category'),
                            'value' => $entryData['gndSubjectCategory']['value']
                        ];
                    if (isset($entryData['broaderTermGeneral']))
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Oberbegriff allgemein' :
                                'broader term general'),
                            'value' => $entryData['broaderTermGeneral']['value']
                        ];

                } elseif (in_array('Familie', $this->types)) {

                }


                /* add geographic information for all */
                if (isset($entryData['geographicAreaCode']))
                    $modifiedEntries[] = [
                        'label' => ($this->language === 'german' ?
                            'Ländercode' :
                            'geographic area code'),
                        'value' => $entryData['geographicAreaCode']['value']
                    ];

                $entryData = $modifiedEntries;

            }

            return $entryData;
        }


        /** @inheritdoc */
        function get_data_for_display_prepare_html(string $title, string $table, mixed $entry): string
        {
            /**
             * Filter the content for the display of a GND entry.
             *
             * @param string $title The title string.
             * @param string $table The table string.
             * @param string $imageHTML The html image string.
             * @param string $consideredEntry The considered entry.
             * @param array $types The gnd types.
             */
            if (has_filter('oes/api_gnd_display_entry'))
                $html = apply_filters('oes/api_gnd_display_entry', $title, $table, $this->imageHTML, $entry['entry'],
                    $this->types);
            else {
                $additionalInfo = '';
                if (isset($entry['entry']['biographicalOrHistoricalInformation']))
                    $additionalInfo .= '<div class="oes-gnd-box-biographical-info"><h4 class="oes-content-table-header">' .
                        ($this->language === 'german' ?
                            'Biografische oder historische Angaben' :
                            'Biographical or historical Information') .
                        '</h4>' .
                        '<div>' . $entry['entry']['biographicalOrHistoricalInformation']['value'] . '</div>' .
                        '</div>';

                if (isset($entry['entry']['definition']))
                    $additionalInfo .= '<div class="oes-gnd-box-definition"><h4 class="oes-content-table-header">' . __('Definition', 'oes') . '</h4>' .
                        '<div>' . $entry['entry']['definition']['value'] . '</div>' .
                        '</div>';

                if (isset($entry['entry']['id']))
                    $additionalInfo .= '<div class="oes-gnd-box-link"><h4 class="oes-content-table-header">' .
                        __('GND Link', 'oes') . '</h4>' .
                        '<div>' . $entry['entry']['id']['value'] . '</div>' .
                        '</div>';

                /* as of 2025, "same as links" are disabled, because they can be accessed via the GDN link and seem to be messy in the GND database */
                /*if (isset($entry['entry']['sameAs']))
                    $additionalInfo .= '<div><h4 class="oes-content-table-header">' .
                        ($this->language === 'german' ? 'Weitere Links' : 'Further Links') .
                        '</h4>' .
                        '<div>' . $entry['entry']['sameAs']['value'] . '</div>' .
                        '</div>';*/

                return '<div class="oes-lod-box-title">' .
                    '<h3 class="oes-content-table-header">' . $title . '</h3>' .
                    '</div>' .
                    '<div class="oes-lod-box-container-inner">' .
                    (empty($this->imageHTML) ?
                        '' :
                        ('<div class="oes-lod-box-image-inner">' . $this->imageHTML . '</div>')) .
                    '<div class="oes-lod-box-content">' . $table . '</div>' .
                    '</div>' .
                    '<div class="oes-lod-box-additional-info">' . $additionalInfo . '</div>';
            }

            return $html;
        }
    }
}
