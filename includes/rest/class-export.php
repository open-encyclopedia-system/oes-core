<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Export')) {

    /**
     * Class Export
     *
     * Handles exporting post data in various formats (JSON, plain text, TEI/XML).
     */
    class Export
    {
        /** @var string The export mode, used to resolve the post preparation class. */
        protected string $mode;

        /** @var array Prepared data to be exported. */
        protected array $data;

        public function __construct(string $mode, array $args = [])
        {
            $this->mode = $mode;
            $this->set_parameters($args);
        }

        protected function set_parameters(array $args = []): void
        {
            //Empty on design since OES 3.0.0
        }

        public function export_json(): void
        {
            if (!isset($this->data)) {
                error_log('No data available');
                return;
            }

            $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        }

        public function export_txt(): void
        {
            if (!isset($this->data)) {
                error_log('No data available');
                return;
            }

            $txt = implode("\n", $this->data);
            header( 'Content-Type: text/plain; charset=utf-8' );
            echo $txt;
        }

        /**
         * @throws \DOMException
         */
        public function export_tei(int $postID): void
        {
            $preparedPost = $this->get_prepared_post($postID);

            if($preparedPost) {
                $preparedPost->echo_dom();
            }
        }

        public function prepare_post_data(int $postID): void
        {
            $preparedPost = $this->get_prepared_post($postID);
            $this->data = $preparedPost ? $preparedPost->get_data() : [];
        }

        protected function get_prepared_post(int $postID)
        {
            $class = $this->get_class_name();
            return $class ? new $class($postID) : null;

        }

        protected function get_class_name(): ?string
        {
            $class = (empty($this->mode) || $this->mode === 'raw')
                ? '\OES\Rest\Post'
                : '\OES\Rest\Post_' . $this->mode;

            if(!class_exists($class)) {
                error_log("Unknown export mode: {$this->mode}");
                return null;
            }

            return $class;
        }
    }
}