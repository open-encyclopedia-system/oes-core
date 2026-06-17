<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('GND_API', false)) {

    /**
     * Class GND_API
     * Dienst des Hochschulbibliothekenzentrum des Landes NRW
     * https://github.com/hbz/lobid-gnd
     *
     * query terms like this : 'http://lobid.org/gnd/search?q=london'
     * https://lobid.org/gnd/api
     *
     */
    class GND_API extends Rest_API
    {
        public string $identifier = 'gnd';
        public string $url = 'https://lobid.org/gnd/';
        public string $api_version = '1.1';

        /** @inheritdoc */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            $fieldFilter = (!empty($args['field']) && $args['field'] !== 'any') ? $args['field'] . ':' : '';
            $url .= 'search?q=' . $fieldFilter . urlencode($searchTerm);

            $options = [];
            if (!empty($args['oes-gnd-size'])) {
                $options['size'] = $args['oes-gnd-size'];
            }
            if (!empty($args['oes-gnd-type']) && $args['oes-gnd-type'] !== 'all') {
                $options['filter'] = 'type:' . $args['oes-gnd-type'];
            }

            foreach ($options as $key => $val) {
                $url .= '&' . $key . '=' . urlencode($val);
            }

            return $url;
        }

        /** @inheritdoc */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            return $url . $id . '.json';
        }

        /** @inheritdoc */
        protected function get_data_from_response($response): array
        {
            if (isset($response->member)) {
                return ['items' => $response->member];
            }

            return ['items' => [$response]];
        }

        /** @inheritdoc */
        protected function transform_data_entry($entry, $item): array
        {
            return [
                'entry' => $entry,
                'id' => $this->get_entry_id($entry, $item),
                'name' => $this->get_entry_name($entry, $item),
                'type' => $this->get_entry_type($entry, $item),
                'link' => $this->get_entry_link($entry, $item),
                'text' => $this->get_entry_link_text($entry, $item),
                'types' => $this->get_entry_types($item),
                'image' => $this->get_entry_image($item),
            ];
        }

        /** @inheritdoc */
        protected function get_entry_id($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'gndIdentifier');
        }

        /** @inheritdoc */
        protected function get_entry_name($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'preferredName');
        }

        /** @inheritdoc */
        protected function get_entry_link($entry, $item): string
        {
            $id = $this->get_entry_id($entry, $item);
            return 'https://d-nb.info/gnd/' . $id;
        }

        /** @inheritdoc */
        protected function get_entry_type($entry, $item): string
        {
            return implode(', ', $this->get_entry_types($item));
        }

        private function get_entry_types($item): array {
            return $item->type ?? [];
        }

        private function get_entry_image($item): string {
            $image = $item->depiction ?? null;

            if(!$image || !is_array($image) || empty($image[0]->thumbnail)){
                return '';
            }

            return '<img src="' . esc_url($image[0]->thumbnail) . '" alt="Depiction"/>';
        }
    }
}
