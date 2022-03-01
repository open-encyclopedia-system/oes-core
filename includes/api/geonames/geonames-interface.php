<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Geonames_Interface')) {
    /**
     * TODO @nextRelease: read the properties from API?
     */
    class Geonames_Interface extends API_Interface
    {

        //Set parent parameter
        public string $identifier = 'geonames';
        public string $label = 'Geonames';
        public string $database_link = 'https://www.geonames.org/';
        public bool $credentials = true;

        const PROPERTIES = [
            'geonameId' => ['label' => ['german' => 'Geonames ID', 'english' => 'Geonames ID'], 'position' => 20],
            'name' => ['label' => ['german' => 'Name', 'english' => 'Name'], 'position' => 21],
            'colloquialName' => ['label' => ['german' => 'Umganssprachliche Name(n)', 'english' => 'Colloquial Name(s)'], 'position' => 22],
            'historicalName' => ['label' => ['german' => 'Historische Name(n)', 'english' => 'Historical Name(s)'], 'position' => 23],
            'alternateName' => ['label' => ['german' => 'Alternative Name(n)', 'english' => 'Alternate Name(s)'], 'position' => 24],
            'officialName' => ['label' => ['german' => 'Offizieller Name', 'english' => 'Official Name'], 'position' => 25],
            'shortName' => ['label' => ['german' => 'Name Abkürzung', 'english' => 'Short Name'], 'position' => 26],
            'lng' => ['label' => ['german' => 'Längengrad', 'english' => 'Longitude'], 'position' => 40],
            'lat' => ['label' => ['german' => 'Breitengrad', 'english' => 'Latitude'], 'position' => 41],
            'countryCode' => ['label' => ['german' => 'Ländercode', 'english' => 'Country Code'], 'position' => 50],
            'countryName' => ['label' => ['german' => 'Land', 'english' => 'Country'], 'position' => 51],
            'postalCode' => ['label' => ['german' => 'Postleitzahl', 'english' => 'Postal Code'], 'position' => 52],
            'population' => ['label' => ['german' => 'Population', 'english' => 'Population'], 'position' => 53]
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

    /* include geonames api files and instantiate api interface */
    oes_include('/includes/api/geonames/rest-api-geonames.php');
    OES()->apis['geonames'] = new Geonames_Interface('geonames');
}