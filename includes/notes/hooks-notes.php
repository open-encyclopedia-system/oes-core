<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('the_content', 'oes_notes_render_for_frontend');
add_filter('oes/the_content', 'oes_notes_render_for_frontend');


/**
 * Render an OES note inside the frontend.
 *
 * @param string $content The OES note.
 * @return string Return rendered OES note.
 */
function oes_notes_render_for_frontend(string $content): string
{

    $additional = 'class=""';
    if (has_filter('oes/notes_render_for_frontend'))
        $additional = apply_filters('oes/notes_render_for_frontend', $additional);

    $content = str_replace('<oesnote>', ('[oesnote ' . $additional . ']'), $content);
    return str_replace('</oesnote>', '[/oesnote]', $content);
}