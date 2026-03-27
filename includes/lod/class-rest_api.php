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

        /** @var string The interface language. Default is 'german'. Valid values are 'english', 'german'. */
        public string $language = 'german';

        /** @var string The post method. Default is 'get'. */
        public string $method = 'get';

        /** @var string|array The api response. */
        public $response = '';

        /** @var mixed The decoded API response. */
        public $data = [];

        /** @var array The transformed api response. */
        public array $transformed_data = [];

        /** @var string|bool The api request error message. */
        public $request_error = false;

        /** @var bool|string The query parameter. */
        public $search_term = false;

        /** @var string API version */
        public string $api_version = '1.0';

        /**
         * @param string $url
         * @param array $args
         */
        public function __construct(string $url = '', array $args = []) {

            if (!empty($url)) {
                $this->url = $url;
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
        protected function set_additional_parameters(array $args = []): void { /* override if needed */ }
        
        /**
         * Set the rest api credentials.
         * @return void
         */
        protected function set_credentials(): void { /* override in child if needed */ }

        /**
         * Perform GET or POST request
         */
        public function request(array $args = []): void {

            $requestURL = $this->get_request_url('', $args);
            $requestArgs = $this->get_request_args(['headers' => ['Content-Type' => 'application/json']], $args);

            if (!$requestURL || !$requestArgs || !is_array($requestArgs)) {
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
                    $this->request_error = wp_remote_retrieve_response_message($response) ?: "HTTP {$statusCode}";
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
        public function get(array $args = []): void {
            $this->request($args);
        }

        /**
         * Post a request to the api and return the response (optional: return the transformed response).
         *
         * @param array $args An array containing parameters for the request.
         * @return array|string The api response.
         */
        public function get_data(array $args = [])
        {
            $this->get($args);
            return $this->response ? $this->transform_data($args) : false;
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
            return $url;
        }

        /**
         * Prepare the request arguments.
         *
         * @param array $array The current request arguments.
         * @param array $args The post arguments.
         * @return mixed Return the modified request arguments.
         */
        protected function get_request_args(array $array, array $args = [])
        {
            if(!empty($this->login) && !empty($this->password)) {
                $array['Authorization'] = 'Basic ' . base64_encode($this->login . ':' . $this->password);
            }
            return $array;
        }

        /**
         * Retrieve data from response.
         *
         * @param mixed $response The api response.
         * @return mixed The modified api response.
         */
        protected function get_data_from_response($response)
        {
            return $response;
        }

        /**
         * Transform the api response.
         *
         * @param array $args An array containing parameters for the transformation.
         * @return array|string The transformed response.
         */
        protected function transform_data(array $args = [])
        {
            if ($this->request_error) {
                return $this->request_error;
            }

            if (!$this->data) {
                return [];
            }

            $transformed = [];
            $apiInterface = $this->identifier . '_Interface';
            $propertyLabel = (class_exists($apiInterface) ? $apiInterface::PROPERTIES : []);

            foreach ($this->data as $entryKey => $entry) {
                $transformedEntry = [];

                foreach ($entry as $propertyKey => $property) {

                    $position = 10000 + ($propertyLabel[$propertyKey]['position'] ?? 8000);
                    $raw = null;

                    $label = $propertyLabel[$propertyKey]['label'][$this->language] ?? $propertyKey;

                    if (is_string($property) || is_int($property)) {
                        $value = $raw = $property;
                    }
                    elseif (is_object($property)) {
                        $prepareValue = [];

                        foreach ($property as $propertyPart) {
                            $prepareValue[] = oes_normalize_to_string($propertyPart);
                        }

                        $value = implode(' ', $prepareValue);
                        $raw = $prepareValue;
                    }
                    else {
                        $value = 'missing';
                    }

                    $transformedEntry[$propertyKey] = [
                        'label' => $label,
                        'value' => $value,
                        'raw' => $raw,
                        'position' => $position
                    ];
                }

                $col = array_column($transformedEntry, 'position');
                array_multisort($col, SORT_ASC, $transformedEntry);

                $transformed[$entryKey] = $this->transform_data_entry($transformedEntry);
            }

            return $this->transformed_data = $transformed;
        }

        /**
         * Prepare single entry for processing.
         *
         * @param mixed $entry The LOD entry.
         * @return array The prepared entry.
         */
        protected function transform_data_entry($entry) : array
        {
            return [
                'entry' => $entry,
                'id' => $entry['id']['value'] ?? 'ID missing',
                'name' => $entry['name']['value'] ?? 'name missing',
                'type' => $entry['type']['value'] ?? 'type missing',
                'link' => $entry['link']['value'] ?? '#',
                'link_frontend' => $entry['link_frontend']['value'] ?? '#'
            ];
        }
    }
}