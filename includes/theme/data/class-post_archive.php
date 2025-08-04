<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Post_Archive') && class_exists('OES_Archive')) {


    /**
     * Class OES_Post_Archive
     *
     * This class prepares an archive of post types for display in the frontend theme.
     */
    class OES_Post_Archive extends OES_Archive
    {

        /** @var string $postType A string containing the post type. */
        public string $post_type = '';

        /** @var string $taxonomy A string containing the taxonomy filtering the posts. */
        public string $taxonomy = '';

        /** @var string $term A string containing the term slug filtering the posts. */
        public string $term = '';


        /** @inheritdoc */
        public function set_parameters(array $args = []): void
        {
            /* set post type */
            $postType = $args['post-type'] ?? '';
            if (empty($postType)) {
                global $post_type;
                if (!is_null($post_type)) $postType = $post_type;
            }
            if (!empty($postType)) {

                /* check if archive is to be displayed as list (no single view, eg. glossary) */
                global $oes;
                $this->post_type = $postType;
                $this->display_content = (isset($oes->post_types[$postType]['archive_on_single_page']) &&
                    $oes->post_types[$postType]['archive_on_single_page']);

                /* check if index */
                global $oes, $oes_is_index;
                if (!$oes_is_index && !empty($oes->theme_index_pages)) {
                    foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                        if (in_array($postType, $indexPage['objects'] ?? [])) $oes_is_index = $indexPageKey;
                }
            }

            /* check for filtering */
            $this->taxonomy = $args['taxonomy'] ?? '';
            $this->term = $args['term'] ?? '';
        }


        /** @inheritdoc */
        public function get_object_label(): string
        {
            global $oes, $oes_language;
            if (!empty($this->post_type))
                return $oes->post_types[$this->post_type]['label_translations_plural'][$oes_language] ??
                    ($oes->post_types[$this->post_type]['label'] ?? 'Label missing');
            return '';
        }


        /** @inheritdoc */
        public function prepare_filter(array $args): array
        {
            $postType = $args['post-type-for-filter'] ?? $this->post_type;

            global $oes, $oes_language;
            $filterArray = [];
            if (!empty($postType))
                foreach ($oes->post_types[$postType]['archive_filter'] ?? [] as $filter) {

                    if ($filter === 'alphabet') $filterArray['alphabet'] = true;
                    else {

                        /* check for further information */
                        $filterArgs = ['parent' => false];
                        $filterKey = $filter;
                        if (str_starts_with($filter, 'parent__')) {
                            $filterKey = substr($filter, 8);
                            $filterArgs['parent'] = true;
                        }

                        if (str_starts_with($filterKey, 'taxonomy__')) {
                            $filterKey = substr($filterKey, 10);
                            if (taxonomy_exists($filterKey)) {
                                $filterArgs['type'] = 'taxonomy';
                                $this->filter_array['list'][$filterKey]['label'] =
                                    $oes->taxonomies[$filterKey]['label_translations_plural'][$oes_language] ??
                                    get_taxonomy($filterKey)->label;
                            }
                        } elseif (str_starts_with($filterKey, 'post_type__')) {
                            $filterKey = substr($filterKey, 11);
                            if (post_type_exists($filterKey)) {
                                $filterArgs['type'] = 'post_type';
                                $this->filter_array['list'][$filterKey]['label'] =
                                    ($oes->post_types[$filterKey]['label_translations_plural'][$oes_language] ??
                                        $filterKey);
                            }
                        } elseif (str_starts_with($filterKey, 'parent_taxonomy__')) {
                            $filterKey = substr($filterKey, 17);
                            if (taxonomy_exists($filterKey)) {
                                $filterArgs['type'] = 'taxonomy';
                                $filterArgs['parent'] = true;
                                $this->filter_array['list'][$filterKey]['label'] =
                                    $oes->taxonomies[$filterKey]['label_translations_plural'][$oes_language] ??
                                    get_taxonomy($filterKey)->label;
                            }
                        } elseif ($field = oes_get_field_object($filterKey)) {
                            $filterArgs['type'] = 'field';
                            $filterArgs['field-type'] = $field['type'] ?? 'Type missing';

                            /* get global configuration for this language */
                            $fieldConfiguration = [];
                            if (!empty($this->post_type)) {
                                if ($filterArgs['parent'] &&
                                    isset($oes->post_types[$this->post_type]['parent']) &&
                                    $oes->post_types[$this->post_type]['parent'])
                                    $fieldConfiguration =
                                        $oes->post_types[$oes->post_types[$this->post_type]['parent']]['field_options'][$filterKey] ??
                                        false;
                                else
                                    $fieldConfiguration =
                                        $oes->post_types[$this->post_type]['field_options'][$filterKey] ?? false;
                            } elseif (!empty($this->taxonomy))
                                $fieldConfiguration =
                                    $oes->taxonomy[$this->taxonomy]['field_options'][$filterKey] ?? false;

                            $this->filter_array['list'][$filterKey]['label'] =
                                $fieldConfiguration['label_translation_' . $oes_language] ??
                                ($field['label'] ?? $filterKey);
                        }

                        $filterArray[$filterKey] = $filterArgs;
                    }
                }
            return $filterArray;
        }


        //Implement parent
        public function loop_objects(): void
        {

            if (!empty($this->taxonomy) && $this->post_type)
                $this->loop_objects_filtered();
            elseif (is_archive() && !is_tax()) {
                if (have_posts())
                    while (have_posts()) {
                        the_post();
                        $post = get_post(get_the_ID());
                        $this->loop_results_post($post);
                    }
            }
            elseif (post_type_exists($this->post_type)) {

                /* query posts */
                $posts = new WP_Query(array_merge([
                    'post_type' => $this->post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => -1
                ], $this->query_parameters));

                /* loop through results */
                if ($posts->have_posts())
                    while ($posts->have_posts()) {
                        $posts->the_post();
                        $post = get_post(get_the_ID());
                        $this->loop_results_post($post);
                    }
            }
        }


        /**
         * Loop through posts filtered by taxonomy or term.
         *
         * @return void
         */
        public function loop_objects_filtered(): void
        {

            /* prepare query (check if term is given) */
            if (empty($this->term)) $queryArgs = [
                'post_type' => $this->post_type,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ];
            else $queryArgs = [
                'post_type' => $this->post_type,
                'tax_query' => [[
                    'taxonomy' => $this->taxonomy,
                    'field' => 'slug',
                    'terms' => $this->term
                ]]
            ];
            $posts = new WP_Query($queryArgs);

            /* loop through results */
            if ($posts->have_posts())
                while ($posts->have_posts()) {
                    $posts->the_post();
                    $post = get_post(get_the_ID());
                    $this->loop_results_post($post);
                }
        }
    }
}