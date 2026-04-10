<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOC_API')) {

    /**
     * Class LOC_API
     * https://www.loc.gov/apis/json-and-yaml/
     *
     * example:
     * https://id.loc.gov/search/?q=book&q=cs:http://id.loc.gov/authorities/subjects&format=json (Subject Headings)
     * unused: https://www.loc.gov/search/?q=baseball&fo=json
     *
     * Limitations:
     * @oesDevelopment Validate limits
     *
     * Newspapers endpoint:
     * Burst Limit    20 requests per 1 minute, Block for 5 minutes
     * Crawl Limit:    20 requests per 10 seconds, Block for 1 hour
     * Item and resource endpoints:
     * Burst Limit:    40 requests per 10 seconds, Block for 5 minutes
     * Crawl Limit    200 requests per 1 minute, Block for 1 hour
     * Collections, format, and other endpoints:
     * Burst Limit    20 requests per 10 seconds, Block for 5 minutes
     * Crawl Limit    80 requests per 1 minute, Block for 1 hour
     *
     */
    class LOC_API extends Rest_API
    {
        public string $identifier = 'loc';
        public string $url = 'https://id.loc.gov/';

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            $query = 'q=' . urlencode($searchTerm)
                . '&q=' . urlencode('cs:http://id.loc.gov/authorities/subjects')
                . '&format=json';

            return $url . 'search/?' . $query;
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            return $url . 'authorities/subjects/' . rawurlencode($id) . '.json';
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            $items = [];

            if (!is_array($response)) {
                return [];
            }

            foreach ($response as $entry) {

                if (!is_array($entry)) {
                    continue;
                }

                if($entry[0] !== 'atom:entry'){
                    continue;
                }

                $title = '';
                $link = '';

                foreach($entry as $property){

                    if ($title && $link) {
                        break;
                    }

                    if (!is_array($property)) {
                        continue;
                    }

                    $first = $property[0] ?? null;

                    if ($first === 'atom:title' && isset($property[2]) && is_string($property[2])) {
                        $title = $property[2];
                    }

                    if ($first === 'atom:link' && isset($property[1]) && is_object($property[1]) && property_exists($property[1], 'href')) {
                        $link = $property[1]->href;
                    }
                }

                if ($title && $link) {
                    $items[] = [
                        'id' => basename($link),
                        'name' => $title,
                        'link' => $link
                    ];
                }
            }

            return ['items' => $items];
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string
        {
            return __('LOC Subject', 'oes');
        }
    }
}