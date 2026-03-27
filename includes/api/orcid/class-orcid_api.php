<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ORCID_API')) {

    /**
     * ORCID API integration
     */
    class ORCID_API extends Rest_API
    {
        /** @inheritdoc */
        public string $identifier = 'ror';
        public string $url = 'https://pub.orcid.org/v3.0/search/';
        public string $url_single = 'https://pub.orcid.org/v3.0/';

        /** @inheritdoc */
        public function get_request_url(string $url, array $args): string
        {
            if (empty($url)) {
                $url = $this->url;
            }

            if ($args['lod_id'] ?? false) {
                return $this->url_single . $args['lod_id'] . '/person';
            }

            $this->search_term = $args['search_term'] ?? '';

            if (!empty($args['search_term'])) {
                $query = sprintf(
                    'given-names:%1$s OR family-name:%1$s OR credit-name:%1$s',
                    $args['search_term']
                );
                $url = add_query_arg('q', urlencode($query), $url);
            }

            if (!empty($args['page'])) {
                $rows = 10;
                $start = (intval($args['page']) - 1) * $rows;

                $url = add_query_arg('rows', $rows, $url);
                $url = add_query_arg('start', $start, $url);
            }

            return $url;
        }

        /** @inheritdoc */
        public function transform_data(array $args = [])
        {
            if ($this->request_error) {
                return $this->request_error;
            }

            if (!$this->data || !isset($this->data['items'])) {
                return [];
            }

            $properties = ORCID_Interface::PROPERTIES;;

            $transformed = [];

            foreach ($this->data['items'] as $item) {

                $path = $item->{'orcid-identifier'}->path;
                $uri = $item->{'orcid-identifier'}->uri;

                $transformed[] = [
                    'entry' => [],
                    'id' => $path,
                    'name' => $path,
                    'type' => __('', 'oes'),
                    'link' => $uri,
                    'link_frontend' => '<a href="' . $uri . '" target="_blank">' . $path . '</a>',
                ];
            }

            return $this->transformed_data = $transformed;
        }

        /** @inheritdoc */
        public function get_data_from_response($response): array
        {
            if (isset($response->result)) {
                return ['items' => (array)$response->result];
            }

            //TODO
            if($response->id ?? false) {
                return ['items' => [(array)$response]];
            }

            return [];
        }
    }
}