<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Retrieve data information (id and label) from shortcode.
 *
 * @param string $shortcode The shortcode.
 * @return array Return data as ['label', 'id']
 */
function gnd_retrieve_data_from_shortcode(string $shortcode): array
{
    /* get label and id */
    preg_match('/label="([^\"]*)"/', $shortcode, $label);
    preg_match('/id="([^\"]*)"/', $shortcode, $id);

    /* prepare return */
    return [
        'label' => $label[1] ?? false,
        'id' => $id[1] ?? false
    ];
}