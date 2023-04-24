<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_shortcode('oesxmlbutton', 'oes_xml_shortcode');


/**
 * Create the html representation of a xml button.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string|null $content Content within the shortcode.
 *
 * @return string Return the html string representing a note.
 */
function oes_xml_shortcode(array $args = [], string $content = null): string
{
    /* get post id */
    global $post;
    return '<span class="' .
        ($args['class'] ?? 'oes-post-buttons') . '">' .
        '<a href="' . get_permalink($post->id) . '?format=xml" id="' .
        ($args['id'] ?? 'oes-create-xml') . '">' .
        ($args['label'] ?? __('Create XML', 'oes')) . '</a></span>';
}