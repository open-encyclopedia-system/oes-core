<?php

namespace OES\API;

if (!class_exists('\OES\API\Geonames_Display_Helper')) :
    class Geonames_Display_Helper extends Display_Helper
    {

        /** @inheritdoc */
        protected function get_table($entry)
        {

            $modifiedEntryKeys = ['geonameId', 'name', 'countryCode', 'countryName', 'lng', 'lat', 'wikipediaURL'];

            $modifiedEntry = [];
            foreach($modifiedEntryKeys as $key) {
                if (isset($entry['entry'][$key])) {
                    $modifiedEntry[$key] = $entry['entry'][$key];
                }
            }

            return $modifiedEntry;
            return $entryData;
        }

        /** @inheritdoc */
        protected function prepare_html(string $title, string $table, mixed $entry): string
        {
            return '<div class="oes-lod-box-title">' . $title . '</div>' . $table;
        }
    }
endif;