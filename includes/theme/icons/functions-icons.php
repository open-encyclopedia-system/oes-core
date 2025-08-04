<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Icon;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Returns an SVG icon from the current theme's icon set.
 *
 * @param string $name The icon name (e.g. 'arrow_right')
 * @return string SVG markup or fallback comment
 */
function get(string $name = ''): string
{
    // Resolve class name from child theme or fallback
    $class = oes_get_project_class_name('\OES\Icon\Icons');

    if (!class_exists($class)) {
        return "<!-- Icon class '{$class}' not found -->";
    }

    $icons = new $class();

    if (!($icons instanceof Manager)) {
        return "<!-- '{$class}' is not an instance of OES\\Icon\\Manager -->";
    }

    return $icons->get_icon($name);
}