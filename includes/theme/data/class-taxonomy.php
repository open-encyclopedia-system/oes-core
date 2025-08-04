<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Taxonomy')) {

    /**
     * Class OES_Taxonomy
     *
     * @Legacy This class prepares a taxonomy term for display in the frontend theme. Replaced by OES_Term.
     */
    class OES_Taxonomy extends OES_Term
    {
    }
}