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


        //Overwrite parent
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


        //Overwrite parent
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

                        /* take first image TODO @nextRelease : can there be more images? */
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
                            $value = '<ul class="oes-lod-box-list">';
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
                                        $value .= '<li>' .
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
                if($entryTypes = $transformedDataEntry['type']['raw'] ?? false)
                    foreach($entryTypes as $entryType)
                        if($entryType !== 'Authority Resource' && $entryType !== 'Normdatenressource')
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


        //Overwrite parent
        function get_data_for_display_modify_table_data($entry){

            $entryData = $entry['entry'];

            /**
             * Filter the considered properties for the display of GND entry.
             *
             * @param array $entryData The considered entry.
             * @param array $types The gnd types.
             */
            if (has_filter('oes/api_gnd_display_table'))
                $entryData = apply_filters('oes/api_gnd_display_table', $entryData, $this->types);
            else{

                $modifiedEntries = [];

                /* add preferred name and name variants (max 10) for all types */
                if (isset($entryData['preferredName']))
                    $modifiedEntries[] = [
                        'label' => '<i class="fa fa-user"></i>',
                        'value' => $entryData['preferredName']['value']
                    ];

                if (isset($entryData['variantName'])) {

                    /* resize array */
                    $variantNames = $entryData['variantName']['raw'];
                    if (isset($entryData['variantName']['raw']) && sizeof($entryData['variantName']['raw']) > 10) {
                        $variantNames = array_slice($entryData['variantName']['raw'], 0, 10);
                        $variantNames[] = '& more';
                    }

                    $modifiedEntries[] = [
                        'label' => '<i class="fa fa-user"></i>',
                        'value' => '<ul class="oes-lod-box-list"><li>' .
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
                            'label' => '<i class="fa fa-asterisk"></i>',
                            'value' => (isset($entryData['dateOfBirth']['value']) ?
                                    date_format(date_create($entryData['dateOfBirth']['raw'][0]), 'd.m.Y') :
                                    '') .
                                (isset($entryData['placeOfBirth']['value']) ?
                                    (',<span class="oes-lod-table-additional">' . $entryData['placeOfBirth']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['dateOfDeath']) || isset($entryData['placeOfDeath']))
                        $modifiedEntries[] = [
                            'label' => '<strong>†</strong>',
                            'value' => (isset($entryData['dateOfDeath']['value']) ?
                                    date_format(date_create($entryData['dateOfDeath']['raw'][0]), 'd.m.Y') :
                                    '') .
                                (isset($entryData['placeOfDeath']['value']) ?
                                    (',<span class="oes-lod-table-additional">' . $entryData['placeOfDeath']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['professionOrOccupation']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-briefcase"></i>',
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
                                'label' => '<i class="fa fa-globe"></i>',
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
                            'label' => '<i class="fa fa-calendar"></i>',
                            'value' => (isset($entryData['dateOfConferenceOrEvent']['value']) ?
                                    date_format(date_create($entryData['dateOfConferenceOrEvent']['raw'][0]), 'd.m.Y') :
                                    '') .
                                (isset($entryData['placeOfConferenceOrEvent']['value']) ?
                                    (',<span class="oes-lod-table-additional">' .
                                        $entryData['placeOfConferenceOrEvent']['value'] . '</span>') :
                                    '')
                        ];

                    if (isset($entryData['topic']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-tags"></i>',
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
                            'label' => '<i class="fa fa-tags"></i>',
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
                            'label' => '<i class="fa fa-user"></i>',
                            'value' => $entryData['abbreviatedNameForTheCorporateBody']['value']
                        ];

                    if (isset($entryData['hierarchicalSuperiorOfTheCorporateBody']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-sitemap"></i>',
                            'value' => $entryData['hierarchicalSuperiorOfTheCorporateBody']['value']
                        ];

                    if (isset($entryData['placeOfBusiness']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-map-marker"></i>',
                            'value' => $entryData['placeOfBusiness']['value']
                        ];

                    if (isset($entryData['homepage']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-home"></i>',
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
                            'label' => '<i class="fa fa-tags"></i>',
                            'value' => $entryData['gndSubjectCategory']['value']
                        ];
                    if (isset($entryData['broaderTermGeneral']))
                        $modifiedEntries[] = [
                            'label' => '<i class="fa fa-tags"></i>',
                            'value' => $entryData['broaderTermGeneral']['value']
                        ];

                } elseif (in_array('Familie', $this->types)) {

                }


                /* add geographic information for all */
                if (isset($entryData['geographicAreaCode']))
                    $modifiedEntries[] = [
                        'label' => '<i class="fa fa-map-marker"></i>',
                        'value' => $entryData['geographicAreaCode']['value']
                    ];

                $entryData = $modifiedEntries;

            }

            return $entryData;
        }


        //Overwrite parent
        function get_data_for_display_prepare_html(string $title, string $table, $entry): string
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
            else{
                $additionalInfo = '';
                if (isset($entry['entry']['biographicalOrHistoricalInformation']))
                    $additionalInfo .= '<div><h4 class="oes-content-table-header4">' .
                        __('Biographical or historical Information', 'oes') . '</h4>' .
                        $entry['entry']['biographicalOrHistoricalInformation']['value'] . '</div>';

                if (isset($entry['entry']['definition']))
                    $additionalInfo .= '<div><h4 class="oes-content-table-header4">' . __('Definition', 'oes') . '</h4>' .
                        $entry['entry']['definition']['value'] . '</div>';

                if (isset($entry['entry']['id']))
                    $additionalInfo .= '<div><h4 class="oes-content-table-header4">' .
                        __('GND Link', 'oes') . '</h4>' .
                        $entry['entry']['id']['value'] . '</div>';

                if (isset($entry['entry']['sameAs']))
                    $additionalInfo .= '<h4 class="oes-content-table-header4">' .
                        __('Further Links', 'oes') . '</h4>' . $entry['entry']['sameAs']['value'];

                return sprintf('<div class="oes-lod-box-title"><h3 class="oes-content-table-header3">%s</h3></div>' .
                    '<div class="oes-lod-box-container-inner">' .
                    '<div class="oes-lod-box-image-inner">%s</div>' .
                    '<div class="oes-lod-box-content">%s</div>' .
                    '</div>' .
                    '<div class="oes-lod-box-additional-info">%s</div>',
                    $title,
                    $this->imageHTML,
                    $table,
                    $additionalInfo
                );
            }

            return $html;
        }
    }
}


/**
 * Retrieve data information (id and label) from shortcode.
 *
 * @param string $shortcode The shortcode.
 * @return array Return data as ['label', 'id']
 */
function gnd_retrieve_data_from_shortcode(string $shortcode): array
{
    /* get label and id */
    preg_match('/label="([^\"]*)"/', $shortcode, $label);
    preg_match('/id="([^\"]*)"/', $shortcode, $id);

    /* prepare return */
    return [
        'label' => $label[1] ?? false,
        'id' => $id[1] ?? false
    ];
}