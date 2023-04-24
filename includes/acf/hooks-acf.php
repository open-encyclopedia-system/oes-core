<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_filter('acf/fields/relationship/query', 'oes_acf_fields_relationship_query', 10, 3);


/**
 * Filters the query $args used by WP_Query to display posts in the Relationship field. Modify acf filter to exclude self reference.
 *
 * @param array $args The query args. See WP_Query for available args.
 * @param array $field The field array containing all settings.
 * @param int|string $post The current post ID being edited.
 * @return array Returns the modified query args.
 */
function oes_acf_fields_relationship_query(array $args, array $field, $post): array
{
    $args['post__not_in'] = [$post];
    return $args;
}