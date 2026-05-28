<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\API\Display_Helper', false)) {

    /**
     * API Interface
     */
    class Display_Helper
    {
        /** @var string The API identifier. */
        public string $identifier = 'lod';

        /** @var string|mixed The displayed language. */
        public string $language = 'german';

        /**
         * @param array $args
         */
        public function __construct(array $args = []) {
            $this->language = $this->get_language($args['date_locale']);
            $this->identifier = $args['identifier'] ?? 'lod';
        }

        /**
         * Get entry language.
         *
         * @var string $locale The date locale.
         * @return string
         */
        protected function get_language(string $locale = 'de_DE'): string
        {
            return $locale === 'de_DE' ? 'german' : 'english';
        }

        /**
         * Prepare API response for frontend display.
         *
         * @param array $entry The LOD API entry.
         * @return string Return API response as HTML string.
         */
        public function html(array $entry): string
        {
            $title =  $this->get_title($entry);

            $tableData = $this->get_table($entry);
            $this->modify_table_data($tableData, $entry);

            $table = '<table class="is-style-oes-simple">';
            foreach ($tableData as $row) {

                if((!$row['frontend'] ?? true)){
                    continue;
                }

                $table .= '<tr><th>' . $row['label'] . '</th><td>' . $row['value'] . '</td></tr>';
            }
            $table .= '</table>';

            return $this->prepare_html($title, $table, $entry);
        }

        /**
         * Get the title for the preview box.
         *
         * @param mixed $entry The LOD entry.
         * @return mixed|string Return the title.
         */
        protected function get_title($entry){

            $text = $entry['text'] ?? __('Entry name missing.', 'oes');
            $link = $entry['link'] ?? '#';

            if(empty($link)){
               return $text;
            }

            return '<a href="' . $link . '" target="_blank">' . $text . '</a>';
        }

        /**
         * Get the modified table data for the preview box.
         *
         * @param mixed $entry The LOD entry.
         * @return mixed Return the modified entry data.
         */
        protected function get_table($entry){

            $popupOptions = get_option('oes_api-' . $this->identifier . '_popup_include', []);

            if (empty($popupOptions) || !is_array($popupOptions)) {
                return $entry['entry'] ?? [];
            }

            $entryData = $entry['entry'] ?? [];

            return array_intersect_key(
                $entryData,
                array_flip($popupOptions)
            );
        }

        /**
         * Modify table data.
         *
         * @param array $tableData
         * @return void
         */
        protected function modify_table_data(array &$tableData, array $entry): void
        {}

        /**
         * Prepare the HTML for the preview box.
         *
         * @param string $title The title.
         * @param string $table The HTML table string.
         * @param mixed $entry The LOD entry.
         * @return string
         */
        protected function prepare_html(string $title, string $table, $entry): string
        {
            return '<div class="oes-lod-box-title">' . $title . '</div>' . $table;
        }
    }
}