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

        /** @var string|array The api response. */
        public $response = '';

        /** @var string|bool The api request error message. */
        public $request_error = false;

        /** @var array The transformed api response. */
        public array $transformed_data = [];

        /** @var string The login information. */
        public string $login = '';

        /** @var string The login password. */
        public string $password = '';

        /** @var mixed The json decoded API response. */
        public $data = [];

        /** @var bool|string The query parameter. */
        public $searchTerm = false;

        /** @var string The interface language. Default is 'german'. Valid values are 'english', 'german'. */
        public string $language = 'german';

        /** @var string The post method. Default is 'get'. */
        public string $method = 'get';


        /**
         * Rest_API constructor.
         *
         * @param string $url The url to the api.
         */
        function __construct(string $url = '')
        {
            if (!empty($url)) $this->url = $url;
            $this->set_credentials();
        }


        /**
         * Set the rest api credentials.
         * @return void
         */
        function set_credentials(): void
        {
        }


        /**
         * Put a request to the api (prepare posting the request).
         *
         * @param array $args An array containing parameters for the request.
         * @return void
         */
        function put(array $args = []): void
        {
        }


        /**
         * Post a request to the api and retrieve api response.
         *
         * @param array $args An array containing parameters for the request.
         * @return void
         */
        function post(array $args = []): void
        {

            /* post request */
            $requestURL = $this->get_request_url($this->url, $args);
            $requestArgs = $this->get_request_args(['headers' => ['Content-Type' => 'application/json']], $args);

            //Exit early
            if(!$requestURL || !$requestArgs || !is_array($requestArgs)) return;

            if($this->method == 'post') $response = wp_remote_post($requestURL, $requestArgs);
            else $response = wp_remote_get($requestURL, $requestArgs);

            /* check if wp request was successful */
            if (is_wp_error($response))
                $this->request_error = $response->get_error_message();
            else {

                /* Check for other request errors or retrieve data */
                if (isset($resonse['response']['code']) && $resonse['response']['code'] !== '200')
                    $this->request_error = $resonse['response']['message'] ?? $resonse['response']['code'];
                else {
                    if ($this->request_error !== false)
                        $this->response = 'Error: ' . $this->request_error;
                    else {
                        $this->response = wp_remote_retrieve_body($response);
                        $this->data = $this->get_data_from_response(json_decode($this->response));
                    }
                }
            }
        }


        /**
         * Prepare the request url.
         *
         * @param string $url The api url.
         * @param array $args The post arguments.
         * @return string The request url.
         */
        function get_request_url(string $url, array $args): string
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
        function get_request_args(array $array, array $args = [])
        {
            if(!empty($this->login) && !empty($this->password))
                $array['Authorization'] = 'Basic ' . base64_encode($this->login . ':' . $this->password);
            return $array;
        }


        /**
         * Retrieve data from response.
         *
         * @param mixed $response The api response.
         * @return mixed The modified api response.
         */
        function get_data_from_response($response)
        {
            return $response;
        }


        /**
         * Prepare and post a request to the api (set api response).
         *
         * @param array $args An array containing parameters for the request.
         * @return void
         */
        function get(array $args = []): void
        {
            $this->put($args);
            $this->post($args);
        }


        /**
         * Post a request to the api and return the response (optional: return the transformed response).
         *
         * @param array $args An array containing parameters for the request.
         * @return array|string The api response.
         */
        function get_data(array $args = [])
        {
            $this->get($args);
            return $this->response ? $this->transform_data($args) : false;
        }


        /**
         * Transform the api response.
         *
         * @param array $args An array containing parameters for the transformation.
         * @return array|string The transformed response.
         */
        function transform_data(array $args = [])
        {
            /* exit early if no data */
            if ($this->request_error) return $this->request_error;
            if (!$this->data) return [];

            $transformedData = [];
            foreach ($this->data as $entryKey => $entry) {

                /* loop through data and prepare for display */
                $transformedDataEntry = [];
                $apiInterface = $this->identifier . '_Inteface';
                $propertyLabel = (class_exists($apiInterface) ? $apiInterface::PROPERTIES : []);
                foreach ($entry as $propertyKey => $property) {

                    /* prepare property position */
                    $position = 10000 + ($propertyLabel[$propertyKey]['position'] ?? 8000);
                    $raw = null;

                    /* get label */
                    $label = $propertyLabel[$propertyKey]['label'][$this->language] ?? $propertyKey;

                    /* get value */
                    if (is_string($property) || is_int($property)) {
                        $value = $raw = $property;
                    }
                    elseif (is_object($property)) {
                        $prepareValue = [];
                        foreach ($property as $propertyPart) {
                            $prepareValue[] = is_string($propertyPart) ? $propertyPart : implode(' ', $propertyPart);
                        }
                        $value = implode(' ', $prepareValue);
                        $raw = $prepareValue;
                    }
                    else {
                        $value = 'missing';
                    }

                    /* prepare position */
                    $transformedDataEntry[$propertyKey] = [
                        'label' => $label,
                        'value' => $value,
                        'raw' => $raw,
                        'position' => $position
                    ];
                }

                /* sort after position */
                $col = array_column($transformedDataEntry, 'position');
                array_multisort($col, SORT_ASC, $transformedDataEntry);

                /* prepare transformed data */
                $transformedData[$entryKey] = $this->transform_data_entry($transformedDataEntry);
            }

            return $this->transformed_data = $transformedData;
        }


        /**
         * Prepare single entry for processing.
         *
         * @param mixed $entry The LOD entry.
         * @return array The prepared entry.
         */
        function transform_data_entry($entry) : array
        {
            return [
                'entry' => $entry,
                'id' => 'ID missing',
                'name' => 'name missing',
                'type' => 'type missing',
                'link' => 'link missing',
                'link_frontend' => 'link frontend missing'
            ];
        }


        /**
         * Prepare API response for frontend display.
         *
         * @param int $resultKey Only one entry will be considered. Take the first '0' if not specified.
         * @return string Return API response as html string.
         */
        function get_data_for_display(int $resultKey = 0): string
        {
            $entry = $this->transformed_data[$resultKey];

            /* prepare title */
            $title =  $this->get_data_for_display_title($entry);

            /* prepare table */
            $tableData = $this->get_data_for_display_modify_table_data($entry);
            $table = '<table class="is-style-oes-simple">';
            foreach ($tableData as $row)
                $table .= '<tr><th>' . $row['label'] . '</th><td>' . $row['value'] . '</td></tr>';
            $table .= '</table>';

            return $this->get_data_for_display_prepare_html($title, $table, $entry);
        }


        /**
         * Get the title for the preview box.
         *
         * @param mixed $entry The LOD entry.
         * @return mixed|string Return the title.
         */
        function get_data_for_display_title($entry){
            return $entry['link_frontend'] ?? ($entry['name'] ?? 'Entry name and link missing.');
        }


        /**
         * Get the modified table data for the preview box.
         *
         * @param mixed $entry The LOD entry.
         * @return mixed Return the modified entry data.
         */
        function get_data_for_display_modify_table_data($entry){
            return $entry['entry'];
        }


        /**
         * Prepare the html for the preview box.
         *
         * @param string $title The title.
         * @param string $table The html table string.
         * @param mixed $entry The LOD entry.
         * @return string
         */
        function get_data_for_display_prepare_html(string $title, string $table, $entry): string
        {
            return '<div class="oes-lod-box-title">' . $title . '</div>' . $table;
        }
    }
}