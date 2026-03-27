<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ROR_Interface')) {

    /**
     * ROR Interface
     *
     * @oesDevelopment Read the properties from API?
     */
    class ROR_Interface extends API_Interface
    {

        /** @inheritdoc */
        public string $identifier = 'ror';
        public string $label = 'ROR';
        public string $database_link = 'https://ror.org/';
        public string $schema_version = '2.1';

        const PROPERTIES = [
            'rorID' => [
                'label' => ['german' => 'ID', 'english' => 'ID']
            ],
            'names' => [
                'label' => ['german' => 'Names', 'english' => 'Names'],
                'type' => 'object',
                'subfields' => [
                    'lang',
                    'types',
                    'value'
                ],
                'pattern' => '{types} {value} ({lang})'
            ],
            'links' => [
                'label' => ['german' => 'Links', 'english' => 'Links'],
                'type' => 'object',
                'subfields' => [
                    'type',
                    'value'
                ],
                'pattern' => '{type} {value}'
            ],
            'locations' => [
                'label' => ['german' => 'Locations', 'english' => 'Locations'],
                'type' => 'object',
                'subfields' => [
                    'geonames_details:name',
                    'geonames_id'
                ],
                'pattern' => '{geonames_details:name}, Geonames ID: {geonames_id}'
            ],
            'relationships' => [
                'label' => ['german' => 'Relationships', 'english' => 'Relationships'],
                'type' => 'object',
                'subfields' => [
                    'id',
                    'type',
                    'label'
                ],
                'pattern' => '{label} ({type}), {id}'
            ],
            'status' => [
                'label' => ['german' => 'Status', 'english' => 'Status'],
                'type' => 'string'
            ],
            'types' => [
                'label' => ['german' => 'Types', 'english' => 'Types'],
                'type' => 'array'
            ],
            'domains' => [
                'label' => ['german' => 'Domains', 'english' => 'Domains'],
                'type' => 'array'
            ],
            'established' => [
                'label' => ['german' => 'Established', 'english' => 'Established'],
                'type' => 'number'
            ],
            'external_ids' => [
                'label' => ['german' => 'External IDs', 'english' => 'External IDs'],
                'type' => 'object',
                'subfields' => [
                    'type', 
                    'preferred', 
                    'all'
                ],
                'pattern' => '{type} {preferred} ({all})'
            ],
        ];

        const SEARCH_PARAMETERS = [];

    }

    OES()->apis['ror'] = new ROR_Interface('ror');
}