<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ORCID_API')) {

    /**
     * ORCID API integration
     */
    class ORCID_API extends Rest_API
    {
        public string $identifier = 'orcid';
        public string $url = 'https://pub.orcid.org/v3.0/';

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            $query = sprintf(
                'given-names:%1$s OR family-name:%1$s OR credit-name:%1$s',
                $args['search_term']
            );
            return add_query_arg('q', urlencode($query), $url . 'expanded-search/');
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            return $this->url . $id . '/record';
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            if (property_exists($response, 'expanded-result')) {
                return ['items' => (array)$response->{"expanded-result"}];
            }

            if(property_exists($response, 'orcid-identifier')) {
                return ['items' => [(array)$response]];
            }

            return [];
        }

        /** @inheritdoc */
        protected function get_entry_id($entry, $item): string
        {
            $entryID = $entry['orcid-identifier']['value'] ?? '';

            if(!empty($entryID)) {
                return $entryID;
            }

            if(property_exists($item, 'orcid-id')) {
                return $item->{'orcid-id'};
            }

            return '';
        }

        /** @inheritdoc */
        protected function get_entry_name($entry, $item): string
        {
            $name = $entry['person']['value'] ?? '';

            if(!empty($name)) {
                return $name;
            }

            $names[] = $entry['given-names']['value'] ?? '';
            $names[] = $entry['family-names']['value'] ?? '';
            return trim(implode(' ', $names));
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string {
            return __('ORCID', 'oes');
        }

        /** @inheritdoc */
        protected function get_entry_link($entry, $item): string {
            $id = $this->get_entry_id($entry, $item);
            return 'https://orcid.org/' . $id;
        }
    }
}