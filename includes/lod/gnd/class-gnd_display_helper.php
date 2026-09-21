<?php

namespace OES\API;

if (!class_exists('\OES\API\GND_Display_Helper')) {
    class GND_Display_Helper extends Display_Helper
    {

        /** @inheritdoc */
        protected function modify_table_data(array &$tableData, array $entry): void
        {
            $types = $entry['types'] ?? [];

            if (has_filter('oes/api_gnd_display_table')) {
                 $tableData = apply_filters('oes/api_gnd_display_table', $tableData, $types);
                 return;
            }

            if (!empty($tableData['variantName']['raw'])) {
                $variantNames = array_slice($tableData['variantName']['raw'], 0, 10);

                $amount = count($tableData['variantName']['raw']);
                $limit = 10;
                if ($amount > $limit) {
                    $variantNames[] = '... +' . ($amount - 10);
                }

                $tableData['variantName']['value'] =
                    '<ul class="oes-field-value-list"><li>' .
                    implode('</li><li>', $variantNames) .
                    '</li></ul>';
            }

            $this->combine_date_and_place(
                $tableData,
                'dateOfBirth',
                'placeOfBirth',
                $this->language === 'german' ? 'Geboren' : 'Birth'
            );

            $this->combine_date_and_place(
                $tableData,
                'dateOfDeath',
                'placeOfDeath',
                $this->language === 'german' ? 'Verstorben' : 'Death'
            );

            $this->combine_date_and_place(
                $tableData,
                'dateOfConferenceOrEvent',
                'placeOfConferenceOrEvent',
                $this->language === 'german' ? 'Datum' : 'Date'
            );
        }

        /**
         * Combine data and Place into one metadatum.
         *
         * @param array $data
         * @param string $dateKey
         * @param string $placeKey
         * @param string $label
         * @return void
         */
        protected function combine_date_and_place(array &$data, string $dateKey, string $placeKey, string $label): void
        {
            if (empty($data[$dateKey]) && empty($data[$placeKey])) {
                return;
            }

            $date = '';
            if (!empty($data[$dateKey]['raw'][0])) {
                $rawDate = $data[$dateKey]['raw'][0];

                $date = str_contains($rawDate, '-')
                    ? oes_convert_date_to_formatted_string($rawDate)
                    : $rawDate;
            }

            $place = !empty($data[$placeKey]['value'])
                ? ', <span class="oes-lod-table-additional">' . $data[$placeKey]['value'] . '</span>'
                : '';

            $data[$dateKey]['label'] = $label;
            $data[$dateKey]['value'] = $date . $place;

            unset($data[$placeKey]);
        }

        /** @inheritdoc */
        protected function prepare_html(string $title, string $table, mixed $entry): string
        {

            if (has_filter('oes/api_gnd_display_entry')) {
                return apply_filters('oes/api_gnd_display_entry', $title, $table, $entry['entry']);
            }

            $additional = [
                'biographicalOrHistoricalInformation',
                'definition',
                'id'
            ];

            $additionalInfo = '';
            foreach ($additional as $key) {

                $property = $entry['entry'][$key] ?? null;

                if(empty($property)) {
                    continue;
                }

                $additionalInfo .= sprintf('<div class="oes-lod-box-inner">
                    <h4 class="oes-content-table-header">%s</h4>
                    <div>%s</div>
                    </div>',
                    $property['label'],
                    $property['value']);
            }

            $imageHTML = '';
            if (!empty($entry['image'])) {
                $imageHTML = '<div class="oes-lod-box-image-inner">' . $entry['image'] . '</div>';
            }

            return '<div class="oes-lod-box-title">' .
                '<h3 class="oes-content-table-header">' . $title . '</h3>' .
                '</div>' .
                '<div class="oes-lod-box-container-inner">' .
                $imageHTML .
                '<div class="oes-lod-box-content">' . $table . '</div>' .
                '</div>' .
                '<div class="oes-lod-box-additional-info">' . $additionalInfo . '</div>';

        }
    }
}
