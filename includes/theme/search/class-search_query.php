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
            add_filter('posts_join', [$this, 'extend_join']);
            add_filter('posts_where', [$this, 'extend_where']);
            add_filter('posts_distinct', [$this, 'make_distinct']);
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
         * Extends WHERE clause to search in content and postmeta, optionally accent-insensitive.
         *
         * @param string $where
         * @return string
         */
        public function extend_where(string $where): string
        {
            if ($this->should_return_early()) return $where;

            global $wpdb, $oes;

            $original = get_query_var('s');
            if (empty($original)) return $where;

            $likeOriginal = '%' . $wpdb->esc_like($original) . '%';

            // Collect postmeta keys to search
            $metaConditions = '';
            $metaKeys = [];

            if (!empty($oes->search['postmeta_fields'])) {
                foreach ($oes->search['postmeta_fields'] as $fields) {
                    if (is_array($fields)) {
                        foreach ($fields as $field) {
                            if (!in_array($field, ['title', 'content'], true)) {
                                $metaKeys[] = "'" . esc_sql($field) . "'";
                            }
                        }
                    }
                }

                if (!empty($metaKeys)) {
                    $metaKeyList = implode(',', $metaKeys);
                    $metaConditions = " OR (
                {$wpdb->postmeta}.meta_value LIKE '{$likeOriginal}'
                AND {$wpdb->postmeta}.meta_key NOT LIKE '^_%' ESCAPE '^'
                AND {$wpdb->postmeta}.meta_key IN ({$metaKeyList})
            )";
                }
            }

            // Replace the default post_title LIKE condition
            $pattern = "/\(\s*{$wpdb->posts}\.post_title\s+LIKE\s*('.*?')\s*\)/";
            $replacement = "(
        ({$wpdb->posts}.post_title LIKE '{$likeOriginal}') OR
        ({$wpdb->posts}.post_content LIKE '{$likeOriginal}')
        {$metaConditions}
    )";

            return preg_replace($pattern, $replacement, $where);
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
