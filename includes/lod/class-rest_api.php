<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Rest_API')) {

    /**
     * Rest API
     */
    class Rest_API
    {

        /** @var string The api identifier. */
        public string $identifier = '';

        /** @var string The url to the api. */
        public string $url = '';

        /** @var string The url to the api for a single request. */
        public string $url_single = '';

        /** @var string The login information. */
        public string $login = '';

        /** @var string The login password. */
        public string $password = '';

        /** @var string The interface language date locale. Default is british english. */
        public string $date_locale = 'en_BE';

        /** @var string The interface language. */
        public string $language = '';

        /** @var string The post method. Default is 'get'. */
        public string $method = 'get';

        /** @var string The api response. */
        public string $response = '{}';

        /** @var array The decoded API response. */
        public array $data = [];

        /** @var array The transformed api response. */
        public array $transformed_data = [];

        /** @var string|bool The api request error message. */
        public $request_error = false;

        /** @var string API version */
        public string $api_version = '1.0';

        /**
         * @param string $url
         * @param array $args
         */
        public function __construct(string $url = '', array $args = [])
        {

            if (!empty($url)) {
                $this->url = $url;
            }

            if(!empty($args['date_locale'] ?? '')){
                $this->date_locale = $args['date_locale'];
            }

            $this->set_credentials();
            $this->set_additional_parameters($args);
        }

        /**
         * Set additional parameters.
         *
         * @param array $args Additional parameters.
         * @return void
         */
        protected function set_additional_parameters(array $args = []): void
        { /* override if needed */
        }

        /**
         * Set the rest api credentials.
         * @return void
         */
        protected function set_credentials(): void
        { /* override in child if needed */
        }

        /**
         * Perform GET or POST request
         */
        public function request(array $args = []): void
        {
            $requestURL = $this->get_request_url('', $args);
            $requestArgs = $this->get_request_args(['headers' => ['Content-Type' => 'application/json']], $args);

            if (!$requestURL || !$requestArgs) {
                return;
            }

            $response = $this->method === 'post'
                ? wp_remote_post($requestURL, $requestArgs)
                : wp_remote_get($requestURL, $requestArgs);

            if (is_wp_error($response)) {
                $this->request_error = $response->get_error_message();
            } else {
                $statusCode = wp_remote_retrieve_response_code($response);
                if ($statusCode !== 200) {
                    $this->request_error = wp_remote_retrieve_response_message($response) ?: "HTTP $statusCode";
                } else {
                    $this->response = wp_remote_retrieve_body($response);
                    $this->data = $this->get_data_from_response(json_decode($this->response));
                }
            }

            oes_write_log($this->request_error, $this->identifier . ' API');
        }

        /**
         * Get data from API.
         * @param array $args
         * @return void
         */
        public function get(array $args = []): void
        {
            $this->request($args);
        }

        /**
         * Post a request to the api and return the response (optional: return the transformed response).
         *
         * @param array $args An array containing parameters for the request.
         * @return array The api response.
         */
        public function get_data(array $args = []): array
        {
            $this->get($args);
            return $this->response ? $this->transform_data() : [];
        }

        /**
         * Get the request url.
         *
         * @param string $url The api url.
         * @param array $args The post arguments.
         * @return string The request url.
         */
        protected function get_request_url(string $url, array $args): string
        {
            if(empty($url)){
                $url = $this->url;
            }

            $searchTerm = $args['search_term'] ?? false;
            $id = $args['lod_id'] ?? false;

            if($searchTerm) {
                return $this->get_request_url_search($url, $args, $searchTerm);
            }
            elseif($id) {
                return $this->get_request_url_single($url, $args, $id);
            }

            return $url;
        }

        /**
         * Construct the request url for search.
         *
         * @param string $url The api url.
         * @param array $args The post arguments.
         * @param string $searchTerm The search term.
         * @return string The request url.
         */
        protected function get_request_url_search(string $url, array $args, string $searchTerm): string
        {
            return $url;
        }

        /**
         * Construct the request url for single record.
         *
         * @param string $url The api url.
         * @param array $args The post arguments.
         * @param string $id The record ID.
         * @return string The request url.
         */
        protected function get_request_url_single(string $url, array $args, string $id): string
        {
            return $url;
        }

        /**
         * Prepare the request arguments.
         *
         * @param array $array The current request arguments.
         * @param array $args The post arguments.
         * @return array Return the modified request arguments.
         */
        protected function get_request_args(array $array, array $args = []): array
        {
            if (!empty($this->login) && !empty($this->password)) {
                $array['Authorization'] = 'Basic ' . base64_encode($this->login . ':' . $this->password);
            }
            return $array;
        }

        /**
         * Retrieve data from response.
         *
         * @param mixed $response The api response.
         * @return array|array[] The modified api response.
         */
        protected function get_data_from_response($response): array
        {
            return ['items' => [$response]];
        }

        /**
         * Transform the api response.
         *
         * @return array The transformed response.
         */
        protected function transform_data(): array
        {
            if ($this->request_error) {
                return [];
            }

            if (!$this->data || !isset($this->data['items'])) {
                return [];
            }

            $apiInterface = '\\OES\\API\\' . $this->identifier . '_Interface';
            $properties = (class_exists($apiInterface) ? $apiInterface::PROPERTIES : []);
            $lang = $this->get_entry_language();

            $transformedData = [];
            foreach ($this->data['items'] as $item) {

                $transformed = [];

                foreach ($properties as $propertyKey => $property) {

                    $rawProperty = match (true) {
                        is_object($item) => $item->$propertyKey ?? null,
                        is_array($item) => $item[$propertyKey] ?? null,
                        default => null,
                    };

                    if (is_null($rawProperty)) {
                        continue;
                    }

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
                                $value = (string)$rawProperty['raw'];
                            } elseif (is_int($rawProperty)) {
                                $value = (string)$rawProperty;
                            }
                            break;

                        case 'string':
                        default:
                            if (!empty($rawProperty['raw'])) {
                                $value = (string)$rawProperty['raw'];
                            } elseif (is_int($rawProperty)) {
                                $value = (string)$rawProperty;
                            } elseif (is_string($rawProperty)) {
                                $value = $rawProperty;
                            } elseif(is_object($rawProperty) || is_array($rawProperty)) {
                                $value = $this->transform_object_property($rawProperty, $property);
                            }
                            break;
                    }

                    if ($value !== '') {
                        $transformed[$propertyKey] = [
                            'label' => $label,
                            'value' => $value,
                            'raw' => $raw,
                            'frontend' => $property['frontend'] ?? true,
                        ];
                    }
                }

                $transformedData[] = $this->transform_data_entry($transformed, $item);
            }

            return $this->transformed_data = $transformedData;
        }

        /**
         * Get entry language.
         * 
         * @return string
         */
        protected function get_entry_language(): string
        {
            return $this->date_locale === 'de_DE' ? 'german' : 'english';
        }

        /**
         * Transform the object property.
         *
         * @param object|array $rawProperty
         * @param array        $property
         * @param string       $separator
         * @return string
         */
        protected function transform_object_property($rawProperty, array $property, string $separator = ', '): string
        {
            $subfields     = $property['subfields'] ?? [];
            $subProperties = $property['subproperties'] ?? [];
            $pattern       = $property['pattern'] ?? null;

            if (!empty($subProperties)) {
                $collected = $this->collect_fields($rawProperty, $subProperties, $separator);

                return $this->format_result($collected, $subProperties, $pattern, $separator);
            }

            $results = [];

            foreach ($rawProperty as $rawItem) {
                if (!empty($subfields)) {
                    $collected = $this->collect_fields($rawItem, $subfields, $separator);
                } else {
                    $collected = $this->collect_generic($rawItem);
                }

                $results[] = $this->format_result($collected, $subfields, $pattern, $separator);
            }

            return implode($separator, array_filter($results));
        }

        /**
         * Collect values based on field definitions.
         */
        private function collect_fields($source, array $fields, string $separator): array
        {
            $collected = [];

            foreach ($fields as $fieldKey) {
                [$field, $sub] = array_pad(explode(':', $fieldKey), 2, null);

                if (!is_object($source) || !property_exists($source, $field)) {
                    continue;
                }

                $value = $sub
                    ? ($source->{$field}->{$sub} ?? null)
                    : ($source->{$field} ?? null);

                $value = $this->normalize_value($value, $separator);

                if ($value !== null && $value !== '') {
                    $collected[$fieldKey] = $value;
                }
            }

            return $collected;
        }

        /**
         * Collect values when no subfields are defined.
         */
        private function collect_generic($rawItem): array
        {
            if (is_string($rawItem)) {
                return [$rawItem];
            }

            if (is_array($rawItem) || is_object($rawItem)) {
                $result = [];

                foreach ($rawItem as $key => $value) {
                    if ($key === 'id') {
                        continue;
                    }

                    $result[] = oes_normalize_to_string($value);
                }

                return $result;
            }

            return [];
        }

        /**
         * Normalize value into string.
         */
        private function normalize_value($value, string $separator): ?string
        {
            if (empty($value)) {
                return null;
            }

            if (is_array($value)) {
                return implode($separator, $value);
            }

            if (is_object($value)) {
                return implode($separator, (array) $value);
            }

            return is_string($value) ? $value : null;
        }

        /**
         * Apply pattern or join values.
         */
        private function format_result(array $collected, array $fields, ?string $pattern, string $separator): string
        {
            if ($pattern) {
                $formatted = $pattern;

                foreach ($fields as $field) {
                    $formatted = str_replace(
                        '{' . $field . '}',
                        $collected[$field] ?? '',
                        $formatted
                    );
                }

                return $formatted;
            }

            return implode($separator, $collected);
        }
        
        /**
         * Prepare single entry for processing.
         *
         * @param mixed $entry The LOD entry.
         * @return array The prepared entry.
         */
        protected function transform_data_entry($entry, $item): array
        {
            return [
                'entry' => $entry,
                'id' => $this->get_entry_id($entry, $item),
                'name' => $this->get_entry_name($entry, $item),
                'type' => $this->get_entry_type($entry, $item),
                'link' => $this->get_entry_link($entry, $item),
                'text' => $this->get_entry_link_text($entry, $item)
            ];
        }

        /**
         * Get entry parameter.
         *
         * @param $entry
         * @param $parameterKey
         * @return string
         */
        protected function get_entry_parameter($entry, $parameterKey): string {
            $parameter = $entry[$parameterKey]['value'] ?? null;

            return is_string($parameter) && $parameter !== ''
                ? $parameter
                : "Parameter $parameterKey missing";
        }

        /**
         * Get entry ID.
         * 
         * @param $entry
         * @param $item
         * @return string
         */
        protected function get_entry_id($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'id');
        }

        /**
         * Get entry name.
         * 
         * @param $entry
         * @param $item
         * @return string
         */
        protected function get_entry_name($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'name');
        }

        /**
         * Get entry type.
         * 
         * @param $entry
         * @param $item
         * @return string
         */
        protected function get_entry_type($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'type');
        }

        /**
         * Get entry link.
         *
         * @param $entry
         * @param $item
         * @return string
         */
        protected function get_entry_link($entry, $item): string
        {
            return $this->get_entry_parameter($entry, 'link');
        }

        /**
         * Get entry link text.
         *
         * @param $entry
         * @param $item
         * @return string
         */
        protected function get_entry_link_text($entry, $item): string
        {
            return $this->get_entry_name($entry, $item);
        }
    }
}