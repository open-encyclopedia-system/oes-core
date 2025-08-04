<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Index_Archive') && class_exists('OES_Archive')) {


    /**
     * Class OES_Index_Archive
     *
     * This class prepares an index archive of post types and taxonomies for display in the frontend theme.
     */
    class OES_Index_Archive extends OES_Archive
    {

        /** @var string The index key. */
        public string $index_key = '';

        /** @var array The index objects. */
        public array $objects = [];

        /** @var bool Hide term if not connected to any post. */
        public bool $hide_on_empty = true;

        /** @var bool Consider only childless terms. */
        public bool $childless = true;


        /** @inheritdoc */
        public function set_parameters(array $args = []): void
        {
            global $oes, $oes_is_index;

            /* set index key and objects */
            $this->index_key = $oes_is_index;
            $objects = $oes->theme_index_pages[$this->index_key]['objects'] ?? [];


            /**
             * Filters the additional objects of an index page.
             *
             * @param array $objects The additional arguments.
             */
            $this->objects = apply_filters('oes/template_redirect_index_additional_objects', $objects);

            /* @oesLegacy add global variable */
            global $oes_additional_objects;
            $oes_additional_objects = $this->objects;

            /* set parameters for optional taxonomy query */
            $this->hide_on_empty = $args['hide_on_empty'] ?? true;
            $this->childless = $args['childless'] ?? true;
        }


        /** @inheritdoc */
        public function prepare_filter(array $args): array
        {
            /* prepare filter (add alphabet filter) */
            $filter = [];
            global $oes, $oes_is_index, $oes_is_index_page;
            if ($oes_is_index &&
                $oes_is_index_page &&
                in_array('alphabet',
                    is_array($oes->theme_index_pages[$oes_is_index]['archive_filter']) ?
                    $oes->theme_index_pages[$oes_is_index]['archive_filter'] :
                    []))
                $filter = ['alphabet' => true];
            return $filter;
        }


        //Implement parent
        public function loop_objects(): void
        {
            /* loop through objects and check for post type or taxonomy */
            foreach ($this->objects as $object) {
                if (post_type_exists($object)) {

                    /* prepare query args */
                    $queryArgs = [
                        'post_type' => $object,
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    ];
                    if (isset($args['post__in'])) {
                        if (empty($args['post__in'])) continue;
                        $queryArgs['post__in'] = $args['post__in'];
                    }
                    if (isset($args['additional_query_args']))
                        $queryArgs = array_merge($queryArgs, $args['additional_query_args']);

                    /* query posts */
                    $posts = new WP_Query($queryArgs);

                    /* loop through results */
                    if ($posts->have_posts())
                        while ($posts->have_posts()) {
                            $posts->the_post();
                            $post = get_post(get_the_ID());
                            $this->loop_results_post($post);
                        }
                } elseif (taxonomy_exists($object)) {

                    /* prepare query args */
                    $queryArgs = [
                        'taxonomy' => $object,
                        'hide_empty' => $this->hide_on_empty,
                        'childless' => $this->childless
                    ];
                    if (isset($args['parent'])) $queryArgs['parent'] = $args['parent'];

                    /* query terms */
                    $terms = get_terms($queryArgs);

                    /* loop through results */
                    if ($terms) foreach ($terms as $term) $this->loop_results_term($term);
                }
            }
        }
    }
}