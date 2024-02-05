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


        //Overwrite parent
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


        //Overwrite parent
        public function get_object_label(): string
        {
            global $oes, $oes_language;
            if (!empty($this->taxonomy))
                return $oes->taxonomies[$this->taxonomy]['label_translations_plural'][$oes_language] ??
                    ($oes->taxonomies[$this->taxonomy]['label'] ?? 'Label missing');
            return '';
        }


        //Overwrite parent
        public function loop_objects(): void
        {

            if (taxonomy_exists($this->taxonomy)) {

                /* prepare query args */
                $queryArgs = [
                    'taxonomy' => $this->taxonomy,
                    'hide_empty' => $this->hide_on_empty,
                    'childless' => $this->childless];
                if (isset($args['parent'])) $queryArgs['parent'] = $args['parent'];

                /* query terms */
                $terms = get_terms($queryArgs);

                /* loop through results */
                if ($terms) foreach ($terms as $term) $this->loop_results_term($term);
            }
        }
    }
}