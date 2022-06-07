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

        /** @var string $taxonomy_label The taxonomy label. */
        public string $taxonomy_label = '';

        /** @var bool $oes_is_index_term Determines if term is an index term. */
        public bool $oes_is_index_term = false;


        //Overwrite parent
        function add_class_variables(array $additionalParameters)
        {
            if ($term = get_term($this->object_ID)) {

                /* get global OES instance parameter */
                global $oes;

                /* set taxonomy */
                $this->taxonomy = $term->taxonomy;

                /* set taxonomy label */
                if ($this->taxonomy) {
                    $cleanLanguage = ($this->language === 'all' || empty($this->language)) ? 'language0' : $this->language;
                    $this->taxonomy_label = $oes->taxonomies[$this->taxonomy]['label_translations'][$cleanLanguage] ??
                        ($oes->taxonomies[$this->taxonomy]['label'] ??
                            (get_taxonomy($this->taxonomy)->labels->singular_name ?? 'Label missing'));
                }

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
                    'post_status' => 'any',
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


/**
 * Set term data for OES Taxonomy object.
 *
 * @param array $args Additional parameters. Valid parameters are:
 *  'term-id'  : The term id.
 */
function oes_set_term_data(array $args = [])
{
    /* get global parameters */
    global $taxonomy, $term, $oes_language, $oes_term;

    /* get the term id */
    $termID = $args['term-id'] ?? false;

    /* get term object */
    $cleanLanguage = $oes_language === 'all' ? 'language0' : $oes_language;
    if(!$termID || !get_term($termID)){
        $termID = get_term_by('slug', $term, $taxonomy)->term_id ?? false;
    }
    $oes_term = class_exists($taxonomy) ?
        new $taxonomy($termID, $cleanLanguage) :
        new OES_Taxonomy($termID, $cleanLanguage);
}