<?php

namespace OES\API;

if (!class_exists('\OES\API\GND_Display_Helper')) :
    class GND_Display_Helper extends Display_Helper
    {

        /** @inheritdoc */
        protected function get_table($entry)
        {

            $entryData = $entry['entry'];
            $types = $entry['types'] ?? [];

            if (has_filter('oes/api_gnd_display_table')) {
                $entryData = apply_filters('oes/api_gnd_display_table', $entryData, $types);
            }
            else {

                $modifiedEntries = [];

                /* @oesDevelopment Add preferred name and name variants (max 10) for all types.
                 * if (isset($entryData['preferredName']))
                 * $modifiedEntries[] = [
                 * 'label' => ...
                 * 'value' => $entryData['preferredName']['value']
                 * ];*/

                if (isset($entryData['variantName'])) {

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

                if (in_array('Person', $types)) {

                    /**
                     * Person:
                     * date + place of birth
                     * date + place of death
                     * profession or occupation
                     */

                    if (isset($entryData['dateOfBirth']) || isset($entryData['placeOfBirth'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Geboren' : 'birth'),
                            'value' => (isset($entryData['dateOfBirth']['value']) ?
                                    (strpos($entryData['dateOfBirth']['raw'][0], '-') > 0 ?
                                        oes_convert_date_to_formatted_string($entryData['dateOfBirth']['raw'][0]) :
                                        $entryData['dateOfBirth']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfBirth']['value']) ?
                                    (', <span class="oes-lod-table-additional">' . $entryData['placeOfBirth']['value'] . '</span>') :
                                    '')
                        ];
                    }

                    if (isset($entryData['dateOfDeath']) || isset($entryData['placeOfDeath'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Verstorben' : 'death'),
                            'value' => (isset($entryData['dateOfDeath']['value']) ?
                                    (strpos($entryData['dateOfDeath']['raw'][0], '-') > 0 ?
                                        oes_convert_date_to_formatted_string($entryData['dateOfDeath']['raw'][0]) :
                                        $entryData['dateOfDeath']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfDeath']['value']) ?
                                    (', <span class="oes-lod-table-additional">' . $entryData['placeOfDeath']['value'] . '</span>') :
                                    '')
                        ];
                    }

                    if (isset($entryData['professionOrOccupation'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Beruf oder Beschäftigung' :
                                'profession or occupation'),
                            'value' => $entryData['professionOrOccupation']['value']
                        ];
                    }
                }
                elseif (in_array('PlaceOrGeographicName', $types)) {

                    /**
                     * Place:
                     * latitude + longitude
                     */

                    if (isset($entryData['hasGeometry']['raw'][0][0])) {
                        preg_match('#\((.*?)\)#', $entryData['hasGeometry']['raw'][0][0], $coordinates);
                        if (isset($coordinates[1])) {
                            $modifiedEntries[] = [
                                'label' => ($this->language === 'german' ?
                                    'Geographischer Bezug' :
                                    'place or geographic name'),
                                'value' => oes_convert_coordinates_decimal_to_degree(trim($coordinates[1]))
                            ];
                        }
                    }
                }
                elseif (in_array('ConferenceOrEvent', $types)) {

                    /**
                     * Conference:
                     * date + place of conference
                     * topic
                     */

                    if (isset($entryData['dateOfConferenceOrEvent']) || isset($entryData['placeOfConferenceOrEvent'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Datum' : 'date'),
                            'value' => (!empty($entryData['dateOfConferenceOrEvent']['value'] ?? '') ?
                                    oes_convert_date_to_formatted_string($entryData['dateOfConferenceOrEvent']['raw'][0]) :
                                    '') .
                                (isset($entryData['placeOfConferenceOrEvent']['value']) ?
                                    (', <span class="oes-lod-table-additional">' .
                                        $entryData['placeOfConferenceOrEvent']['value'] . '</span>') :
                                    '')
                        ];
                    }

                    if (isset($entryData['topic'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Themen' : 'topic'),
                            'value' => $entryData['topic']['value']
                        ];
                    }

                }
                elseif (in_array('Work', $types)) {

                    /**
                     * Work:
                     * category
                     * topic
                     */

                    if (isset($entryData['gndSubjectCategory'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'GND-Sachgruppe' : 'GND subject category'),
                            'value' => $entryData['gndSubjectCategory']['value']
                        ];
                    }

                }
                elseif (in_array('CorporateBody', $types)) {

                    /**
                     * Institution:
                     * abbreviation
                     * location
                     * superior
                     * homepage
                     */

                    if (isset($entryData['abbreviatedNameForTheCorporateBody'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Abgekürzter Name der Körperschaft	' :
                                'abbreviated name for the corporate body'),
                            'value' => $entryData['abbreviatedNameForTheCorporateBody']['value']
                        ];
                    }

                    if (isset($entryData['hierarchicalSuperiorOfTheCorporateBody'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Administrative Überordnung der Körperschaft' :
                                'hierarchical superior of the corporate body'),
                            'value' => $entryData['hierarchicalSuperiorOfTheCorporateBody']['value']
                        ];
                    }

                    if (isset($entryData['placeOfBusiness'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Sitz' : 'place of business'),
                            'value' => $entryData['placeOfBusiness']['value']
                        ];
                    }

                    if (isset($entryData['homepage'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'Homepage' : 'homepage'),
                            'value' => $entryData['homepage']['value']
                        ];
                    }

                }
                elseif (in_array('SubjectHeading', $types)) {

                    /**
                     * Conference:
                     * date + place of conference
                     * topic
                     */

                    if (isset($entryData['gndSubjectCategory'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ? 'GND-Sachgruppe' : 'GND subject category'),
                            'value' => $entryData['gndSubjectCategory']['value']
                        ];
                    }
                    if (isset($entryData['broaderTermGeneral'])) {
                        $modifiedEntries[] = [
                            'label' => ($this->language === 'german' ?
                                'Oberbegriff allgemein' :
                                'broader term general'),
                            'value' => $entryData['broaderTermGeneral']['value']
                        ];
                    }

                } elseif (in_array('Familie', $types)) {
                    //@oesDevelopment
                }

                if (isset($entryData['geographicAreaCode'])) {
                    $modifiedEntries[] = [
                        'label' => ($this->language === 'german' ?
                            'Ländercode' :
                            'geographic area code'),
                        'value' => $entryData['geographicAreaCode']['value']
                    ];
                }

                $entryData = $modifiedEntries;

            }

            return $entryData;
        }

        /** @inheritdoc */
        protected function prepare_html(string $title, string $table, mixed $entry): string
        {

            if (has_filter('oes/api_gnd_display_entry')) {
                $html = apply_filters('oes/api_gnd_display_entry', $title, $table, $entry['entry']);
            }
            else {
                $additionalInfo = '';
                if (isset($entry['entry']['biographicalOrHistoricalInformation'])) {
                    $additionalInfo .= '<div class="oes-gnd-box-biographical-info"><h4 class="oes-content-table-header">' .
                        ($this->language === 'german' ?
                            'Biografische oder historische Angaben' :
                            'Biographical or historical Information') .
                        '</h4>' .
                        '<div>' . $entry['entry']['biographicalOrHistoricalInformation']['value'] . '</div>' .
                        '</div>';
                }

                if (isset($entry['entry']['definition'])) {
                    $additionalInfo .= '<div class="oes-gnd-box-definition"><h4 class="oes-content-table-header">' . __('Definition', 'oes') . '</h4>' .
                        '<div>' . $entry['entry']['definition']['value'] . '</div>' .
                        '</div>';
                }

                if (isset($entry['entry']['id'])) {
                    $additionalInfo .= '<div class="oes-gnd-box-link"><h4 class="oes-content-table-header">' .
                        __('GND Link', 'oes') . '</h4>' .
                        '<div>' . $entry['entry']['id']['value'] . '</div>' .
                        '</div>';
                }

                /* as of 2025, "same as links" are disabled, because they can be accessed via the GDN link and seem to be messy in the GND database */
                /*if (isset($entry['entry']['sameAs']))
                    $additionalInfo .= '<div><h4 class="oes-content-table-header">' .
                        ($this->language === 'german' ? 'Weitere Links' : 'Further Links') .
                        '</h4>' .
                        '<div>' . $entry['entry']['sameAs']['value'] . '</div>' .
                        '</div>';*/

                $imageHTML = '';
                if(!empty($entry['image'])){
                    $imageHTML = '<div class="oes-lod-box-image-inner">' . $entry['image'] . '</div>';
                }

                return '<div class="oes-lod-box-title">' .
                    '<h3 class="oes-content-table-header">' . $title . '</h3>' .
                    '</div>' .
                    '<div class="oes-lod-box-container-inner">' .
                    $imageHTML .
                    '<div class="oes-lod-box-content">' . $table . '</div>' .
                    '</div>' .
                    '<div class="oes-lod-box-additional-info">' . $additionalInfo . '</div>';
            }

            return $html;
        }
    }
endif;