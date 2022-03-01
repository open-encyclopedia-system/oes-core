<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Geonames_API')) {

    /**
     * Class Geonames_API
     * https://www.geonames.org/export/web-services.html
     *
     * The parameter 'username' needs to be passed with each request. The username for your application can be
     * registered http://www.geonames.org/login.
     * You will then receive an email with a confirmation link and after you have confirmed the email you can enable
     * your account for the webservice on your account page http://www.geonames.org/manageaccount.
     *
     * services: TODO
     * https://www.geonames.org/export/ws-overview.html
     *
     * Limitations: TODO
     * 20'000 credits daily limit per application (identified by the parameter 'username'), the hourly limit is 1000
     * credits. A credit is a web service request hit for most services. An exception is thrown when the limit is
     * exceeded.
     * (1 credit per search, http://www.geonames.org/export/credits.html)
     *
     */
    class Geonames_API extends Rest_API
    {

        /** @var string The url to the lobid api. */
        public string $url = 'http://api.geonames.org/';


        //Overwrite
        function set_credentials()
        {
            $this->login = get_option('oes_api-geonames_login');
            //$this->password = '';
        }


        //Overwrite parent
        function get_request_url(string $url, array $args): string
        {
            $this->searchTerm = $args['search_term'] ?? '';

            if(!empty($this->searchTerm)) $url .= 'searchJSON?q=' . $this->searchTerm;
            elseif($args['lodid'] ?? false)  $url .= 'getJSON?geonameId=' . $args['lodid'];

            /* add size */
            $size = $args['oes-geonames-size'] ?? 10;
            if(!empty($size)) $url .= '&maxRows=' . $size;

            /* add user */
            if(!empty($this->login)) $url .= '&username=' . $this->login;

            //TODO url encode
            return $url;
        }


        //Overwrite parent
        function get_data_from_response($response)
        {
            return $response->geonames ?? [$response];
        }


        //Overwrite parent
        function transform_data_entry($entry):array
        {

            $entryID = $entry['geonameId']['value'] ?? false;
            return [
                'entry' => $entry,
                'id' => $entryID,
                'name' => $entry['name']['value'] ?? false,
                'type' => 'Geographikum',
                'link' => '<a class="oes-admin-link oes-gnd-external" href="https://www.geonames.org/' . $entryID .
                    '" target="_blank"></a>',
                'link_frontend' => '<a href="https://www.geonames.org/' . $entryID . '" target="_blank">' .
                    ($entry['name']['value'] ?? $entryID) . '</a>'
            ];
        }

        //Overwrite parent
        function get_data_for_display_prepare_html(string $title, string $table, $entry): string
        {

            $html = '<div class="oes-lod-box-title">' . $title . '</div>' . $table . 'hdsaf';

            return $html;
        }
    }
}