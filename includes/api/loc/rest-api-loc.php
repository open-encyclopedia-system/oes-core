<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOC_API')) {

    /**
     * Class LOC_API
     * https://www.loc.gov/apis/json-and-yaml/
     *
     * example: TODO In Entwicklung!
     * https://www.loc.gov/search/?q=baseball&fo=json
     *
     * Limitations: TODO
     *
    Newspapers endpoint:
    Burst Limit 	20 requests per 1 minute, Block for 5 minutes
    Crawl Limit: 	20 requests per 10 seconds, Block for 1 hour
    Item and resource endpoints:
    Burst Limit: 	40 requests per 10 seconds, Block for 5 minutes
    Crawl Limit 	200 requests per 1 minute, Block for 1 hour
    Collections, format, and other endpoints:
    Burst Limit 	20 requests per 10 seconds, Block for 5 minutes
    Crawl Limit	80 requests per 1 minute, Block for 1 hour
     *
     */
    class LOC_API extends Rest_API
    {

        /** @var string The url to the lobid api. */
        public string $url = 'https://www.loc.gov/';


        //Overwrite parent
        function get_request_url(string $url, array $args): string
        {
            $this->searchTerm = $args['search_term'] ?? '';

            if(!empty($this->searchTerm)) $url .= 'search/?q=' . $this->searchTerm . '&fo=json';
            elseif($args['lodid'] ?? false)  $url .= $args['lodid'];

            /* add size */
            //$size = $args['oes-loc-size'] ?? 10;
            //if(!empty($size)) $url .= '&maxRows=' . $size;


            //TODO url encode
            return $url;
        }

        //Overwrite parent
        function get_data_from_response($response)
        {
            return $response->geonames ?? [$response];
        }

    }
}