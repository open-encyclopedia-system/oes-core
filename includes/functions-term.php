<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Get the canonical (query-string based) link for a term.
 *
 * @param WP_Term|int|string $term     A WP_Term object, or a term ID/identifier
 *                                      accepted by get_term().
 * @param bool                $absolute Whether to prefix the link with the site URL.
 *                                      Default true.
 * @return string The canonical link, or an empty string if the term could
 *                 not be resolved.
 */
function oes_term_get_canonical_link(WP_Term|int|string $term, bool $absolute = true): string
{
    if (!($term instanceof WP_Term)) {
        $term = get_term($term);
    }

    if (!$term || is_wp_error($term)) {
        return '';
    }

    $taxonomy = $term->taxonomy;
    $slug = $term->slug;

    $queryVar = get_taxonomy($taxonomy)->query_var;

    $path = $queryVar
        ? ('?' . $queryVar . '=' . $slug)
        : ('?taxonomy=' . $taxonomy . '&term=' . $slug);

    return $absolute ? (get_site_url() . $path) : $path;
}