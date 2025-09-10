<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Template_Redirect')):

    /**
     * Handles data preparation and customization of the template redirect phase in WordPress.
     */
    class OES_Template_Redirect
    {
        /**
         * @var string The current url.
         */
        protected string $current_url = '';

        /**
         * Template redirect constructor.
         */
        public function __construct()
        {
            add_filter('body_class', [$this, 'body_class']);
            $this->set_language();
        }

        /**
         * Prepare data based on the current request context.
         *
         * @return void
         */
        public function prepare_data(): void
        {
            if (is_front_page() || is_page()) {
                $this->set_page_data();
            } elseif (is_attachment()) {
                $this->prepare_attachment();
            } elseif (is_single()) {
                $this->prepare_single();
            } elseif (is_tax()) {
                $this->prepare_tax();
            } elseif (is_archive()) {
                $this->set_archive_data();
            } elseif (is_search()) {
                $this->prepare_search();
            } else {
                $this->prepare_data_other();
            }
        }

        /**
         * Set the current language context.
         *
         * @return void
         */
        protected function set_language(): void
        {
            global $oes, $oes_language, $oes_language_switched;

            if (count($oes->languages) < 2) {
                $oes_language = 'language0';
            } else {
                if ($oes_language_switched) {
                    $oes_language = $oes_language_switched;
                }
                if (empty($oes_language)) {
                    $oes_language = $_COOKIE['oes_language'] ?? 'language0';
                }
            }
        }

        /**
         * Set data for a regular page.
         *
         * @return void
         */
        protected function set_page_data(): void
        {
            oes_set_page_data();
        }

        /**
         * Set data for an attachment page.
         *
         * @return void
         */
        protected function prepare_attachment(): void
        {
            oes_set_attachment_data();
        }

        /**
         * Set data for a single post.
         *
         * @return void
         */
        protected function prepare_single(): void
        {
            global $oes, $post;

            if ($oes->post_types[$post->post_type]['archive_on_single_page'] ?? false) {
                $url = get_post_type_archive_link($post->post_type) . '#' . $post->post_type . '-' . $post->ID;
                oes_redirect($url);
            } else {
                oes_set_post_data();
            }
        }

        /**
         * Set data for taxonomy archives.
         *
         * @return void
         */
        protected function prepare_tax(): void
        {
            global $taxonomy, $term, $oes;

            if (
                $taxonomy &&
                ($oes->taxonomies[$taxonomy]['redirect'] ?? false) &&
                ($oes->taxonomies[$taxonomy]['redirect'] !== 'none') &&
                !isset($_GET['oesf_' . $taxonomy]) &&
                $termObject = get_term_by('slug', $term, $taxonomy)
            ) {
                $url = get_post_type_archive_link($oes->taxonomies[$taxonomy]['redirect']) .
                    '?oesf_' . $taxonomy . '=' . $termObject->term_id;
                oes_redirect($url);
            } else {
                oes_set_term_data();
            }
        }

        /**
         * Set data for archive pages.
         *
         * @return void
         */
        protected function set_archive_data(): void
        {
            $this->call_set_archive_data();
        }

        /**
         * Prepare data for search results.
         *
         * @return void
         */
        protected function prepare_search(): void
        {
            global $oes_search, $oes_archive_count, $oes_is_search;

            $args = ['language' => 'all'];
            $class = oes_get_project_class_name('OES_Search');
            $oes_search = new $class($args);
            $oes_archive_count = $oes_search->count;

            if (!empty($oes_search->search_term)) {
                global $oes, $oes_archive_data, $oes_filter, $oes_archive;

                if ($oes->block_theme) {
                    $oes_search->get_results();
                    $oes_archive_count = $oes_search->count;
                    $oes_filter = $oes_search->filter_array;
                    $oes_archive_data = $oes_search->get_data_as_table();
                    $oes_archive = (array)$oes_search; // @oesDevelopment: simplify this if possible.
                }
            }

            $oes_is_search = true;
        }

        /**
         * Prepare fallback data for other pages (e.g., index pages or taxonomy URLs).
         *
         * @return void
         */
        protected function prepare_data_other(): void
        {
            global $oes;

            foreach ($oes->theme_index_pages as $indexPageKey => $indexPage) {
                if ($this->get_current_url() === get_site_url() . '/' . ($indexPage['slug'] ?? 'index') . '/') {
                    $this->prepare_index($indexPageKey);
                }
            }

            $this->prepare_taxonomies();
            $this->prepare_custom();
        }

        /**
         * Prepare data for a custom index page.
         *
         * @param string $indexPageKey
         * @return void
         */
        protected function prepare_index(string $indexPageKey): void
        {
            global $oes, $oes_is_index, $oes_is_index_page;

            if ($oes->theme_index_pages[$indexPageKey]['slug'] !== 'hidden') {
                $oes_is_index = $indexPageKey;
                $oes_is_index_page = true;

                $archiveClass = $indexPageKey . '_Index_Archive';
                if (!class_exists($archiveClass)) {
                    $archiveClass = 'OES_Index_Archive';
                }

                $this->call_set_archive_data($archiveClass);

                do_action('oes/redirect_template', 'prepare_index');
            }
        }

        /**
         * Handle taxonomy URLs that behave like archives or index pages.
         *
         * @return void
         */
        protected function prepare_taxonomies(): void
        {
            global $oes, $oes_taxonomy;

            foreach ($oes->taxonomies as $taxonomyKey => $singleTaxonomy) {
                $taxonomyObject = get_taxonomy($taxonomyKey);

                $expectedSlug = $taxonomyObject->rewrite['slug'] ?? $taxonomyKey;
                $expectedUrl = get_site_url() . '/' . $expectedSlug . '/';

                if (
                    $this->get_current_url() === $expectedUrl &&
                    !is_page($expectedSlug)
                ) {
                    foreach ($oes->theme_index_pages as $indexPageKey => $indexPage) {
                        if (in_array($taxonomyKey, $indexPage['objects'] ?? [])) {
                            global $oes_is_index;
                            $oes_is_index = $indexPageKey;
                        }
                    }

                    $oes_taxonomy = $taxonomyKey;
                    $archiveClass = $taxonomyKey . '_Taxonomy_Archive';

                    if (!class_exists($archiveClass)) {
                        $archiveClass = $taxonomyKey . '_Archive';
                    }
                    if (!class_exists($archiveClass)) {
                        $archiveClass = 'OES_Taxonomy_Archive';
                    }

                    $this->call_set_archive_data($archiveClass, ['taxonomy' => $taxonomyKey]);

                    do_action('oes/redirect_template', 'prepare_taxonomies');
                }
            }
        }

        /**
         * Prepare custom behaviour.
         * @return void
         */
        protected function prepare_custom(): void
        {
        }

        /**
         * Retrieve current url.
         * @return string
         */
        protected function get_current_url(): string
        {
            if(empty($this->current_url)){
                $this->current_url = oes_get_current_url(false);
            }
            return $this->current_url;
        }

        /**
         * Set archive parameters and data for post types and taxonomies.
         *
         * @param string $class The archive class.
         * @param array $args Additional parameters.
         *
         * @return void
         */
        protected function call_set_archive_data(string $class = '', array $args = []): void
        {
            oes_set_archive_data($class, $args);
        }

        /**
         * Adds custom classes to the <body> element.
         *
         * @param array $classes
         * @return array
         */
        public function body_class(array $classes): array
        {
            global $oes_is_index, $oes_taxonomy, $oes_language;

            $removeError = false;

            if (!empty($oes_is_index)) {
                $classes[] = 'oes-index-archive';
                $classes[] = 'oes-index-archive-' . $oes_is_index;
                $removeError = true;
            }

            if (!empty($oes_taxonomy)) {
                $classes[] = 'oes-taxonomy-archive';
                $classes[] = 'oes-taxonomy-archive-' . $oes_taxonomy;
                $removeError = true;
            }

            if (!empty($oes_language)) {
                $classes[] = 'oes-body-' . $oes_language;
            }

            if ($removeError) {
                $key = array_search('error404', $classes);
                if ($key !== false) {
                    unset($classes[$key]);
                }
            }

            return $classes;
        }
    }
endif;