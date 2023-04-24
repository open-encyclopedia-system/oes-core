<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LOC_API')) {

    /**
     * @oesDevelopment
     *
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

        /** @var string The url to the loc api. */
        public string $url = 'https://id.loc.gov/';


        //Overwrite parent
        function get_request_url(string $url, array $args): string
        {
            $this->searchTerm = $args['search_term'] ?? '';

            if (!empty($this->searchTerm)) $url .= 'search/?q=' . $this->searchTerm . '&q=cs:http://id.loc.gov/authorities/subjects&format=json';
            elseif ($args['lodid'] ?? false) $url .= $args['lodid'];

            return $url;
        }


        //Overwrite parent
        function get_data_from_response($response): array
        {
            $data = [];
            foreach ($response as $entry)
                if (is_array($entry) && $entry[0] === "atom:entry") {

                    $name = false;
                    $link = false;
                    foreach ($entry as $entryData)
                        if (is_array($entryData) &&
                            $entryData[0] === "atom:link" &&
                            isset($entryData[1]) &&
                            $entryData[1]->href &&
                            !$entryData[1]->type
                        ) {
                            $link = $entryData[1]->href;
                            if ($name) break;
                        } elseif (is_array($entryData) &&
                            $entryData[0] === "atom:title" &&
                            isset($entryData[2]) &&
                            is_string($entryData[2])) {
                            $name = $entryData[2];
                            if ($link) break;
                        }

                    if ($name && $link)
                        $data[] = [
                            'name' => $name,
                            'link' => $link
                        ];
                }

            return $data;
        }


        //Overwrite parent
        function transform_data_entry($entry): array
        {
            $link = $entry['link']['value'] ?? false;
            $name = $entry['name']['value'] ?? false;
            $id = substr($link, strrpos($link, '/') + 1);
            return [
                'entry' => $entry,
                'id' => $id,
                'name' => $name,
                'type' => 'Subject Heading',
                'link' => '<a class="oes-admin-link oes-gnd-external" href="' . $link .
                    '" target="_blank"></a>',
                'link_frontend' => '<a href="https://catalog.loc.gov/vwebv/search?searchArg=' .
                    $name . '&searchCode=SKEY%5E*&searchType=1" target="_blank">' .
                    $name . '</a>'
            ];
        }
    }
}