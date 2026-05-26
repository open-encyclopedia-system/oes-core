<?php

namespace OES\Export;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Export\TEI_Export') && class_exists('\OES\Export\Export')) {


    /**
     * todo
     */
    class TEI_Export extends \OES\Export\Export
    {

        protected function prepare_data(): void
        {

        }
    }
}