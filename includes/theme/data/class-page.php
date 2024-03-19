<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Page')) {

    /**
     * Class OES_Page
     *
     * This class prepares a page for display in the frontend theme.
     */
    class OES_Page extends OES_Object
    {

        //Overwrite parent
        public bool $has_theme_subtitle = true;

        //Overwrite parent
        public $post_type = 'page';

        /** @var array $translations The translations */
        public array $translations = [];


        //Overwrite parent
        public function set_parameters(): void
        {
            $this->language = $this->get_language();
            $this->set_title();
            $this->set_translations();

            /* check if page is front page or translation of front page */
            if (is_front_page()) $this->is_frontpage = true;
            else {
                $frontPageID = get_option('page_on_front');
                foreach ($this->translations as $translation)
                    if ($translation['id'] === (int)$frontPageID) $this->is_frontpage = true;
            }

            /* set theme labels */
            $this->theme_labels = OES()->theme_labels;
        }


        //Overwrite parent
        public function get_language(): string
        {
            global $oes, $oes_language;

            /* return early if only one language or empty post type */
            if (sizeof($oes->languages) < 2 || empty($this->post_type)) return 'language0';

            /* check if language is defined by schema */
            $schemaLanguage = $oes->post_types[$this->post_type]['language'] ?? '';
            if (!empty($schemaLanguage) && $schemaLanguage != 'none') {
                if (oes_starts_with($schemaLanguage, 'parent__'))
                    $language = oes_get_field(substr($schemaLanguage, 8), oes_get_parent_id($this->object_ID)) ?? 'language0';
                else $language = oes_get_field($schemaLanguage, $this->object_ID) ?? 'language0';
            } else $language = oes_get_field('field_oes_post_language', $this->object_ID) ?? 'language0';

            return (empty($language) || $language === 'all') ? $oes_language : $language;
        }


        /**
         * Set version information of this post.
         */
        public function set_translations()
        {
            /* check for  existing translation */
            if ($translationField =
                oes_get_field('field_oes_page_translations', $this->object_ID)) {
                if (is_array($translationField))
                    foreach ($translationField as $singlePost)
                        $this->translations[] = [
                            'id' => $singlePost->ID ?? $singlePost,
                            'language' => oes_get_post_language($singlePost->ID ?? $singlePost)
                        ];
            }
        }


        //Overwrite parent
        public function prepare_html_main_classic(array $args = []): array
        {
            /* prepare title */
            $title = '';
            if (!$this->has_theme_subtitle) $title = '<div class="oes-sub-subheader-container">' .
                '<div class="oes-sub-subheader"><h1 class="oes-single-title">' . $this->title . '</h1></div>' .
                '</div>';

            return [
                '010_title' => $title,
                '100_toc' => (!isset($args['skip-toc']) || !$args['skip-toc']),
                '200_content' => $args['content'] ?? ''
            ];
        }
    }
}