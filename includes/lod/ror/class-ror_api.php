<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('ROR_API')) {

    /**
     * ROR API integration
     */
    class ROR_API extends Rest_API
    {
        /** @inheritdoc */
        public string $identifier = 'ror';
        public string $url = 'https://api.ror.org/organizations';
        public string $url_single = 'https://api.ror.org/v2/organizations/';

        /** @inheritdoc */
        public function get_request_url(string $url, array $args): string
        {
            if (empty($url)) {
                $url = $this->url;
            }

            if ($args['lod_id'] ?? false) {
                return $this->url_single . $args['lod_id'];
            }

            $this->search_term = $args['search_term'] ?? '';

            if (!empty($args['search_term'])) {
                $url = add_query_arg('query', urlencode($args['search_term']), $url);
            }

            //TODO
            if (!empty($args['page'])) {
                $url = add_query_arg('page', intval($args['page']), $url);
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

            $properties = ROR_Interface::PROPERTIES;;

            $transformed = [];
            $lang = $this->language === 'german' ? 'german' : 'english';

            foreach ($this->data['items'] as $item) {

                $id = basename($item->id);

                $entry = (array) $item;
                $entry['rorID']['raw'] = $id;

                $transformedEntry = [];

                foreach ($properties as $propertyKey => $property) {

                    if (empty($entry[$propertyKey])) {
                        continue;
                    }

                    $rawProperty = $entry[$propertyKey];
                    $label = $property['label'][$lang] ?? $propertyKey;
                    $type = $property['type'] ?? 'string';

                    $value = '';
                    $raw = $rawProperty;

                    switch ($type) {

                        case 'array':
                            $value = is_array($rawProperty)
                                ? implode(', ', $rawProperty)
                                : '';
                            break;

                        case 'object':
                            $value = $this->transform_object_property($rawProperty, $property);
                            $raw = $value ? explode(', ', $value) : [];
                            break;

                        case 'number':
                            if (!empty($rawProperty['raw'])) {
                                $value = (string) $rawProperty['raw'];
                            } elseif (is_int($rawProperty)) {
                                $value = (string) $rawProperty;
                            }
                            break;

                        case 'string':
                        default:
                            $value = $rawProperty['raw'] ?? (is_string($rawProperty) ? $rawProperty : '');
                            break;
                    }

                    if ($value !== '') {
                        $transformedEntry[$propertyKey] = [
                            'label' => $label,
                            'value' => $value,
                            'raw'   => $raw
                        ];
                    }
                }

                $transformedEntry['rorID']['raw'] = $id;

                $transformed[] = [
                    'entry' => $transformedEntry,
                    'id' => $id,
                    'name' => $item->names[0]->value ?? '',
                    'type' => __('Organization', 'oes'),
                    'link' => $item->id,
                    'link_frontend' => '<a href="' . $item->id . '" target="_blank">' . $item->id . '</a>',
                ];
            }

            return $this->transformed_data = $transformed;
        }

        private function transform_object_property($rawProperty, $property): string
        {
            $subfields = $property['subfields'] ?? [];
            $pattern   = $property['pattern'] ?? null;

            $result = [];

            foreach ($rawProperty as $rawItem) {

                $collected = [];

                foreach ($subfields as $subfieldKey) {

                    [$field, $sub] = array_pad(explode(':', $subfieldKey), 2, null);

                    if (!property_exists($rawItem, $field)) {
                        continue;
                    }

                    $value = $sub
                        ? ($rawItem->{$field}->{$sub} ?? '')
                        : ($rawItem->{$field} ?? '');

                    if (empty($value)) {
                        continue;
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    if (!$pattern && $field !== 'value') {
                        $value = $field . ': ' . $value;
                    }

                    $collected[$subfieldKey] = $value;
                }

                if ($pattern) {
                    $formatted = $pattern;
                    foreach ($subfields as $sf) {
                        $formatted = str_replace('{' . $sf . '}', $collected[$sf] ?? '', $formatted);
                    }
                    $result[] = $formatted;
                } else {
                    $result[] = implode(', ', $collected);
                }
            }

            return implode(', ', $result);
        }

        /** @inheritdoc */
        public function get_data_from_response($response): array
        {
            if (isset($response->items)) {
                return ['items' => (array)$response->items];
            }

            if($response->id ?? false) {
                return ['items' => [(array)$response]];
            }

            return [];
        }
    }
}