<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Registers WordPress search modification hooks for custom search behavior.
 *
 * This function initializes a project-specific `OES_Search_Query` class and applies
 * filters to modify WordPress's default search to include postmeta fields. It is called
 * by an OES theme.
 *
 * @param array $args Optional arguments:
 *  - 'accent_sensitive' => bool
 *  - 'case_sensitive'   => bool
 * @return void
 */
function oes_theme_modify_search(array $args = []): void
{
    $class = oes_get_project_class_name('OES_Search_Query');
    $search = new $class($args);
    $search->register_hooks();
}

/**
 * Creates and executes the main OES search engine logic.
 *
 * This is the main function to instantiate the `OES_Search` class which holds logic
 * for collecting post IDs and initial filtering, before results are looped.
 *
 * @param array $args Optional arguments for the search engine. Example:
 *  - 'post-id' => (int) Specific post ID to narrow the search.
 * @return OES_Search An instance of the active OES_Search handler.
 */
function oes_get_search_data(array $args = []): OES_Search
{
    $class = oes_get_project_class_name('OES_Search');
    return new $class($args);
}

/**
 * Loops through prepared search result post IDs and formats them into a full array.
 *
 * Typically called after `oes_get_search_data()` to get full search data,
 * including highlighted fields, filters, post type grouping, etc.
 *
 * @param array $args {
 *     Optional arguments.
 *
 *     @type array $prepared_ids List of post IDs to include in the results.
 *     @type string $search_term The original search term.
 * }
 * @param array $additionalArgs {
 *     Optional extra control options.
 *
 *     @type bool $sort_by_language Sort results by language (default true).
 *     @type bool $sort_by_post_type Sort by post type (default true).
 *     @type bool $accent_sensitive Enable accent-sensitive matching (default false).
 *     @type bool $case_sensitive Enable case-sensitive matching (default true).
 * }
 * @return array An array of structured search results:
 * {
 *     'search_term'     => string,
 *     'characters'      => string[],
 *     'prepared_posts'  => array,
 *     'post_ids'        => int[],
 *     'count'           => int,
 *     'filter'          => array,
 *     'filter_array'    => array,
 * }
 */
function oes_search_get_results(array $args = [], array $additionalArgs = []): array
{
    global $oes_is_search;

    $class = oes_get_project_class_name('OES_Search_Results');
    $searchHandler = new $class($args, $additionalArgs);
    $searchHandler->loop_results();
    $oes_is_search = true;

    return $searchHandler->to_array();
}
