<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Term')) {

    /**
     * Class OES_Term
     *
     * This class prepares a taxonomy term for display in the frontend theme.
     */
    class OES_Term extends OES_Object
    {

        /** @var string $taxonomy The taxonomy. */
        public string $taxonomy = '';

        /** @var string $taxonomy_label The taxonomy label. */
        public string $taxonomy_label = '';


        /** @inheritdoc */
        public function set_parameters(): void
        {
            $this->language = $this->get_language();
            $this->set_title();

            if ($term = get_term($this->object_ID)) {

                /* get global OES instance parameter */
                global $oes, $oes_language;

                /* set taxonomy */
                $this->taxonomy = $term->taxonomy;

                /* set taxonomy label */
                if ($this->taxonomy)
                    $this->taxonomy_label = $oes->taxonomies[$this->taxonomy]['label_translations'][$oes_language] ??
                        ($oes->taxonomies[$this->taxonomy]['label'] ??
                            (get_taxonomy($this->taxonomy)->labels->singular_name ?? 'Label missing'));

                /* check if term is part of the index */
                if (!empty($oes->theme_index_pages))
                    foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                        if (in_array($this->taxonomy, $indexPage['objects'] ?? []))
                            $this->part_of_index_pages[] = $indexPageKey;

                /* set theme labels */
                $this->theme_labels = $oes->theme_labels;
                if (isset($oes->taxonomies[$this->taxonomy]['theme_labels']))
                    $this->theme_labels = array_merge($this->theme_labels,
                        $oes->taxonomies[$this->taxonomy]['theme_labels']);
            }
        }


        /** @inheritdoc */
        public function set_title(): void
        {
            $this->title = oes_get_display_title(get_term($this->object_ID));
        }


        /** @inheritdoc */
        public function prepare_html_main_classic(array $args = []): array
        {
            $contentArray = [];

            /* prepare title */
            if (!$this->has_theme_subtitle)
                $contentArray['010_title'] = '<div class="oes-sub-subheader-container">' .
                    '<div class="oes-sub-subheader">' .
                    '<h1 class="oes-single-title">' . $this->title . '</h1>' .
                    '</div>' .
                    '</div>';

            /* add index information single__toc__index */
            global $oes_language;
            if(!empty($this->part_of_index_pages))
                $contentArray['400_index'] = '<div class="oes-index-connections">' .
                    oes_get_index_html([
                        'language' => $oes_language,
                        'display-header' => $this->get_theme_label('single__toc__index')]) .
                    '</div>';

            /* prepare description */
            global $oes_language;
            if ($oes_language === 'language0') {
                if ($description = get_term($this->object_ID)->description)
                    if (!empty($description))
                        $contentArray['100_description'] = '<div class="oes-term-single-description">' .
                            $description .
                            '</div>';
            } else {
                if ($metadata = get_term_meta($this->object_ID))
                    if (isset($metadata['description_' . $oes_language][0]) &&
                        !empty($metadata['description_' . $oes_language][0]))
                        $contentArray['100_description'] = '<div class="oes-term-single-description">' .
                            $metadata['description_' . $oes_language][0] .
                            '</div>';
            }

            return $contentArray;
        }


        /** @inheritdoc */
        public function get_index_connected_posts($consideredPostType, $postRelationship): array
        {
            /* prepare data */
            $connectedPosts = [];

            /* get considered post type */
            if ($consideredPostType) {
                $collectPosts = get_posts([
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

                /* modify for post relationships */
                if ($postRelationship === 'child_version')
                    foreach ($collectPosts as $post) {
                        $versionID = \OES\Versioning\get_current_version_id($post->ID);
                        if ($versionID) $connectedPosts[] = get_post($versionID);
                    }
                elseif ($postRelationship === 'parent')
                    foreach ($collectPosts as $post) {
                        $versionID = \OES\Versioning\get_parent_id($post->ID);
                        if ($versionID) $connectedPosts[] = get_post($versionID);
                    }
                else $connectedPosts = $collectPosts;
            }

            return [$connectedPosts];
        }


        /** @inheritdoc */
        public function get_breadcrumbs_html(array $args = []): string
        {

            global $oes;
            $link = '';
            $name = '';
            if ($taxonomyObject = get_taxonomy($this->taxonomy)) {
                $name = ($oes->taxonomies[$this->taxonomy]['label_translations_plural'][$this->language] ??
                    ($oes->taxonomies[$this->taxonomy]['label_translations'][$this->language] ??
                        ($oes->taxonomies[$this->taxonomy]['label'] ?? $taxonomyObject->label)));
                $link = (get_site_url() . '/' .
                    (get_taxonomy($this->taxonomy)->rewrite['slug'] ?? $this->taxonomy) . '/');
            }

            $header = ((isset($args['header']) && $args['header']) ?
                ('<div class="oes-breadcrumbs-header">' .
                    ($oes->taxonomies[$this->taxonomy]['label_translations_plural'][$this->language] ??
                        ($this->get_theme_label('archive__header', $this->taxonomy_label))) .
                    '</div>') :
                '');
            return '<div class="oes-sidebar-wrapper">' .
                $header .
                '<div class="oes-breadcrumbs-container">' .
                '<ul class="oes-breadcrumbs-list">' .
                '<li>' .
                '<a href="' . $link . '">' .
                '<span class="oes-breadcrumbs-back-to-archive" >' .
                $this->get_theme_label('archive__link_back', 'See all') .
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