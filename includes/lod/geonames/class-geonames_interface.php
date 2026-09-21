<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Geonames_Interface')) {

    /**
     * Geonames Interface.
     */
    class Geonames_Interface extends API_Interface
    {
        public string $identifier = 'geonames';
        public string $label = 'GeoNames';
        public string $database_link = 'https://www.geonames.org/';
        public string $url = 'https://www.geonames.org/';
        public bool $credentials = true;

        const PROPERTIES = [
            'geonameId' => [
                'label' => [
                    'german' => 'Geonames ID',
                    'english' => 'Geonames ID'
                ]
            ],
            'name' => [
                'label' => [
                    'german' => 'Name',
                    'english' => 'Name'
                ]
            ],
            'colloquialName' => [
                'label' => [
                    'german' => 'Umganssprachliche Name(n)',
                    'english' => 'Colloquial Name(s)'
                ]
            ],
            'historicalName' => [
                'label' => [
                    'german' => 'Historische Name(n)',
                    'english' => 'Historical Name(s)'
                ]
            ],
            'alternateName' => [
                'label' => [
                    'german' => 'Alternative Name(n)',
                    'english' => 'Alternate Name(s)'
                ]
            ],
            'officialName' => [
                'label' => [
                    'german' => 'Offizieller Name',
                    'english' => 'Official Name'
                ]
            ],
            'shortName' => [
                'label' => [
                    'german' => 'Name Abkürzung',
                    'english' => 'Short Name'
                ]
            ],
            'lng' => [
                'label' => [
                    'german' => 'Längengrad',
                    'english' => 'Longitude'
                ]
            ],
            'lat' => [
                'label' => [
                    'german' => 'Breitengrad',
                    'english' => 'Latitude'
                ]
            ],
            'countryCode' => [
                'label' => [
                    'german' => 'Ländercode',
                    'english' => 'Country Code'
                ]
            ],
            'countryName' => [
                'label' => [
                    'german' => 'Land',
                    'english' => 'Country'
                ]
            ],
            'postalCode' => [
                'label' => [
                    'german' => 'Postleitzahl',
                    'english' => 'Postal Code'
                ]
            ],
            'population' => [
                'label' => [
                    'german' => 'Population',
                    'english' => 'Population'
                ],
                'frontend' => false
            ]
        ];

        const SEARCH_PARAMETERS = [
            [
                'id' => 'oes-geonames-size',
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

    OES()->apis['geonames'] = new Geonames_Interface('geonames');
}