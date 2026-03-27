<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\API\Display_Helper')) {

    /**
     * API Interface
     */
    class Display_Helper
    {

        /** @var string|mixed The displayed language. */
        public string $language = '';

        /**
         * @param array $args
         */
        function __construct(array $args = []) {
            if($args['language'] ?? false) {
                $this->language = $args['language'];
            }
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

            $table = '<table class="is-style-oes-simple">';
            foreach ($tableData as $row) {
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
            return $entry['link_frontend'] ?? ($entry['name'] ?? 'Entry name and link missing.');
        }

        /**
         * Get the modified table data for the preview box.
         *
         * @param mixed $entry The LOD entry.
         * @return mixed Return the modified entry data.
         */
        protected function get_table($entry){
            return $entry['entry'];
        }

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