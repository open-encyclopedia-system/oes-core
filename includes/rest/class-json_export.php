<?php

namespace OES\Export;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Export\JSON_Export') && class_exists('\OES\Export\Export')) {


    /**
     * todo
     */
    class JSON_Export extends \OES\Export\Export
    {

        protected function prepare_data(): void
        {
            $this->prepare_site_info();
            $this->prepare_post_info();
            $this->prepare_featured_image();
            $this->prepare_terms();
            $this->prepare_fields();
        }
    }
}