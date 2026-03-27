<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('GND_API')) {

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

        /** @inheritdoc */
        public string $identifier = 'gnd';
        public string $url = 'https://lobid.org/gnd/';
        public string $api_version = '1.1';

        /** @inheritdoc */
        function get_request_url(string $url, array $args): string
        {
            if(empty($url)){
                $url = $this->url;
            }

            $this->search_term = $args['search_term'] ?? '';

            if (!empty($this->search_term)) {
                $fieldFilter = (!empty($args['field']) && $args['field'] !== 'any') ? $args['field'] . ':' : '';
                $url .= 'search?q=' . $fieldFilter . urlencode($this->search_term);

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
            } elseif (!empty($args['lod_id'])) {
                $url .= $args['lod_id'] . '.json';
            }

            return $url;
        }

        /** @inheritdoc */
        public function transform_data(array $args = []): array|string
        {
            if (!$this->data || $this->request_error) {
                return $this->request_error ?: __('Empty response', 'oes');
            }

            $propertyLabels = GND_Interface::PROPERTIES;
            $typeLabels = GND_Interface::TYPES;

            $entries = $this->data->member ?? [$this->data];
            $transformed = [];

            foreach ($entries as $entryKey => $entry) {

                $image = '';
                $types = [];
                $transformedEntry = [];

                foreach ($entry as $propertyKey => $property) {

                    if ($propertyKey === '@context') {
                        continue;
                    }

                    if ($propertyKey === 'type') {
                        $types = $property;
                    }

                    if ($propertyKey === 'depiction') {
                        $image = $this->extract_image($property);
                        continue;
                    }

                    [$label, $position] = $this->resolve_label_and_position($propertyKey, $propertyLabels);

                    if ($propertyKey === 'describedBy' && $property->license->id) {
                        $value = oes_get_html_anchor($property->license->label ?? $property->license->id,
                            $property->license->id, false, false, '_blank');
                        $raw = $property->license->label ?? $property->license->id;
                    }
                    else {
                        [$value, $raw] = $this->format_property_value(
                            $propertyKey,
                            $property,
                            $typeLabels
                        );
                    }

                    $transformedEntry[$propertyKey] = [
                        'label' => $label,
                        'value' => $value,
                        'raw' => $raw,
                        'position' => $position
                    ];
                }

                $this->sort_by_position($transformedEntry);

                $entryID = $transformedEntry['gndIdentifier']['value'] ?? false;
                $entryTypesArray = $this->prepare_entry_types($transformedEntry);

                $transformed[$entryKey] = [
                    'entry' => $transformedEntry,
                    'id' => $entryID,
                    'name' => $transformedEntry['preferredName']['value'] ?? false,
                    'type' => implode(', ', $entryTypesArray),
                    'image' => $image,
                    'types' => $types,
                    'link' => 'https://d-nb.info/gnd/' . $entryID,
                    'link_frontend' => '<a href="https://d-nb.info/gnd/' . $entryID . '" target="_blank">' .
                        ($transformedEntry['preferredName']['value'] ?? $entryID) . '</a>'
                ];
            }

            return $this->transformed_data = $transformed;
        }

        /**
         * Extract property image.
         * @param $property
         * @return string
         */
        private function extract_image($property): string
        {
            if (is_array($property) && !empty($property[0]->thumbnail)) {
                return '<img src="' . esc_url($property[0]->thumbnail) . '" alt="Depiction"/>';
            }

            return '';
        }

        /**
         * Resolve property label and position.
         * @param string $propertyKey
         * @param array $propertyLabels
         * @return array
         */
        private function resolve_label_and_position(string $propertyKey, array $propertyLabels): array
        {
            $lang = $this->language === 'german' ? 'german' : 'english';

            $special = [
                'id' => ['label' => $lang === 'german' ? 'GND-ID' : 'GND ID', 'position' => 10011],
                'type' => ['label' => $lang === 'german' ? 'Typ' : 'Type', 'position' => 10030],
                'sameAs' => ['label' => $lang === 'german' ? 'Siehe auch' : 'See also', 'position' => 18001],
                'describedBy' => ['label' => $lang === 'german' ? 'Lizenz' : 'license', 'position' => 18002],
                'hasParent' => ['label' => $lang === 'german' ? 'Eltern' : 'Parents', 'position' => 11220],
                'hasGrandParent' => ['label' => $lang === 'german' ? 'Großeltern' : 'Grand Parents', 'position' => 11220],
                'hasChild' => ['label' => $lang === 'german' ? 'Kinder' : 'Children', 'position' => 11220],
                'hasGrandChild' => ['label' => $lang === 'german' ? 'Enkel:innen' : 'Grand Children', 'position' => 11220],
                'hasSibling' => ['label' => $lang === 'german' ? 'Geschwister' : 'Siblings', 'position' => 11220],
            ];

            if (isset($special[$propertyKey])) {
                return [$special[$propertyKey]['label'], $special[$propertyKey]['position']];
            }

            $label = $propertyLabels[$propertyKey]['label'][$this->language] ?? $propertyKey;
            $position = 10000 + ($propertyLabels[$propertyKey]['position'] ?? 8000);

            return [$label, $position];
        }

        /**
         * Format property.
         * @param string $propertyKey
         * @param $property
         * @param array $typeLabels
         * @return array
         */
        private function format_property_value(string $propertyKey, $property, array $typeLabels): array
        {
            if (is_string($property)) {

                $raw = $property;
                $value = $property;

                if ($propertyKey === 'id') {
                    $value = oes_get_html_anchor($property, $property, false, false, '_blank');
                }

                return [$value, $raw];
            }

            if (is_array($property)) {
                return $this->format_array_property($propertyKey, $property, $typeLabels);
            }

            if (is_object($property)) {
                $values = [];

                foreach ($property as $part) {
                    if (is_array($part)) {
                        $values[] = implode(' ', $part);
                    }
                }

                $value = implode(' ', $values);

                return [$value, $values];
            }

            return [$property, $property];
        }

        /**
         * Format array.
         * @param string $propertyKey
         * @param array $property
         * @param array $typeLabels
         * @return array
         */
        private function format_array_property(string $propertyKey, array $property, array $typeLabels): array
        {
            $value = '<ul class="oes-field-value-list">';
            $raw = [];

            foreach ($property as $item) {

                if (is_string($item)) {

                    if ($propertyKey === 'type') {
                        $label = $typeLabels[$item]['labels'][$this->language] ?? $item;
                        $value .= '<li>' . esc_html($label) . '</li>';
                        $raw[] = $label;
                    } else {
                        $value .= '<li>' . esc_html($item) . '</li>';
                        $raw[] = $item;
                    }
                }
                elseif (is_object($item)) {

                    $label = $item->label ?? $item->id ?? '';

                    if (!empty($item->url)) {
                        $value .= '<li>' . oes_get_html_anchor($label, $item->url, false, false, '_blank') . '</li>';
                        $raw[] = $item->label ?? ($item->id ?? $item->url);
                    } elseif (!empty($item->id)) {
                        $icon = !empty($item->collection->icon) ?
                            ('<img class="oes-gnd-sameas-links" src="' . $item->collection->icon . '" alt="oes-link-icon">') :
                            '';
                        $value .= '<li>' .
                            $icon .
                            oes_get_html_anchor(
                                ($item->collection->name ?? ($label ?? $item->id)),
                                $item->id, false, false, '_blank') . '</li>';
                        $raw[] = $item->collection->name ?? ($label ?? $item->id);
                    } elseif($label){
                        $value .= '<li>' . esc_html($label) . '</li>';
                        $raw[] = $label;
                    } else {
                        $prepareValue = [];
                        foreach ($item as $itemPart) {
                            if (is_array($itemPart)) {
                                $prepareValue[] = implode(' ', $itemPart);
                            }
                        }
                        $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                        $raw[] = $prepareValue;
                    }
                }
                elseif (is_array($item)) {
                    $prepareValue = [];
                    foreach ($item as $itemPart) {
                        $prepareValue = implode(' ', $itemPart);
                    }
                    $value .= '<li>' . implode(' ', $prepareValue) . '</li>';
                    $raw = $prepareValue;
                } else {
                    $value .= $item;
                    $raw = $item;
                }
            }

            $value .= '</ul>';

            return [$value, $raw];
        }

        /**
         * Sort transformed data by position.
         * @param array $entry
         * @return void
         */
        private function sort_by_position(array &$entry): void
        {
            uasort($entry, fn($a, $b) => $a['position'] <=> $b['position']);
        }

        /**
         * Prepare entry types.
         * @param array $entry
         * @return array
         */
        private function prepare_entry_types(array $entry): array
        {
            $types = [];

            foreach ($entry['type']['raw'] ?? [] as $type) {
                if ($type !== 'Authority Resource' && $type !== 'Normdatenressource') {
                    $types[] = $type;
                }
            }

            return $types;
        }
    }
}
