<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Lobid_API')) {

    /**
     * Class Lobid_API
     * Dienst des Hochschulbibliothekenzentrum des Landes NRW
     * https://github.com/hbz/lobid-gnd
     *
     * query terms like this : 'http://lobid.org/gnd/search?q=london'
     * https://lobid.org/gnd/api
     *
     */
    class Lobid_API extends Rest_API
    {

        /** @var string The url to the lobid api. */
        public string $url = 'http://lobid.org/gnd/';

        /** @var bool|string The query parameter. */
        public $searchTerm = false;

        /** @var string The interface language. Default is german. Valid values are 'english', 'german'. */
        public string $language = 'german';

        /** @var mixed The json decoded API response. */
        public $data = [];

        /** @var string|bool The html representation of an image (depiction) for a gnd entry. */
        public $imageHTML = false;

        /** @var array $types The GND types */
        public array $types = [];


        /**
         * Overwrite parent
         * @param array $args Array containing search parameters such as
         *  'search_term'   : the query parameter,
         *  'field'         : adds a filter for a specific field to the query
         *  'options'       : adds filter to the query. Syntax must be 'key=value' in the query url.
         */
        function post(array $args = [])
        {
            /* set searchTerm */
            $this->searchTerm = $args['search_term'] ?? '';

            /* prepare request url */
            $requestURL = $this->url;
            if (!empty($this->searchTerm)) {

                /* add field filter */
                $fieldFilter = (($args['field'] && $args['field'] != 'any') ? $args['field'] . ':' : '');
                $requestURL .= 'search?q=' . $fieldFilter . $this->searchTerm;

                /* add more filter */
                if (isset($args['options']))
                    foreach ($args['options'] as $key => $option)
                        $requestURL .= '&' . $key . '=' . $option;
            } elseif (isset($args['gnd_id'])) $requestURL .= $args['gnd_id'] . '.json';

            /* post request */
            $response = wp_remote_get($requestURL, ['headers' => ['Content-Type' => 'application/json']]);

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
                        $this->data = json_decode($this->response);
                    }
                }
            }
        }


        //Overwrite parent
        function transform_data($args = [])
        {

            /* exit early if no data */
            if (!$this->data || $this->request_error) return $this->request_error ?? 'Response seems to be empty.';

            $transformedData = [];
            foreach ($this->data->member ?? [$this->data] as $entryKey => $entry) {

                /* loop through data and prepare for display */
                $transformedDataEntry = [];
                $propertyLabel = GND_Interface::PROPERTIES;
                foreach ($entry as $propertyKey => $property) {

                    /* skip context  */
                    if ($propertyKey === '@context') continue;

                    /* add types */
                    if($propertyKey === 'type') $this->types = $property;

                    /* add image */
                    if ($propertyKey === 'depiction') {

                        /* take first image TODO @nextRelease : can there be more images? */
                        if ($property[0]->thumbnail)
                            $this->imageHTML = '<img src="' . $property[0]->thumbnail . '" alt="Depiction"/>';
                        continue;
                    }

                    /* prepare property position */
                    $position = 10000 + ($propertyLabel[$propertyKey]['position'] ?? 8000);
                    $value = '';
                    $raw = null;

                    if ($propertyKey === 'describedBy' && $property->license->id) {
                        $label = $this->language === 'german' ? 'Lizenz' : 'license';
                        $value = oes_get_html_anchor($property->license->label ?? $property->license->id,
                            $property->license->id, false, false, '_blank');
                        $raw = $property->license->label ?? $property->license->id;
                    } else {

                        /* get label */
                        if ($propertyKey == 'id') {
                            $label = $this->language === 'german' ? 'GND-ID' : 'GND ID';
                            $position = 10011;
                        } elseif ($propertyKey == 'type') {
                            $label = $this->language === 'german' ? 'Typ' : 'Type';
                            $position = 10030;
                        } elseif ($propertyKey == 'sameAs') {
                            $label = $this->language === 'german' ? 'Siehe auch' : 'See also';
                            $position = 18001;
                        } elseif ($propertyKey == 'hasParent') {
                            $label = $this->language === 'german' ? 'Eltern' : 'Parents';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasGrandParent') {
                            $label = $this->language === 'german' ? 'Großeltern' : 'Grand Parents';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasChild') {
                            $label = $this->language === 'german' ? 'Kinder' : 'Children';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasGrandChild') {
                            $label = $this->language === 'german' ? 'Enkel:innen' : 'Grand Children';
                            $position = 11220;
                        } elseif ($propertyKey == 'hasSibling') {
                            $label = $this->language === 'german' ? 'Geschwister' : 'Siblings';
                            $position = 11220;
                        } else $label = $propertyLabel[$propertyKey]['label'][$this->language] ?? $propertyKey;

                        /* get value */
                        if (is_string($property)) {
                            $value = $raw = $property;
                            if ($propertyKey == 'id')
                                $value = oes_get_html_anchor($value, $value, false, false, '_blank');
                        } elseif (is_array($property)) {
                            $value = '<ul class="oes-gnd-box-list">';
                            foreach ($property as $singleProperty) {

                                if (is_string($singleProperty)) {
                                    if ($propertyKey == 'type') {
                                        $typeLabels = GND_Interface::TYPES;
                                        $value .= '<li>' .
                                            $typeLabels[$singleProperty]['labels'][$this->language] ?? $singleProperty .
                                            '</li>';
                                        $raw[] = $typeLabels[$singleProperty]['labels'][$this->language] ??
                                            $singleProperty;
                                    } else {
                                        $value .= '<li>' . $singleProperty . '</li>';
                                        $raw[] = $singleProperty;
                                    }
                                } elseif (is_object($singleProperty)) {
                                    $singleLabel = $singleProperty->label ?? false;
                                    if ($url = $singleProperty->url ?? false) {
                                        $value .= '<li>' .
                                            oes_get_html_anchor(($singleProperty->label ??
                                                ($singleProperty->id ?? $url)),
                                                $url, false, false, '_blank') . '</li>';
                                        $raw[] = $singleProperty->label ?? ($singleProperty->id ?? $url);
                                    } elseif ($singleLink = $singleProperty->id ?? false) {
                                        $value .= '<li>' .
                                            oes_get_html_anchor(
                                                ($singleProperty->collection->name ?? ($singleLabel ?? $singleLink)),
                                                $singleLink, false, false, '_blank') . '</li>';
                                        $raw[] = $singleProperty->collection->name ?? ($singleLabel ?? $singleLink);
                                    } elseif ($singleLabel) {
                                        $value .= '<li>' . $singleLabel . '</li>';
                                        $raw[] = $singleLabel;
                                    } else {
                                        $prepareValue = [];
                                        foreach ($singleProperty as $singlePropertyPart)
                                            if (is_array($singlePropertyPart))
                                                $prepareValue[] = implode(' ', $singlePropertyPart);
                                        $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                                        $raw[] = $prepareValue;
                                    }
                                } elseif (is_array($singleProperty)) {
                                    $prepareValue = [];
                                    foreach ($singleProperty as $singlePropertyPart)
                                        $prepareValue = implode(' ', $singlePropertyPart);
                                    $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                                    $raw = $prepareValue;
                                } else {
                                    $value .= $singleProperty;
                                    $raw = $singleProperty;
                                }
                            }
                            $value .= '</ul>';
                        } elseif (is_object($property)) {
                            $prepareValue = [];
                            foreach ($property as $propertyPart) $prepareValue[] = implode(' ', $propertyPart);
                            $value = implode(' ', $prepareValue);
                            $raw = $prepareValue;
                        }
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
                $transformedData[$entryKey] = $transformedDataEntry;
            }

            return $this->transformed_data = $transformedData;
        }


        /**
         * Prepare API response for frontend display.
         *
         * @param int $resultKey Only one entry will be considered. Take the first '0' if not specified.
         * @return string Return API response as html string.
         */
        function get_data_for_display(int $resultKey = 0): string
        {
            $consideredEntry = $this->transformed_data[$resultKey];

            /* prepare title */
            $title = oes_get_html_anchor(
                ($consideredEntry['preferredName']['value'] ?? $consideredEntry['gndIdentifier']['value']),
                'https://d-nb.info/gnd/' . ($consideredEntry['gndIdentifier']['value'] ?? ''),
                false, false, '_blank');


            /**
             * Filter the considered properties for the display of Lobid entry.
             *
             * @param array $entry The considered entry.
             * @param array $types The gnd types.
             */
            $consideredEntryModified = $consideredEntry;
            if(has_filter('oes/api_lobid_display_table'))
                $consideredEntryModified = apply_filters('oes/api_lobid_display_table', $consideredEntry, $this->types);

            /* prepare table */
            $table = '<table class="oes-gnd-box-table-data">';
            foreach ($consideredEntryModified as $row)
                $table .= '<tr><th>' . $row['label'] . '</th><td>' . $row['value'] . '</td></tr>';
            $table .= '</table>';



            /**
             * Filter the content for the display of a Lobid entry.
             *
             * @param string $title The title string.
             * @param string $table The table string.
             * @param string $imageHTML The html image string.
             * @param string $consideredEntry The considered entry.
             * @param array $types The gnd types.
             */
            $html = '<div class="oes-gnd-box-title">' . $title . '</div>' . $table;
            if(has_filter('oes/api_lobid_display_entry'))
                $html = apply_filters('oes/api_lobid_display_entry', $title, $table, $this->imageHTML, $consideredEntry,
                    $this->types);

            return $html;
        }
    }
}