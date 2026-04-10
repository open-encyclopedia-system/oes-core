<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOC_Interface')) {

    /**
     * LOC Interface
     */
    class LOC_Interface extends API_Interface
    {
        public string $identifier = 'loc';
        public string $label = 'Library of Congress (Subjects)';
        public string $database_link = 'https://id.loc.gov/authorities/subjects.html';
        public string $url = 'https://id.loc.gov/authorities/subjects/';
        public bool $schema = false;

        const PROPERTIES = [
            'id' => [
                'label' => ['german' => 'ID', 'english' => 'ID'],
            ],
            'name' => [
                'label' => ['german' => 'Titel', 'english' => 'Title'],
            ],
            'link' => [
                'label' => ['german' => 'Link', 'english' => 'Link'],
            ],
        ];
    }

    OES()->apis['loc'] = new LOC_Interface('loc');
}