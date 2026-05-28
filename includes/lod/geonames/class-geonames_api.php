<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Geonames_API', false)) {

    /**
     * Class Geonames_API
     * https://www.geonames.org/export/web-services.html
     *
     * The parameter 'username' needs to be passed with each request. The username for your application can be
     * registered http://www.geonames.org/login.
     * You will then receive an email with a confirmation link, and after you have confirmed the email you can enable
     * your account for the webservice on your account page http://www.geonames.org/manageaccount.
     *
     * services:
     * https://www.geonames.org/export/ws-overview.html
     *
     * Limitations:
     * @oesDevelopment Validate limitation
     * 20'000 credits daily limit per application (identified by the parameter 'username'), the hourly limit is 1000
     * credits. A credit is a web service request hit for most services. An exception is thrown when the limit is
     * exceeded.
     * (1 credit per search, http://www.geonames.org/export/credits.html)
     *
     */
    class Geonames_API extends Rest_API
    {
        public string $identifier = 'geonames';
        public string $url = 'http://api.geonames.org/';

        /** @inheritdoc */
        function set_credentials(): void
        {
            $this->login = get_option('oes_api-geonames_login');
        }

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            $url .= 'searchJSON?q=' . $searchTerm;

            $size = $args['oes-geonames-size'] ?? 10;
            if(!empty($size)) {
                $url .= '&maxRows=' . $size;
            }

            if(!empty($this->login)) {
                $url .= '&username=' . $this->login;
            }

            return $url;
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            $url .= 'getJSON?geonameId=' . $id;

            if(!empty($this->login)) {
                $url .= '&username=' . $this->login;
            }

            return $url;
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            if($response->geonames ?? false){
                return ['items' => $response->geonames];
            }
            return ['items' => [$response]];
        }

        /** @inheritdoc */
        protected function get_entry_id($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'geonameId');
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string {
            return __('Geonames Geographikum', 'oes');
        }

        /** @inheritdoc */
        protected function get_entry_link($entry, $item): string {
            $entryID = $this->get_entry_id($entry, $item);
            return 'https://www.geonames.org/' . $entryID;
        }
    }
}