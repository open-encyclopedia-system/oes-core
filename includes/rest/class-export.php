<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Rest\Export')) {


    /**
     */
    class Export
    {
        protected string $mode;

        protected array $data;

        public function __construct(string $mode, array $args = [])
        {
            $this->mode = $mode; //TODO validate
            $this->set_parameters($args);
        }

        protected function set_parameters(array $args = []): void
        {
            // Implement in child class
        }

        public function export_json(): void
        {
            $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
        }

        public function export_txt(): void
        {
            $txt = implode("\n", $this->data);
            header( 'Content-Type: text/plain; charset=utf-8' );
            echo $txt;
        }

        public function export_tei(int $postID): void
        {
            if(!class_exists('\OES\Rest\Post_TEI')) {
                return;
            }

            $preparedPost = new \OES\Rest\Post_TEI($postID);
            $preparedPost->echo_dom();
        }

        public function prepare_post_data(int $postID, bool $dom = false): void
        {
            $class = '\OES\Rest\Post_' . $this->mode;

            if(class_exists($class)) {
                $preparedPost = new $class($postID);
            }
            else {
                $preparedPost = new \OES\Rest\Post($postID);
            }

            $this->data = $preparedPost->get_data();
        }
    }
}