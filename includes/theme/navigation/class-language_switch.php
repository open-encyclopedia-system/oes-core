<?php

namespace OES\Navigation;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


if (!class_exists('Language_Switch')) {

    /**
     * Class Language_Switch
     *
     * This class prepares the template management for the frontend theme.
     */
    class Language_Switch
    {

        /** @var string The page language */
        public string $language = 'language0';

        /** @var string Current URL */
        public string $current_url = '';

        /** @var string Current abb */
        public string $current_label = '';

        /** @var array The target language(s) */
        public array $target_languages = [];

        /** @var array The links to the connected pages */
        public array $links = [];


        /**
         * Language_Switch constructor.
         *
         * @param array $args Additional parameters
         */
        public function __construct(array $args = [])
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
        public function set_parameters(array $args): void
        {
            /* prepare languages */
            global $oes_language, $oes;
            $this->language = $oes_language ?? 'language0';
            foreach ($oes->languages as $languageKey => $languageData) {
                if ($languageKey !== $this->language) {
                    $this->links[$languageKey] = [
                        'text' => $languageData['abb'] ?? $languageData['label'],
                        'abb' => $languageData['abb'] ?? $languageKey
                    ];
                } else $this->current_label = $languageData['abb'] ?? $languageData['label'];
            }

            $this->current_url = $args['current_url'] ?? oes_get_current_url(false);
        }


        /**
         * Prepare the language switch link for the current page.
         *
         * @return void
         */
        public function prepare_link(): void
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
         * Set links for front page.
         * If a translation of this page exists, link to translation, otherwise change only language of page.
         *
         * @return void
         */
        public function links_for_front_page(): void
        {
            foreach ($this->links as $language => $data) {
                $url = home_url();

                /* check if frontpage in other language exists */
                if ($page = get_page_by_path(strtolower($data['abb']))) $url = get_permalink($page->ID);
                $this->links[$language]['link'] = $url;
            }
        }


        /**
         * Set links for page.
         * If a translation of this page exists, link to translation, otherwise change only language of page.
         *
         * @return void
         */
        public function links_for_page(): void
        {
            global $post;
            if ($translations = oes_get_field('field_oes_page_translations', $post->ID))
                foreach ($translations as $translationID) {
                    $pageLanguage = oes_get_post_language($translationID);
                    if ($pageLanguage && $pageLanguage !== $this->language) {
                        $this->links[$pageLanguage]['link'] = get_permalink($translationID);
                        break;
                    }
                }
        }


        /**
         * Set links for single.
         * If a translation of this post exists, link to translation, otherwise change only language of post.
         *
         * @return void
         */
        public function links_for_single(): void
        {
            global $oes_post;
            if (!empty($oes_post->translations)) {
                foreach ($oes_post->translations as $translation)
                    if (isset($translation['id']) &&
                        isset($translation['language'])) {
                        $this->links[$translation['language']]['link'] = get_permalink($translation['id']);
                    }
            }
        }


        /**
         * Set links for taxonomy.
         * Change only language of page.
         *
         * @return void
         */
        public function links_for_tax(): void
        {
        }


        /**
         * Set links for archive.
         * Change only language of page.
         *
         * @return void
         */
        public function links_for_archive(): void
        {
        }


        /**
         * Set links for search.
         *
         * @return void
         */
        public function links_for_search(): void
        {
            foreach ($this->links as $languageKey => $languageAbb) {
                $this->links[$languageKey]['link'] = get_site_url() . '/';
                $this->links[$languageKey]['param']['s'] = $_GET['s'] ?? '';
            }
        }


        /**
         * Set links for index.
         * Change only language of page.
         *
         * @return void
         */
        public function links_for_index(): void
        {
        }


        /**
         * Set links for taxonomy archives.
         *
         * @return void
         */
        public function links_for_taxonomies(): void
        {
        }


        /**
         * Set links for other pages.
         *
         * @return void
         */
        public function links_for_data_other(): void
        {
            /* check if index page */
            if ($this->current_url === get_site_url() . '/' . ($oes->theme_index['slug'] ?? 'index') . '/')
                $this->links_for_index();

            /* check if oes page in different language */
            global $oes;
            foreach ($oes->languages as $languageData) {

                /* check if index page in different language */
                if ($this->current_url === get_site_url() . '/' . strtolower($languageData['abb']) . '/' .
                    ($oes->theme_index['slug'] ?? 'index') . '/')
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
        public function links_for_data_custom(): void
        {
        }


        /**
         * Get the link text of the html representation of the language switch.
         *
         * @param string $targetLanguage The target language.
         * @return string Returns the link text.
         */
        public function get_menu_item_title(string $targetLanguage = ''): string
        {
            /* take first target language if not specified */
            if (empty($targetLanguage)) $targetLanguage = array_key_first($this->links);


            /* prepare link text */
            $linkText = $this->links[$targetLanguage]['text'] ?? '';
            if (empty($linkText))
                $linkText = $this->target_languages[$targetLanguage] ?? $targetLanguage;


            /**
             * Filters the language switch link text.
             *
             * @param string $link The link text.
             * @param Language_Switch $this The language switch object.
             * @param string $targetLanguage The target language.
             */
            return apply_filters('oes/language_switch_link_text', $linkText, $this, $targetLanguage);
        }


        /**
         * Get the link of the language switch for the classic menu item.
         *
         * @param string $targetLanguage The target language.
         * @return string Returns the menu item link.
         */
        public function get_menu_item_link(string $targetLanguage = ''): string
        {
            /* take first target language if not specified */
            if (empty($targetLanguage)) $targetLanguage = array_key_first($this->links);

            /* prepare link, and add switch info */
            $link = $this->links[$targetLanguage]['link'] ?? '';
            $link .= (parse_url($link, PHP_URL_QUERY) ? '&' : '?') . 'oes-language-switch=' . $targetLanguage;


            /* add additional parameters*/
            if (isset($this->links[$targetLanguage]['param']))
                foreach ($this->links[$targetLanguage]['param'] as $key => $value) $link .= '&' . $key . '=' . $value;


            /**
             * Filters the language switch link.
             *
             * @param string $link The link.
             * @param Language_Switch $this The language switch object.
             * @param string $targetLanguage The target language.
             */
            return apply_filters('oes/language_switch_link', $link, $this, $targetLanguage);
        }


        /**
         * Get the HTML representation of the language switch.
         *
         * @param string $style Optional style. Default displays all links. Valid options are:
         *  'is-style-oes-popup'    : Display links as popup.
         *  'is-style-oes-two'      : Display only one link.
         *  'is-style-default'      : Display all links.
         *
         * @return string Return the HTML representation of the language switch.
         */
        public function html(string $style = 'is-style-oes-default'): string
        {
            switch ($style) {
                case 'is-style-oes-popup':
                    return $this->get_popup_links_html();

                case 'is-style-oes-two':
                    return $this->get_single_link_html();

                case 'is-style-oes-default':
                default:
                    return $this->get_all_links_html();
            }
        }


        /**
         * Get one link (the link to the first target language). If only two languages exists, this is the
         * opposite language.
         *
         * @return string Return single link.
         */
        public function get_single_link_html(): string
        {
            $firstKey = array_key_first($this->links);
            if ($firstKey) return $this->prepare_single_link($firstKey, $this->links[$firstKey]);
            else return sprintf('<a href="%s" class="%s">%s</a>',
                'javascript:void(0);',
                'oes-switch-active',
                $this->current_label ?? $this->language
            );
        }


        /**
         * Get all links.
         *
         * @return string Return all links.
         */
        public function get_all_links_html(): string
        {
            /* prepare item itself */
            $switchArray[$this->language] = sprintf('<a href="%s" class="%s">%s</a>',
                'javascript:void(0);',
                'oes-switch-active',
                $this->current_label ?? $this->language
            );
            foreach ($this->links as $language => $data)
                if ($this->language !== $language)
                    $switchArray[$language] = $this->prepare_single_link($language, $data);
            ksort($switchArray);

            return '<span>' . implode(' | ', $switchArray) . '</span>';
        }


        /**
         * Get links as popup.
         *
         * @return string Return popup.
         */
        public function get_popup_links_html(): string
        {
            $switchArray = [];
            foreach ($this->links as $language => $data)
                if ($this->language !== $language)
                    $switchArray[$language] = $this->prepare_single_link($language, $data);
            ksort($switchArray);

            return \OES\Popup\get_single_html(
                'oes-language-switch-list',
                '<span class="oes-language-switch">' . $this->current_label . '</span>',
                implode('', $switchArray)
            );
        }


        /**
         * Prepare a single link.
         *
         * @param string $language The target language for this link.
         * @param array $data The language data.
         * @return string Return a single link.
         */
        public function prepare_single_link(string $language, array $data): string
        {
            /* prepare url */
            $url = ($data['link'] ?? '') . '?oes-language-switch=' . $language;

            /* add additional parameters*/
            if (isset($data['param']))
                foreach ($data['param'] as $key => $value) $url .= '&' . $key . '=' . $value;

            return sprintf('<a href="%s" class="%s">%s</a>',
                $url,
                ($data['class'] ?? ''),
                $data['text'] ?? ''
            );
        }
    }
}