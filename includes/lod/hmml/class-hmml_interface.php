<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('HMML_Interface', false)) {

    /**
     * HMML Interface
     */
    class HMML_Interface extends API_Interface
    {
        public string $identifier = 'hmml';
        public string $label = 'HMML';
        public string $database_link = 'https://www.vhmml.org/';
        public string $url = 'https://www.hmml.org/';
        public string $schema_version = '3';

        const PROPERTIES = [
            'hmmlID' => [
                'label' => ['english' => 'ID'],
                'frontend' => false
            ],
            'primaryEntityType' => [
                'label' => ['english' => 'Typ'],
                'type' => 'string'
            ],
            'primaryDisplayName' => [
                'label' => ['english' => 'Name'],
                'type' => 'string'
            ],

            //Work
            'nativeScriptPrefName' => [
                'label' => ['english' => 'Native Script'],
                'type' => 'string'
            ],
            'description' => [
                'label' => ['english' => 'Description'],
                'type' => 'string'
            ],
            'preferredCitation' => [
                'label' => ['english' => 'Citation'],
                'type' => 'string'
            ],
            'originalLanguage' => [
                'label' => ['english' => 'Original Language'],
                'type' => 'object',
                'subfields' => [
                    'name'
                ],
                'pattern' => '{name}'
            ],
            'author' => [
                'label' => ['english' => 'Author'],
                'type' => 'object',
                'subfields' => [
                    'hmmlID',
                    'name'
                ],
                'pattern' => '{name}, {hmmlID} https://haf.vhmml.org/person/{hmmlID}'
            ],

            //TODO  'place', 'person', 'organization', 'note', 'family', 'expression'
        ];

        const SEARCH_PARAMETERS = [
            [
                'id' => 'oes-hmml-type',
                'label' => 'Type',
                'type' => 'select',
                'value' => 5,
                'args' => [
                    'options' => [
                        'all' => 'All',
                        'work' => 'Work',
                        'place' => 'Place',
                        'person' => 'Person',
                        'organization' => 'Organization',
                        'note' => 'Note',
                        'family' => 'Family',
                        'expression' => 'Expression'
                    ]
                ]
            ]
        ];

    }

    OES()->apis['hmml'] = new HMML_Interface('hmml');
}