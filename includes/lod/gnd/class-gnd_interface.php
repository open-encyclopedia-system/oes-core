<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('GND_Interface')) {

    /**
     * GND Interface
     */
    class GND_Interface extends API_Interface
    {
        public string $identifier = 'gnd';
        public string $label = 'GND';
        public string $database_link = 'https://www.dnb.de/';
        public string $url = 'https://d-nb.info/gnd/';
        public bool $preview_box = true;

        const PROPERTIES = [
            'gndIdentifier' => [
                'label' => [
                    'german' => 'GND-Nummer',
                    'english' => 'GND-Identifier'
                ]
            ],
            'biographicalOrHistoricalInformation' => [
                'label' => [
                    'german' => 'Biografische oder historische Angaben',
                    'english' => 'Biographical or historical information'
                ],
                'type' => 'array',
                'frontend' => false
            ],

            'academicDegree' => [
                'label' => [
                    'german' => 'Akademischer Grad',
                    'english' => 'Academic degree'
                ]
            ],
            'prefix' => [
                'label' => [
                    'german' => 'Präfix',
                    'english' => 'Prefix'
                ]
            ],
            'titleOfNobility' => [
                'label' => [
                    'german' => 'Adelstitel',
                    'english' => 'Title of nobility'
                ]
            ],
            'nameAddition' => [
                'label' => [
                    'german' => 'Namensusatz',
                    'english' => 'Name addition'
                ]
            ],
            'forename' => [
                'label' => [
                    'german' => 'Vorname',
                    'english' => 'Forename'
                ]
            ],
            'surname' => [
                'label' => [
                    'german' => 'Nachname',
                    'english' => 'Surname'
                ]
            ],
            'pseudonym' => [
                'label' => [
                    'german' => 'Pseudonym',
                    'english' => 'Pseudonym'
                ]
            ],
            'realIdentity' => [
                'label' => [
                    'german' => 'Echte Identität',
                    'english' => 'Real identity'
                ]
            ],
            'preferredName' => [
                'label' => [
                    'german' => 'Bevorzugter Name',
                    'english' => 'Preferred name'
                ]
            ],
            'variantName' => [
                'label' => [
                    'german' => 'Namensvariante(n)',
                    'english' => 'Variant name'
                ]
            ],
            'definition' => [
                'label' => [
                    'german' => 'Definition',
                    'english' => 'Definition'
                ]
            ],
            'broaderTerm' => [
                'label' => [
                    'german' => 'Oberbegriff',
                    'english' => 'Broader term'
                ]
            ],
            'broaderTermGeneral' => [
                'label' => [
                    'german' => 'Oberbegriff allgemein',
                    'english' => 'Broader term (general)'
                ]
            ],
            'broaderTermGeneric' => [
                'label' => [
                    'german' => 'Oberbegriff generisch',
                    'english' => 'Broader term (generic)'
                ]
            ],
            'broaderTermInstantial' => [
                'label' => [
                    'german' => 'Oberbegriff instantiell',
                    'english' => 'Broader term (instantial)'
                ]
            ],
            'broaderTermPartitive' => [
                'label' => [
                    'german' => 'Oberbegriff partitiv',
                    'english' => 'Broader term (partitive)'
                ]
            ],
            'broaderTermWithMoreThanOneElement' => [
                'label' => [
                    'german' => 'Oberbegriff mehrgliedrig',
                    'english' => 'Broader term (with more than one element)'
                ]
            ],
            'gndSubjectCategory' => [
                'label' => [
                    'german' => 'GND-Sachgruppe',
                    'english' => 'GND subject category'
                ]
            ],
            'gender' => [
                'label' => [
                    'german' => 'Geschlecht',
                    'english' => 'Gender'
                ]
            ],
            'dateOfBirthAndDeath' => [
                'label' => [
                    'german' => 'Geburts- und Sterbedatum',
                    'english' => 'Date of birth and death'
                ]
            ],
            'dateOfBirth' => [
                'label' => [
                    'german' => 'Geburtsdatum',
                    'english' => 'Date of birth'
                ]
            ],
            'placeOfBirth' => [
                'label' => [
                    'german' => 'Geburtsort',
                    'english' => 'Place of Birth'
                ]
            ],
            'dateOfDeath' => [
                'label' => [
                    'german' => 'Sterbedatum',
                    'english' => 'Date of death'
                ]
            ],
            'placeOfDeath' => [
                'label' => [
                    'german' => 'Sterbeort',
                    'english' => 'Place of death'
                ]
            ],
            'dateOfEstablishment' => [
                'label' => [
                    'german' => 'Gründungsdatum',
                    'english' => 'Date of establishment'
                ]
            ],
            'dateOfPublication' => [
                'label' => [
                    'german' => 'Erscheinungszeit',
                    'english' => 'Date of publication'
                ]
            ],
            'dateOfConferenceOrEvent' => [
                'label' => [
                    'german' => 'Veranstaltungsdaten',
                    'english' => 'Date of conference or event'
                ]
            ],
            'geographicAreaCode' => [
                'label' => [
                    'german' => 'Ländercode',
                    'english' => 'Geographic Area Code'
                ]
            ],
            'place' => [
                'label' => [
                    'german' => 'Ort',
                    'english' => 'Place'
                ]
            ],
            'placeOfActivity' => [
                'label' => [
                    'german' => 'Wirkungsort',
                    'english' => 'Place of activity'
                ]
            ],
            'placeOfBusiness' => [
                'label' => [
                    'german' => 'Sitz',
                    'english' => 'Place of business'
                ]
            ],
            'placeOfConferenceOrEvent' => [
                'label' => [
                    'german' => 'Veranstaltungsort',
                    'english' => 'Place of conference or event'
                ]
            ],
            'associatedPlace' => [
                'label' => [
                    'german' => 'Assoziierter Ort',
                    'english' => 'Associated place'
                ]
            ],
            'otherPlace' => [
                'label' => [
                    'german' => 'Weiterer Ort',
                    'english' => 'Other place'
                ]
            ],
            'coordinates' => [
                'label' => [
                    'german' => 'Koordinaten',
                    'english' => 'Coordinates'
                ]
            ],
            'professionOrOccupation' => [
                'label' => [
                    'german' => 'Beruf oder Beschäftigung',
                    'english' => 'Profession or occupation'
                ],
                'frontend' => false
            ],
            'affiliation' => [
                'label' => [
                    'german' => 'Affiliation',
                    'english' => 'Affiliation'
                ]
            ],
            'fieldOfActivity' => [
                'label' => [
                    'german' => 'Tätigkeitsbereich',
                    'english' => 'Field of activity'
                ]
            ],
            'fieldOfStudy' => [
                'label' => [
                    'german' => 'Studienfach',
                    'english' => 'Field of study'
                ]
            ],
            'firstAuthor' => [
                'label' => [
                    'german' => 'Erste Verfasserschaft',
                    'english' => 'First author'
                ]
            ],
            'predecessor' => [
                'label' => [
                    'german' => 'Vorgänger',
                    'english' => 'Predecessor'
                ]
            ],
            'homepage' => [
                'label' => [
                    'german' => 'Homepage',
                    'english' => 'Homepage'
                ]
            ],
        ];

        const SEARCH_PARAMETERS = [
            [
                'id' => 'oes-gnd-type',
                'label' => 'Type',
                'type' => 'select',
                'value' => 5,
                'args' => [
                    'options' => [
                        'all' => 'All',
                        'Person' => 'Person',
                        'ConferenceOrEvent' => 'Konferenz oder Veranstaltung',
                        'Work' => 'Werk',
                        'CorporateBody' => 'Körperschaft',
                        'SubjectHeading' => 'Schlagwort',
                        'PlaceOrGeographicName' => 'Geografikum',
                        'Familie' => 'Familie'
                    ]
                ]
            ],
            [
                'id' => 'oes-gnd-size',
                'label' => 'Size',
                'type' => 'number',
                'value' => 5,
                'args' => [
                    'min' => 0,
                    'max' => 100
                ]
            ]
        ];
    }

    OES()->apis['gnd'] = new GND_Interface('gnd');
}