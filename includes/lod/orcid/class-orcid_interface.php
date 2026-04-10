<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ORCID_Interface')) {

    /**
     * ORCID Interface
     */
    class ORCID_Interface extends API_Interface
    {
        public string $identifier = 'orcid';
        public string $label = 'ORCID';
        public string $database_link = 'https://orcid.org/';
        public string $url = 'https://orcid.org/';

        const PROPERTIES = [
            'orcid-id' => [
                'label' => ['german' => 'ORCID ID', 'english' => 'ORCID ID'],
            ],
            'given-names' => [
                'label' => ['german' => 'Namen', 'english' => 'Names'],
            ],
            'family-names' => [
                'label' => ['german' => 'Nachnamen', 'english' => 'Surnames'],
            ],
            'credit-name' => [
                'label' => ['german' => 'Name', 'english' => 'Credit Name'],
                'type' => 'array',
            ],
            'institution-name' => [
                'label' => ['german' => 'Insitutionen', 'english' => 'Affiliations'],
                'type' => 'array',
            ],
            'orcid-identifier' => [
                'label' => ['german' => 'ORCID ID', 'english' => 'ORCID ID'],
                'type' => 'object',
                'subproperties' => ['path']
            ],
            'person' => [
                'label' => ['german' => 'Name', 'english' => 'Name'],
                'type' => 'object',
                'subproperties' => [
                    'name:given-names',
                    'name:family-name',
                ]
            ]
        ];
    }

    OES()->apis['orcid'] = new ORCID_Interface('orcid');
}