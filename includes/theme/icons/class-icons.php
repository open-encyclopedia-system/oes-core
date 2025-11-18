<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Icon;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


if (!class_exists('\OES\Icon\Icons')) {

    /**
     * Concrete icon set for use in the theme or plugin.
     */
    class Icons extends Manager
    {

        /**
         * Returns SVG for "arrow up" icon.
         *
         * @return string
         */
        protected function arrow_up(): string {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round"
     class="icon icon-arrow-up" aria-hidden="true" focusable="false">
    <path d="M12 19V5M5 12l7-7 7 7"/>
</svg>
SVG;
        }

        /**
         * Returns SVG for "arrow up" caret (^).
         *
         * @return string
         */
        protected function arrow_caret_up(): string {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round"
     class="icon icon-arrow-caret-up" aria-hidden="true" focusable="false">
    <path d="M6 15l6-6 6 6"/>
</svg>
SVG;
        }

        /**
         * Returns SVG for "arrow right" icon.
         *
         * @return string
         */
        protected function arrow_right(): string {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round"
     class="icon icon-arrow-right" aria-hidden="true" focusable="false">
    <path d="M5 12h14M13 6l6 6-6 6"/>
</svg>
SVG;
        }

        /**
         * Returns SVG for "arrow left" icon.
         *
         * @return string
         */
        protected function arrow_left(): string {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round"
     class="icon icon-arrow-left" aria-hidden="true" focusable="false">
    <path d="M19 12H5m6-6-6 6 6 6"/>
</svg>
SVG;
        }

        /**
         * Returns SVG for "list" icon.
         *
         * @return string
         */
        protected function list(): string {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
     fill="none" stroke="currentColor" stroke-width="2"
     stroke-linecap="round" stroke-linejoin="round"
     class="icon icon-list" aria-hidden="true" focusable="false">
    <line x1="8" y1="6" x2="21" y2="6"/>
    <line x1="8" y1="12" x2="21" y2="12"/>
    <line x1="8" y1="18" x2="21" y2="18"/>
    <line x1="3" y1="6" x2="3" y2="6"/>
    <line x1="3" y1="12" x2="3" y2="12"/>
    <line x1="3" y1="18" x2="3" y2="18"/>
</svg>
SVG;
        }
    }
}
