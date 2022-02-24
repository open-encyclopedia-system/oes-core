<?php


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Taxonomy')) {

    /**
     * Class OES_Taxonomy
     *
     * This class prepares a taxonomy term for display in the frontend theme.
     */
    class OES_Taxonomy extends OES_Object
    {

        /** @var string $taxonomy The taxonomy. */
        public string $taxonomy = '';

        /** @var bool $is_index_term Determines if term is an index term. */
        public bool $is_index_term = false;


        //Overwrite parent
        function add_class_variables(array $additionalParameters)
        {
            if ($term = get_term($this->object_ID)) {

                /* set taxonomy */
                $this->taxonomy = $term->taxonomy;

                /* get global OES instance parameter */
                $oes = OES();

                /* check if term is part of the index */
                $this->is_index_term = in_array($term->taxonomy, $oes->theme_index['objects'] ?? []);

                /* add general themes */
                if(isset($oes->theme_labels))
                    $this->theme_labels = array_merge($oes->theme_labels, $this->theme_labels);

                /* check for taxonomy specific labels */
                if (isset($oes->taxonomies[$this->taxonomy]['theme_labels']))
                    $this->theme_labels = $oes->taxonomies[$this->taxonomy]['theme_labels'];
            }
        }


        //Overwrite parent
        function set_title()
        {
            $this->title = oes_get_display_title(get_term($this->object_ID)) ?? $this->object_ID;
        }


        //Overwrite parent
        function prepare_html_main(array $args = []): array
        {
            /* prepare content array */
            $prepareContentArray = [];

            /* check for language */
            if (isset($args['language'])) $this->language = $args['language'];

            /* add index information */
            $prepareContentArray['index'] = $this->is_index_term ? $this->get_index_connections() : '';

            $contentArray = $this->modify_content([
                '400_index' => $prepareContentArray['index'] ?? ''
            ]);

            ksort($contentArray);
            return $contentArray;
        }


        //Overwrite parent
        function get_index_connected_posts($consideredPostType, $postRelationship): array
        {
            /* prepare data */
            $connectedPosts = [];

            /* get considered post type */
            if ($consideredPostType)
                $connectedPosts = get_posts([
                    'post_type' => $consideredPostType,
                    'numberposts' => -1,
                    'tax_query' => [[
                        'taxonomy' => $this->taxonomy,
                        'field' => 'term_id',
                        'terms' => $this->object_ID,
                        'include_children' => false
                    ]
                    ]
                ]);

            return [$connectedPosts];
        }
    }
}