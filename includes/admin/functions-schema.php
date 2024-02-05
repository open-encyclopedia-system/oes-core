<?php

namespace OES\Schema;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get parameter value from post type schema.
 *
 * @param string $postType The post type.
 * @param string $param The parameter key.
 * @return array|mixed|string Return the parameter value.
 */
function get_post_type_params(string $postType, string $param = '')
{
    $postTypeData = OES()->post_types[$postType] ?? [];
    if (empty($param)) return $postTypeData;
    elseif (!empty($postTypeData[$param])) return $postTypeData[$param];
    return '';
}