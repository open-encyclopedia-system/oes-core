<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ROR_API', false)) {

    /**
     * ROR API integration
     */
    class ROR_API extends Rest_API
    {
        public string $identifier = 'ror';
        public string $url = 'https://api.ror.org/organizations';
        public string $url_single = 'https://api.ror.org/v2/organizations/';

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            return add_query_arg('query', urlencode($args['search_term']), $url);
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            return $this->url_single . $id;
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            if (isset($response->items)) {
                return ['items' => $response->items];
            }

            if($response->id ?? false) {
                return ['items' => [$response]];
            }

            return [];
        }

        /** @inheritdoc */
        protected function get_entry_id($entry, $item): string
        {
            return basename($entry['id']['value'] ?? '');
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string {
            return __('ROR Organization', 'oes');
        }

        /** @inheritdoc */
        protected function get_entry_name($entry, $item): string {
            return $item->names[0]->value ?? '';
        }

        /** @inheritdoc */
        protected function get_entry_link($entry, $item): string {
            return $item->id ?? '';
        }
    }
}