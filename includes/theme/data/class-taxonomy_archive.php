<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Taxonomy_Archive') && class_exists('OES_Archive')) {


    /**
     * Class OES_Taxonomy_Archive
     *
     * This class prepares an archive of taxonomies for display in the frontend theme.
     */
    class OES_Taxonomy_Archive extends OES_Archive
    {

        /** @var string $taxonomy A string containing the taxonomy. */
        public string $taxonomy = '';

        /** @var bool Hide term if not connected to any post. */
        public bool $hide_on_empty = true;

        /** @var bool Consider only childless terms. */
        public bool $childless = true;

        /** @var bool Consider only terms that have no parent term. */
        public bool $only_first_level = false;


        /** @inheritdoc */
        public function set_parameters(array $args = []): void
        {
            /* Set taxonomy */
            $thisTaxonomy = $args['taxonomy'] ?? '';
            if (empty($thisTaxonomy)) {
                global $taxonomy;
                if (!is_null($taxonomy)) $thisTaxonomy = $taxonomy;
            }
            $this->taxonomy = $thisTaxonomy;

            if (isset($args['hide_on_empty'])) $this->hide_on_empty = $args['hide_on_empty'];
        }


        /** @inheritdoc */
        public function prepare_filter(array $args): array
        {
            $taxonomy = $args['taxonomy-for-filter'] ?? $this->taxonomy;

            global $oes;
            $filterArray = [];
            if (!empty($taxonomy))
                foreach ($oes->taxonomies[$taxonomy]['archive_filter'] ?? [] as $filter) {
                    if ($filter === 'alphabet') $filterArray['alphabet'] = true;
                }
            return $filterArray;
        }


        /** @inheritdoc */
        public function get_object_label(): string
        {
            global $oes, $oes_language;
            if (!empty($this->taxonomy))
                return $oes->taxonomies[$this->taxonomy]['label_translations_plural'][$oes_language] ??
                    ($oes->taxonomies[$this->taxonomy]['label'] ?? 'Label missing');
            return '';
        }


        /** @inheritdoc */
        public function loop_objects(): void
        {

            if (taxonomy_exists($this->taxonomy)) {

                /* prepare query args */
                $queryArgs = $this->modify_query_args([
                    'taxonomy' => $this->taxonomy,
                    'hide_empty' => $this->hide_on_empty,
                    'childless' => $this->childless]);

                /* query terms */
                $terms = get_terms($queryArgs);

                /* loop through results */
                if ($terms) foreach ($terms as $term)
                    if(!$this->only_first_level || !$term->parent) $this->loop_results_term($term);
            }
        }


        /**
         * Modify the query args.
         *
         * @param array $args The current query args.
         * @return array The modified query args.
         */
        public function modify_query_args(array $args = []): array {
            return $args;
        }
    }
}