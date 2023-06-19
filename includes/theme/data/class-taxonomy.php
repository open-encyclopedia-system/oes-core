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


        //Overwrite parent
        function add_class_variables(array $additionalParameters): void
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
                if (!empty($oes->theme_index_pages))
                    foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                        if (in_array($this->taxonomy, $indexPage['objects'] ?? []))
                            $this->part_of_index_pages[] = $indexPageKey;

                /* add general themes */
                if (isset($oes->theme_labels))
                    $this->theme_labels = array_merge($oes->theme_labels, $this->theme_labels);

                /* check for taxonomy specific labels */
                if (isset($oes->taxonomies[$this->taxonomy]['theme_labels']))
                    $this->theme_labels = $oes->taxonomies[$this->taxonomy]['theme_labels'];
            }
        }


        //Overwrite parent
        function set_title(): void
        {
            $this->title = oes_get_display_title(get_term($this->object_ID), ['language' => $this->language]) ?? $this->object_ID;
        }


        //Overwrite parent
        function prepare_html_main(array $args = []): array
        {
            /* prepare content array */
            $prepareContentArray = [];

            /* check for language */
            if (isset($args['language'])) $this->language = $args['language'];

            /* prepare title */
            /* prepare title */
            if (!$this->has_theme_subtitle)
                $contentArray['010_title'] = sprintf('<div class="oes-sub-subheader-container"><div class="oes-sub-subheader"><h1 class="oes-single-title">%s</h1></div></div>',
                    $this->title
                );

            /* add index information single__toc__index */
            $prepareContentArray['index'] = !empty($this->part_of_index_pages) ? $this->get_index_connections() : '';
            $contentArray['400_index'] = $prepareContentArray['index'] ?? '';

            /* prepare description */
            if ($this->language === 'language0') {
                if ($description = get_term($this->object_ID)->description)
                    if (!empty($description))
                        $contentArray['100_description'] = '<div class="oes-term-single-description">' . $description . '</div>';
            } else {
                if ($metaData = get_term_meta($this->object_ID))
                    if (isset($metaData['description_' . $this->language][0]) && !empty($metaData['description_' . $this->language][0]))
                        $contentArray['100_description'] = '<div class="oes-term-single-description">' .
                            $metaData['description_' . $this->language][0] .
                            '</div>';
            }

            /* check for modification */
            $contentArray = $this->modify_content($contentArray);

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


        /**
         * Get html representation of breadcrumbs.
         *
         * @param array $args Additional arguments.
         *
         * @return string
         */
        function get_breadcrumbs_html(array $args = []): string
        {

            global $oes;
            $link = '';
            $name = '';
            if ($taxonomyObject = get_taxonomy($this->taxonomy)) {
                $name = ($oes->taxonomies[$this->taxonomy]['label_translations_plural'][$this->language] ??
                    ($oes->taxonomies[$this->taxonomy]['label_translations'][$this->language] ??
                    ($oes->taxonomies[$this->taxonomy]['label'] ?? $taxonomyObject->label)));
                $link = (get_site_url() . '/' . $oes->taxonomies[$this->taxonomy]['rewrite']['slug'] . '/');
            }

            $header = ((isset($args['header']) && $args['header']) ?
                ('<div class="oes-breadcrumbs-header">' .
                    ($oes->taxonomies[$this->taxonomy]['label_translations_plural'][$this->language] ??
                        ($this->theme_labels['archive__header'][$this->language] ?? $this->taxonomy_label)) . '</div>') :
                '');
            return '<div class="oes-sidebar-wrapper">' .
                $header .
                '<div class="oes-breadcrumbs-container">' .
                '<ul class="oes-breadcrumbs-list">' .
                '<li>' .
                '<a href="' . $link . '">' .
                '<span class="oes-breadcrumbs-back-to-archive" >' .
                ($this->theme_labels['archive__link_back'][$this->language] ?? 'See all') .
                '</span>' .
                $name .
                '</a>' .
                '</li>' .
                '</ul>' .
                '</div>' .
                '</div>';
        }
    }
}