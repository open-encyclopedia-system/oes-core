<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Taxonomy')) {

    /**
     * @deprecated 3.0.0 Use OES_Term instead.
     */
    class OES_Taxonomy extends OES_Term
    {
        public function __construct(int $objectID, string $language = '', array $args = [])
        {
            _deprecated_class( __CLASS__, '3.0.0', 'OES_Term' );
            parent::__construct($objectID, $language, $args);
        }
    }

}