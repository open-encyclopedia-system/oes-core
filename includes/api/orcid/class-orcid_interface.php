<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ORCID_Interface')) {

    /**
     * ORCID Interface
     *
     * @oesDevelopment Read the properties from API?
     */
    class ORCID_Interface extends API_Interface
    {

        /** @inheritdoc */
        public string $identifier = 'orcid';
        public string $label = 'ORCID';
        public string $database_link = 'https://orcid.org/';
        public string $schema_version = '2.1';

        const PROPERTIES = [];

        const SEARCH_PARAMETERS = [];

    }

    OES()->apis['orcid'] = new ORCID_Interface('orcid');
}