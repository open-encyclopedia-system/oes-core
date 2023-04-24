<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Page')) {

    /**
     * Class OES_Page
     *
     * This class prepares a page for display in the frontend theme.
     */
    class OES_Page extends OES_Post
    {

        //Overwrite parent
        public bool $has_theme_subtitle = true;
    }
}