<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('HMML_API', false)) {

    /**
     * HMML API integration
     *
     * Documentation: https://api.haf.vhmml.org/swagger-ui/index.html?configUrl=/v3/api-docs/swagger-config#/public-controller/search
     */
    class HMML_API extends Rest_API
    {
        public string $identifier = 'hmml';
        public string $url = 'https://api.haf.vhmml.org/authorities/browse/';
        public string $url_single = 'https://api.haf.vhmml.org/';

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
           //TODO multiple types? types=PLACE&types=WORK
            $type = $args['oes-hmml-type'] ?? 'all';

            $types = $type !== 'all'
                ? strtoupper($type)
                : '';

            //TODO encoding for arabic characters urlencode()?
            $searchTerm = str_replace(' ', '+', $searchTerm);

            return $url . $searchTerm . '?types=' . $types;
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            //TODO: what happens, if no valid type?
            $validTypes = ['work', 'place', 'person', 'organization', 'note', 'family', 'expression'];
            $type = 'none';

            $additional = array_filter(explode(';', $args['additional'] ?? ''));

            foreach ($additional as $param) {

                if (!is_string($param) || !str_contains($param, ':')) {
                    continue;
                }

                [$key, $value] = explode(':', $param, 2);

                if ($key === 'type') {
                    $value = strtolower(trim($value));

                    if (in_array($value, $validTypes, true)) {
                        $type = $value;
                    }

                    break;
                }
            }


            return $this->url_single . $type . '/' . $id;
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            if(is_object($response) && property_exists($response, 'hmmlID')) {
                return ['items' => [$response]];
            }
            elseif(is_array($response)) {
                return ['items' => $response];
            }

            return [];
        }

        /** @inheritdoc */
        protected function get_entry_id($entry, $item): string
        {
            return $entry['hmmlID']['value'] ?? '';
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string {
            return $entry['primaryEntityType']['value'] ?? '';
        }

        /** @inheritdoc */
        protected function get_entry_name($entry, $item): string {
            return $entry['primaryDisplayName']['value'] ?? '';
        }

        /** @inheritdoc */
        protected function get_entry_link($entry, $item): string {
            return 'https://haf.vhmml.org/' . $item->primaryEntityType . '/' . $item->hmmlID;
        }

        /** @inheritdoc */
        protected function get_entry_additional($entry, $item): string
        {
            return 'type:' . $item->primaryEntityType;
        }
    }
}