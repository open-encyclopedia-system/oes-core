<?php

namespace OES\Navigation;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Template_Redirect')) {

    /**
     * Class Template_Redirect
     *
     * This class prepares the template management for the frontend theme.
     */
    class Template_Redirect
    {

        /** @var string The page (content) language */
        public string $oes_language = 'language0';

        /** @var string The navigation language */
        public string $oes_nav_language = 'language0';


        /**
         * Template_Redirect constructor
         */
        function __construct()
        {
            $this->set_language_before();
            $this->prepare_data();
            $this->set_language_after();
        }


        /**
         * Set languages before preparing page data.
         *
         * @return void
         */
        function set_language_before(): void
        {

            global $oes;
            $this->oes_language = sizeof($oes->languages) < 2 ? 'all' : $oes->main_language;
            if (empty($this->oes_nav_language)) $this->oes_nav_language = $oes->main_language ?? 'language0';

            /* set global variables */
            global $oes_language, $oes_nav_language, $oes_language_switched;

            /* language switch was just used */
            if($oes_language_switched){
                $oes_language = $oes_language_switched;
                $oes_nav_language = $oes_language_switched;
            }
            else {
                $oes_language = $_COOKIE['oes_language'] ?? $this->oes_language;
                $oes_nav_language = $_COOKIE['oes_language'] ??
                    ($oes_language === 'all' ? $oes->main_language : $this->oes_nav_language);
            }
        }


        /**
         * Set languages after preparing page data.
         *
         * @return void
         */
        function set_language_after(): void
        {
            /* set global variables */
            global $oes_language, $oes_nav_language;

            /* re-evaluate language if nav language differs from content language */
            if($oes_language != $oes_nav_language) {
                $oes_language = ($_COOKIE['oes_language'] ?? $this->oes_language);
                $oes_nav_language = ((isset($_COOKIE['oes_language']) && $_COOKIE['oes_language'] !== 'all') ?
                    $_COOKIE['oes_language'] :
                    $this->oes_nav_language);
            }
        }


        /**
         * Prepare page data for template call by differentiate between page types
         *
         * @return void
         */
        function prepare_data(): void
        {
            if (is_front_page()) $this->prepare_front_page();
            elseif (is_page()) $this->prepare_page();
            elseif (is_single()) $this->prepare_single();
            elseif (is_tax()) $this->prepare_tax();
            elseif (is_archive()) $this->prepare_archive();
            else $this->prepare_data_other();
        }


        /**
         * Prepare front page data.
         *
         * @return void
         */
        function prepare_front_page(): void
        {
            global $oes_frontpage, $oes_post;
            $oes_frontpage = true;
            oes_set_post_data();
            $this->oes_language = $oes_post->language ?? 'language0';
            $this->oes_nav_language = $this->oes_language;
        }


        /**
         * Prepare front page data for frontpage in different language (from main language).
         *
         * @param string $languageAbb The language abbreviation.
         *
         * @return void
         */
        function prepare_front_page_different_language(string $languageAbb): void
        {
            if (oes_get_current_url(false) === get_site_url() . '/' . strtolower($languageAbb) . '/') {
                global $oes_frontpage;
                $oes_frontpage = true;
                $this->set_language_after();
                if (locate_template('front-page.php', true)) exit();
            }
        }


        /**
         * Prepare page data.
         *
         * @return void
         */
        function prepare_page(): void
        {
            /* set post data */
            global $oes, $oes_post;
            oes_set_page_data();
            $this->oes_language = $oes_post->language ?? 'language0';
            $this->oes_nav_language = $this->oes_language;

            /* check if page is frontpage in different language */
            if (sizeof($oes->languages) > 1)
                foreach ($oes->languages as $languageData)
                    if (isset($languageData['abb']) &&
                        oes_get_current_url(false) === get_site_url() . '/' . strtolower($languageData['abb']) . '/') {
                        global $oes_frontpage;
                        $oes_frontpage = true;
                        $this->set_language_after();
                        if (locate_template('front-page.php', true)) exit();
                    }
        }


        /**
         * Prepare page data for single post.
         *
         * @return void
         */
        function prepare_single(): void
        {
            /* check if archive is "flat" (redirect to archive), else set single post data (global $oes_post) */
            global $oes, $post;
            if (isset($oes->post_types[$post->post_type]['archive_on_single_page']) &&
                $oes->post_types[$post->post_type]['archive_on_single_page']) {
                wp_safe_redirect(
                    get_post_type_archive_link($post->post_type) . '#' . $post->post_type . '-' . $post->ID);
                die();
            } else {
                global $oes_post;
                oes_set_post_data();
                $this->oes_language = $oes_post->language ?: 'language0';
                $this->oes_nav_language = $this->oes_language;
            }
        }


        /**
         * Prepare page data for tax (term) page.
         *
         * @return void
         */
        function prepare_tax(): void
        {
            /* check if redirect */
            global $taxonomy, $term, $oes;
            if ($taxonomy &&
                isset($oes->taxonomies[$taxonomy]['redirect']) &&
                $oes->taxonomies[$taxonomy]['redirect'] &&
                ($oes->taxonomies[$taxonomy]['redirect'] !== 'none') &&
                !isset($_GET['oesf_' . $taxonomy]) &&
                $termObject = get_term_by('slug', $term, $taxonomy)) {
                wp_redirect(get_post_type_archive_link($oes->taxonomies[$taxonomy]['redirect']) . '?oesf_' . $taxonomy . '=' . $termObject->term_id);
            }

            oes_set_term_data();
        }


        /**
         * Prepare page data for archive page.
         *
         * @param array $args Additional arguments for archive call.
         * @return void
         */
        function prepare_archive(array $args = []): void
        {

            /* call hook */
            if (has_action('oes/theme_redirect_archive')) do_action('oes/theme_redirect_archive');

            oes_set_archive_data(array_merge(['language' => $this->oes_language], $args));
        }


        /**
         * Prepare archive page data for archive in different language (from main language).
         *
         * @param string $languageKey The language key.
         * @param array $languageData The language data.
         *
         * @return void
         */
        function prepare_archive_different_language(string $languageKey, array $languageData): void
        {
            global $oes;
            foreach ($oes->post_types as $postType => $ignore)
                if (oes_get_current_url(false) === get_site_url() . '/' . strtolower($languageData['abb']) . '/' .
                    (get_post_type_object($postType)->rewrite['slug'] ?? $postType) . '/') {
                    $this->oes_language = $languageKey;
                    $this->oes_nav_language = ($languageKey === 'all' ? $oes->main_language : $languageKey);

                    if (!empty($oes->theme_index_pages))
                        foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                            if (in_array($postType, $indexPage['objects'] ?? [])) {
                                global $oes_is_index, $oes_index_objects;
                                $oes_is_index = $indexPageKey;
                                $oes_index_objects = $indexPage['objects'];
                            }

                    $this->prepare_archive(['post-type' => $postType]);
                    $this->set_language_after();
                    if (locate_template('archive.php', true)) exit();
                }
        }


        /**
         * Prepare page data for index page.
         *
         * @param string $indexPageKey The index page key.
         * @return void
         */
        function prepare_index(string $indexPageKey): void
        {
            global $oes, $oes_additional_objects, $oes_index_objects, $oes_is_index, $oes_is_index_page;
            if ($oes->theme_index_pages[$indexPageKey]['slug'] !== 'hidden' &&
                $oes_additional_objects = $oes->theme_index_pages[$indexPageKey]['objects'] ?? false) {
                $oes_is_index = $indexPageKey;
                $oes_is_index_page = true;
                $oes_index_objects = $oes->theme_index_pages[$indexPageKey]['objects'];

                /* call hook */
                if (has_action('oes/theme_redirect_index')) do_action('oes/theme_redirect_index');

                $this->prepare_archive();
                $this->set_language_after();
                if (locate_template('archive.php', true)) exit();
            }
        }


        /**
         * Prepare index page data for index in different language (from main language).
         *
         * @param string $languageKey The language key.
         * @param array $languageData The language data.
         *
         * @return void
         */
        function prepare_index_page_different_language(string $languageKey, array $languageData): void
        {
            global $oes;
            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                    if ($indexPage['slug'] !== 'hidden' &&
                        oes_get_current_url(false) === get_site_url() . '/' . strtolower($languageData['abb']) . '/' .
                        ($indexPage['slug'] ?? 'index') . '/') {
                        $this->oes_language = $languageKey;
                        $this->oes_nav_language = $languageKey;
                        $this->prepare_index($indexPageKey);
                    }
        }


        /**
         * Prepare page data for taxonomy archive.
         *
         * @param string $languageKey The language key.
         * @param array $languageData The language data.
         *
         * @return void
         */
        function prepare_taxonomies(string $languageKey = 'language0', array $languageData = []): void
        {
            global $oes;
            foreach ($oes->taxonomies as $taxonomyKey => $singleTaxonomy) {

                /* Archive pages */
                if (isset($singleTaxonomy['rewrite']['slug']) &&
                    oes_get_current_url(false) == (get_site_url() . '/' .
                        (isset($languageData['abb']) ? (strtolower($languageData['abb']) . '/') : '') .
                        $singleTaxonomy['rewrite']['slug'] . '/') &&
                    !is_page($singleTaxonomy['rewrite']['slug'])) {

                    global $oes_additional_objects, $oes_taxonomy;
                    if ($oes_additional_objects = [$taxonomyKey]) {

                        /* add action for taxonomy single pages */
                        if (has_action('oes/theme_redirect_taxonomy'))
                            do_action('oes/theme_redirect_taxonomy',
                                $taxonomyKey,
                                $singleTaxonomy,
                                oes_get_current_url(false));

                        if (!empty($oes->theme_index_pages))
                            foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                                if (in_array($taxonomyKey, $indexPage['objects'] ?? [])) {
                                    global $oes_is_index, $oes_index_objects;
                                    $oes_is_index = $indexPageKey;
                                    $oes_index_objects = $indexPage['objects'];
                                }

                        $oes_taxonomy = $taxonomyKey;
                        oes_set_archive_data(['execute-loop' => true, 'language' => $languageKey]);

                        if(!empty($languageData)){
                            global $oes_archive_data, $oes_language, $oes_nav_language;
                            $oes_language = $oes_archive_data['archive']['language'] ?? 'language0';
                            $oes_nav_language = $oes_language;
                        }
                        if (locate_template('archive.php', true)) exit();
                    }
                }
            }
        }


        /**
         * Prepare other page data.
         *
         * @return void
         */
        function prepare_data_other(): void
        {

            /* check if index page */
            global $oes;
            if (!empty($oes->theme_index_pages))
                foreach ($oes->theme_index_pages as $indexPageKey => $indexPage)
                    if (oes_get_current_url(false) === get_site_url() . '/' . ($indexPage['slug'] ?? 'index') . '/')
                        $this->prepare_index($indexPageKey);

            /* check if page is oes page in different language */
            if (sizeof($oes->languages) > 1)
                foreach ($oes->languages as $languageKey => $languageData)
                    if (isset($languageData['abb'])) {

                        /* check if page is frontpage in different language */
                        $this->prepare_front_page_different_language($languageData['abb']);

                        /* check if page is index page in different language */
                        $this->prepare_index_page_different_language($languageKey, $languageData);

                        /* check if page is archive in different language */
                        $this->prepare_archive_different_language($languageKey, $languageData);

                        /* check if page is taxonomy archive in different language */
                        $this->prepare_taxonomies($languageKey, $languageData);
                    }

            /* check if page is taxonomy archive */
            $this->prepare_taxonomies();

            /* check for more, customized page options */
            $this->prepare_data_custom();
        }


        /**
         * Custom page data (overwritten by project processing)
         *
         * @return void
         */
        function prepare_data_custom(): void
        {
        }
    }
}
