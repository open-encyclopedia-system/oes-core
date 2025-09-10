<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Search_Query')) {

    /**
     * Class OES_Search_Query
     *
     * Extends WordPress search functionality to include postmeta fields
     * and to support accent-insensitive search (if enabled).
     */
    class OES_Search_Query
    {
        /**
         * Whether search should treat accents as significant.
         * If false, the search includes both original and accent-free terms.
         *
         * @var bool
         */
        protected bool $accent_sensitive = false;

        /**
         * OES_Search_Query constructor.
         *
         * @param array $args ['accent_sensitive' => bool]
         */
        public function __construct(array $args = [])
        {
            $this->set_args($args);
        }

        /**
         * Sets configuration options.
         *
         * @param array $args
         */
        protected function set_args(array $args): void
        {
            $this->accent_sensitive = $args['accent_sensitive'] ?? false;
        }

        /**
         * Registers search-related hooks.
         */
        public function register_hooks(): void
        {
            add_action('pre_get_posts', function (\WP_Query $query) {
                if (!is_admin() && $query->is_main_query() && $query->is_search()) {
                    add_filter('posts_join', [$this, 'extend_join']);
                    add_filter('posts_distinct', [$this, 'make_distinct']);
                    add_filter('posts_search', [$this, 'posts_search'], 10, 2);

                }
            });
        }

        /**
         * Adds JOIN clause for postmeta if needed.
         *
         * @param string $join
         * @return string
         */
        public function extend_join(string $join): string
        {
            if ($this->should_return_early()) return $join;

            global $wpdb, $oes;

            if (!empty($oes->search['postmeta_fields'])) {
                $join .= " LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) ";
            }

            return $join;
        }

        /**
         * Ensures distinct results when joining postmeta.
         *
         * @param string $distinct
         * @return string
         */
        public function make_distinct(string $distinct): string
        {
            return $this->should_return_early() ? $distinct : 'DISTINCT';
        }

        /**
         * Filters the SQL WHERE clause of the main WordPress search query to support:
         * 1. Apostrophe normalization – converts various apostrophe-like characters
         *    (e.g., `´`, `‘`, `’`, `‛`) into wildcards to improve search matching.
         * 2. Optional meta field searching – allows searching in additional postmeta
         *    fields defined in $oes->search['postmeta_fields'].
         *
         * The method modifies the search to match partial terms using SQL LIKE,
         * automatically adding wildcards (%) if the search term contains apostrophes.
         *
         * @param string   $search The current SQL WHERE clause for the search.
         * @param WP_Query $query  The WP_Query instance for the current query.
         *
         * @return string The modified SQL WHERE clause with apostrophe-aware and
         *                meta field search applied.
         *
         * @global wpdb   $wpdb Global WordPress database object.
         * @global object  $oes  Global object containing search settings and meta fields.
         */
        public function posts_search(string $search, WP_Query $query): string
        {
            if ($this->should_return_early() || !$query->is_main_query()) {
                return $search;
            }

            global $wpdb, $oes;

            $original = (string)$query->get('s');
            if ($original === '') {
                return $search;
            }

            $apostrophes = oes_get_apostrophe_variants(true);

            // detect whether the user typed any of those characters
            $charClass = preg_quote(implode('', $apostrophes), '/');
            $hasApostroph = (bool)preg_match("/[{$charClass}]/u", $original);

            if ($hasApostroph) {
                $pattern = str_replace($apostrophes, '%', $original);
                $pattern = preg_replace('/%+/u', '%', $pattern);
                if ($pattern === '' || $pattern === '%') {
                    $like = '%';
                } else {
                    if ($pattern[0] !== '%') $pattern = '%' . $pattern;
                    if (substr($pattern, -1) !== '%') $pattern .= '%';
                    $like = $pattern;
                }
            } else {
                $like = '%' . $wpdb->esc_like($original) . '%';
            }

            $metaKeys = [];
            if (!empty($oes->search['postmeta_fields'])) {
                foreach ($oes->search['postmeta_fields'] as $fields) {
                    foreach ((array)$fields as $field) {
                        if (!in_array($field, ['title', 'content', 'excerpt'], true)) {
                            $metaKeys[] = "'" . esc_sql($field) . "'";
                        }
                    }
                }
            }

            $metaSql = '';
            if ($metaKeys) {
                $metaKeyList = implode(',', $metaKeys);
                $metaSql = " OR (
            {$wpdb->postmeta}.meta_value LIKE %s
            AND {$wpdb->postmeta}.meta_key NOT LIKE '^_%' ESCAPE '^'
            AND {$wpdb->postmeta}.meta_key IN ({$metaKeyList})
        )";
            }

            $sql = "
        AND (
            ({$wpdb->posts}.post_title LIKE %s)
            OR ({$wpdb->posts}.post_excerpt LIKE %s)
            OR ({$wpdb->posts}.post_content LIKE %s)
            {$metaSql}
        )
    ";

            $params = [$like, $like, $like];
            if ($metaKeys) {
                $params[] = $like;
            }

            return $wpdb->prepare($sql, ...$params);
        }

        /**
         * Checks whether this is a frontend search query (early exit otherwise).
         *
         * @return bool
         */
        protected function should_return_early(): bool
        {
            return !is_search() || is_admin();
        }
    }
}
