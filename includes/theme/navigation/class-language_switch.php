<?php

namespace OES\Multilingualism;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field;

if (!class_exists('Language_Switch')) {

    /**
     * Class Language_Switch
     *
     * This class prepares the template management for the frontend theme.
     */
    class Language_Switch
    {

        /** @var string The page language */
        public string $oes_language = 'language0';

        /** @var string Current URL */
        public string $current_url = '';

        /** @var array The target language(s) */
        public array $target_languages = [];

        /** @var array The additional parameters */
        public array $args = [];

        /** @var array The links to the connected pages */
        public array $links = [];


        /**
         * Language_Switch constructor.
         *
         * @param array $args Additional parameters
         */
        function __construct(array $args = [])
        {
            $this->set_parameters($args);
            $this->prepare_link();
        }


        /**
         * Set the parameters for the language switch link.
         *
         * @param array $args Additional parameters
         * @return void
         */
        function set_parameters(array $args): void
        {
            /* prepare languages */
            global $oes_language, $oes;
            $this->oes_language = $oes_language;
            foreach ($oes->languages as $languageKey => $languageData)
                if ($languageKey !== $this->oes_language)
                    $this->target_languages[$languageKey] = $languageData['abb'] ?? $languageData['label'];

            $this->current_url = $args['current_url'] ?? oes_get_current_url(false);

            /* set additional parameter */
            $this->args = $args;
        }


        /**
         * Prepare the language switch link for the current page.
         *
         * @return void
         */
        function prepare_link(): void
        {
            if (is_front_page()) $this->links_for_front_page();
            elseif (is_page()) $this->links_for_page();
            elseif (is_single()) $this->links_for_single();
            elseif (is_tax()) $this->links_for_tax();
            elseif (is_archive()) $this->links_for_archive();
            elseif (is_search()) $this->links_for_search();
            else $this->links_for_data_other();
        }


        /**
         * Get the html menu item representation of language switch link.
         *
         * @param string $targetLanguage The target language.
         * @param array $args Additional arguments.
         * @return string Returns the html menu item representation of the language switch link.
         */
        function menu_item_html(string $targetLanguage = '', array $args = []): string
        {
            global $oes;
            $item = $this->item_link_html($targetLanguage, $args);
            $itemID = $args['id'] ?? '';
            $itemClasses = $args['classes'] ?? (sizeof($oes->languages) > 2 ?
                'nav-item menu-item menu-item-has-children' :
                'menu-item');

            return sprintf('<li id="%s" class="%s">%s</li>',
                $itemID,
                $itemClasses,
                $item
            );
        }


        /**
         * Get the html link representation of the language switch.
         *
         * @param string $targetLanguage The target language.
         * @param array $args Additional arguments.
         * @return string Returns the html link representation of the language switch.
         */
        function item_link_html(string $targetLanguage = '', array $args = []): string
        {

            $linkObjects = $this->item_link_object($targetLanguage, $args);

            /* return link of menu item */
            return sprintf('<a href="%s" class="%s">%s</a>',
                $linkObjects['link'] ?? '',
                $linkObjects['class'] ?? '',
                $linkObjects['text'] ?? ''
            );
        }


        /**
         * Get the html link representation of the language switch.
         *
         * @param string $targetLanguage The target language.
         * @param array $args Additional arguments.
         * @return array Returns the html link object.
         */
        function item_link_object(string $targetLanguage = '', array $args = []): array
        {
            global $oes;
            $linkClass = $args['link-class'] ?? 'oes-language-switch';

            if (sizeof($oes->languages) > 2) {

                $linkList = [];
                foreach ($this->target_languages as $languageKey => $languageAbb)
                    $linkList[$languageKey] = [
                        'link' => home_url('/') . strtolower($languageAbb) . '/?oes-language-switch=true',
                        'class' => $linkClass,
                        'text' => $languageAbb
                    ];
                return $linkList;
            } else {

                /* take first target language if not specified */
                if (empty($targetLanguage)) $targetLanguage = array_key_first($this->target_languages);

                /* prepare link */
                $link = $this->links[$targetLanguage]['link'] ?? '';
                if (empty($link))
                    $link = (home_url('/') . strtolower($this->target_languages[$targetLanguage] ?? '') . '/');

                /* add switch info */
                if (!empty($link))
                    $link .= (parse_url($link, PHP_URL_QUERY) ? '&' : '?') .'oes-language-switch=' . $targetLanguage;


                /**
                 * Filters the language switch link.
                 *
                 * @param string $link The link.
                 * @param Language_Switch $this The language switch object.
                 * @param string $targetLanguage The target language.
                 */
                if (has_filter('oes/language_switch_link'))
                    $link = apply_filters('oes/language_switch_link', $link, $this, $targetLanguage);


                /* prepare link text */
                $linkText = $this->links[$targetLanguage]['link-text'] ?? '';
                if (empty($linkText))
                    $linkText = $this->target_languages[$targetLanguage] ?? $targetLanguage;


                /**
                 * Filters the language switch link text.
                 *
                 * @param string $link The link text.
                 * @param Language_Switch $this The language switch object.
                 * @param string $targetLanguage The target language.
                 */
                if (has_filter('oes/language_switch_link_text'))
                    $linkText = apply_filters('oes/language_switch_link_text', $linkText, $this, $targetLanguage);


                /* return link as menu item */
                return [
                    'link' => $link,
                    'class' => $linkClass,
                    'text' => $linkText
                ];
            }
        }


        /**
         * Set links for front page.
         *
         * @return void
         */
        function links_for_front_page(): void
        {
            //@oesDevelopment Make sure, that page exists
            foreach ($this->target_languages as $languageKey => $languageAbb)
                $this->links[$languageKey]['link'] = home_url('/') . strtolower($languageAbb) . '/';
        }


        /**
         * Set links for page.
         *
         * @return void
         */
        function links_for_page(): void
        {
            /* check for translations */
            global $post;
            if ($translations = oes_get_field('field_oes_page_translations', $post->ID))
                foreach ($translations as $translationID) {
                    $pageLanguage = oes_get_post_language($translationID);
                    if ($pageLanguage && $pageLanguage !== $this->oes_language) {
                        $this->links[$pageLanguage]['link'] = get_permalink($translationID);
                        break;
                    }
                }
        }


        /**
         * Set links for single.
         *
         * @return void
         */
        function links_for_single(): void
        {
            global $oes_post;
            if (!empty($oes_post->translations)) {
                foreach ($oes_post->translations as $translation)
                    if (isset($translation['id']) &&
                        isset($translation['language'])) {
                        $this->links[$translation['language']]['link'] = get_permalink($translation['id']);
                        break;
                    }
            } elseif (!empty($this->target_languages)) {
                foreach ($this->target_languages as $languageKey => $data)
                    $this->links[$languageKey]['link'] = get_permalink($oes_post->object_ID) .
                        '';
            }

            /* add item itself */
            $this->links[$oes_post->language]['link'] = get_permalink($oes_post->object_ID);
        }


        /**
         * Set links for taxonomy.
         *
         * @return void
         */
        function links_for_tax(): void
        {
            global $oes;
            foreach ($this->target_languages as $languageKey => $languageAbb)
                $this->links[$languageKey]['link'] = home_url('/') .
                    ($languageKey !== $oes->main_language ? strtolower($languageAbb) . '/' : '');
        }


        /**
         * Set links for archive.
         *
         * @return void
         */
        function links_for_archive(): void
        {
            global $post_type, $oes, $oes_post_type;
            if ($consideredPostType = empty($post_type) ? $oes_post_type : $post_type)
                foreach ($this->target_languages as $languageKey => $languageAbb)
                    $this->links[$languageKey]['link'] = home_url('/') .
                        ($languageKey !== $oes->main_language ? strtolower($languageAbb) . '/' : '') .
                        (get_post_type_object($consideredPostType)->rewrite['slug'] ?? $post_type) . '/';
        }


        /**
         * Set links for search.
         *
         * @return void
         */
        function links_for_search(): void
        {
            global $oes;
            foreach ($this->target_languages as $languageKey => $languageAbb)
                $this->links[$languageKey]['link'] = get_site_url() . '/' .
                    (isset($_GET['s']) ? '?s=' . $_GET['s'] : '');
        }


        /**
         * Set links for index.
         *
         * @return void
         */
        function links_for_index(): void
        {
            global $oes;
            foreach ($this->target_languages as $languageKey => $languageAbb)
                $this->links[$languageKey]['link'] = get_site_url() . '/' .
                    ($languageKey !== $oes->main_language ?
                        strtolower($languageAbb) . '/' :
                        '') .
                    ($oes->theme_index['slug'] ?? 'index') . '/';
        }


        /**
         * Set links for taxonomies.
         *
         * @return void
         */
        function links_for_taxonomies(): void
        {
            global $oes;
            foreach ($oes->taxonomies as $taxonomyKey => $singleTaxonomy)
                if (isset($singleTaxonomy['rewrite']['slug'])) {

                    /* taxonomy archive in main language */
                    if (!is_page($singleTaxonomy['rewrite']['slug']) &&
                        $this->current_url === get_site_url() . '/' . $singleTaxonomy['rewrite']['slug'] . '/') {
                        foreach ($this->target_languages as $languageKey => $languageAbb)
                            $this->links[$languageKey]['link'] = home_url('/') .
                                ($languageKey !== $oes->main_language ? strtolower($languageAbb) . '/' : '') .
                                ($singleTaxonomy['rewrite']['slug'] ?? $taxonomyKey) . '/';
                    } /* taxonomy archive in different language */
                    else
                        foreach ($oes->languages as $languageData)
                            if (!is_page(strtolower($languageData['abb']) . '/' . $singleTaxonomy['rewrite']['slug']) &&
                                $this->current_url === get_site_url() . '/' . strtolower($languageData['abb']) . '/' .
                                ($singleTaxonomy['rewrite']['slug'] ?? $taxonomyKey) . '/') {
                                foreach ($this->target_languages as $languageKey => $languageAbb)
                                    $this->links[$languageKey]['link'] = home_url('/') .
                                        ($languageKey !== $oes->main_language ? strtolower($languageAbb) . '/' : '') .
                                        ($singleTaxonomy['rewrite']['slug'] ?? $taxonomyKey) . '/';
                            }

                }
        }


        /**
         * Set links for other pages.
         *
         * @return void
         */
        function links_for_data_other(): void
        {
            /* check if index page */
            if ($this->current_url === get_site_url() . '/' . ($oes->theme_index['slug'] ?? 'index') . '/')
                $this->links_for_index();

            /* check if oes page in different language */
            global $oes;
            foreach ($oes->languages as $languageData) {

                /* check if index page in different language */
                if ($this->current_url === get_site_url() . '/' . strtolower($languageData['abb']) . '/' . ($oes->theme_index['slug'] ?? 'index') . '/')
                    $this->links_for_index();
            }

            /* check if page is archive in different language */
            foreach ($oes->post_types as $postType => $ignore)
                foreach ($oes->languages as $languageData)
                    if ($this->current_url === get_site_url() . '/' . strtolower($languageData['abb']) . '/' .
                        (get_post_type_object($postType)->rewrite['slug'] ?? $postType) . '/') {
                        $this->links_for_archive();
                    }

            /* check if taxonomies */
            $this->links_for_taxonomies();

            /* check for more, customized page options */
            $this->links_for_data_custom();
        }


        /**
         * Custom page link (overwritten by project processing).
         *
         * @return void
         */
        function links_for_data_custom(): void
        {
        }

    }
}
