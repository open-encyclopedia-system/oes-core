<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('template_redirect', 'oes_xml_template_redirect', 8);


/**
 * Redirect for file generation.
 * @return void
 */
function oes_xml_template_redirect(): void
{
    /* redirect single for post types where archive are displayed as full list */
    if (!is_admin() && isset($_GET['format']) && is_single())
        if ($_GET['format'] === 'xml') {
            oes_export_post_to_xml();
            exit();
        } elseif ($_GET['format'] === 'rdf') {
            oes_export_post_to_xml(['rdf' => true]);
            exit();
        }
}

