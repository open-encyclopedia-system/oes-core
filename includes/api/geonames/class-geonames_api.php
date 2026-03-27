<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Geonames_API')) {

    /**
     * @oesDevelopment
     * @oesDevelopment Rename 'lobid' for frontend search with neutral term.
     *
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
     * @oesDevelopment Validate
     * 20'000 credits daily limit per application (identified by the parameter 'username'), the hourly limit is 1000
     * credits. A credit is a web service request hit for most services. An exception is thrown when the limit is
     * exceeded.
     * (1 credit per search, http://www.geonames.org/export/credits.html)
     *
     */
    class Geonames_API extends Rest_API
    {

        /** @inheritdoc */
        public string $identifier = 'geonames';
        public string $url = 'http://api.geonames.org/';

        /** @inheritdoc */
        function set_credentials(): void
        {
            $this->login = get_option('oes_api-geonames_login');
        }

        /** @inheritdoc */
        function get_request_url(string $url, array $args): string
        {
            if(empty($url)){
                $url = $this->url;
            }

            $this->search_term = $args['search_term'] ?? '';

            if(!empty($this->search_term)) {
                $url .= 'searchJSON?q=' . $this->search_term;
            }
            elseif($args['lod_id'] ?? false) {
                $url .= 'getJSON?geonameId=' . $args['lod_id'];
            }

            $size = $args['oes-geonames-size'] ?? 10;
            if(!empty($size)) {
                $url .= '&maxRows=' . $size;
            }

            if(!empty($this->login)) {
                $url .= '&username=' . $this->login;
            }

            //@oesDevelopment Encode url.
            return $url;
        }

        /** @inheritdoc */
        function get_data_from_response($response)
        {
            if($response->geonames ?? false){
                return $response->geonames;
            }
            return [(array) $response];
        }

        /** @inheritdoc */
        function transform_data_entry(mixed $entry):array
        {
            if(!is_array($entry)) {
                $entry = array($entry);
            }

            $entryID = $entry['geonameId']['value'] ?? false;
            return [
                'entry' => $entry,
                'id' => (string) $entryID,
                'name' => $entry['name']['value'] ?? false,
                'type' => 'Geographikum',
                'link' => 'https://www.geonames.org/' . $entryID,
                'link_frontend' => '<a href="https://www.geonames.org/' . $entryID . '" target="_blank">' .
                    ($entry['name']['value'] ?? $entryID) . '</a>'
            ];
        }
    }
}