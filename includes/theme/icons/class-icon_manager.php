<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Icon;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


if (!class_exists('\OES\Icon\Manager')) {

    /**
     * Abstract base class for managing inline SVG icons.
     */
    abstract class Manager
    {

        /**
         * Retrieves an SVG icon by name.
         *
         * @param string $name Icon identifier, e.g. 'arrow_right'
         * @return string Inline SVG markup or fallback HTML comment
         */
        public function get_icon(string $name): string {
            static $cache = [];

            if (isset($cache[$name])) {
                return $cache[$name];
            }

            $method = strtolower($name);
            $cache[$name] = method_exists($this, $method)
                ? $this->$method()
                : $this->icon_fallback($name);

            return $cache[$name];
        }

        /**
         * Fallback output for undefined icons.
         *
         * @param string $name The requested icon name
         * @return string HTML comment as fallback
         */
        protected function icon_fallback(string $name): string {
            return "<!-- Icon '{$name}' not found -->";
        }
    }
}
