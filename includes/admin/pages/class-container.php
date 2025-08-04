<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Page')) oes_include('admin/pages/class-page.php');

if (!class_exists('Container')) :

    /**
     * Class Container
     *
     * Create container page for multiple post types or taxonomies admin pages.
     */
    class Container extends Page
    {
        /** @inheritdoc */
        protected bool $is_core_page = true;

        /** @var string The container key. */
        protected string $key = '';

        /** @var string The page menu title. */
        protected string $menu_title = '';

        /** @var array The subpage information. */
        protected array $subpage_data = [];

        /** @var array Parameters for an info page. */
        public array $info_page = [];

        /** @var string The main slug for the container page. */
        public string $main_slug = '';

        /** @inheritdoc */
        protected function prepare_actions(): void
        {
            $this->prepare_info_page();
            $this->prepare_subpages();

            parent::prepare_actions();
            add_filter('parent_file', [$this, 'modify_parent_file']);
            add_filter('submenu_file', [$this, 'modify_submenu_file']);
        }

        /** @inheritdoc */
        protected function get_page_parameters_defaults(): array
        {
            return [
                'menu_title' => '[' . $this->key . ']',
                'capability' => 'edit_posts',
                'function' => [$this, 'html'],
                'position' => 20,
                'menu_slug' => 'container_' . $this->key,
                'generated' => true,
                'hide' => false
            ];
        }

        /** @inheritdoc */
        function set_additional_parameters(): void
        {
            $this->main_slug = 'container_' . $this->key;

            if (empty($this->page_parameters['subpages'] ?? '')) {
                $this->page_parameters['subpages'] = $this->get_elements();
            }
            if (empty($this->info_page['elements'] ?? '')) {
                $this->info_page['elements'] = $this->get_elements();
            }
        }

        /**
         * Add subpages for container in admin menu.
         * @return array
         */
        protected function get_elements(): array
        {
            global $oes;

            // Check if key is post type
            $postType = $oes->post_types[$this->key] ?? false;
            if (!$postType) {
                return [];
            }

            return $this->get_default_elements($postType['parent'] ?? '');
        }

        /**
         * Get default elements.
         *
         * @param string $parent
         * @param bool $elements_only
         * @return array
         */
        protected function get_default_elements(string $parent = '', bool $elements_only = false): array
        {
            $elements = [];

            if (!empty($parent)) {
                $elements[] = $parent;
            }

            $elements[] = $this->key;

            if ($elements_only) {
                return $elements;
            }

            $postTypeTaxonomies = get_post_type_object($this->key)->taxonomies ?? [];
            foreach ($postTypeTaxonomies as $taxonomy) {
                $elements[] = $taxonomy;
            }

            $parentPostTypeTaxonomies = get_post_type_object($parent)->taxonomies ?? [];
            foreach ($parentPostTypeTaxonomies as $taxonomy) {
                $elements[] = $taxonomy;
            }
            return array_unique($elements);
        }

        /**
         * Prepares an info subpage if the `info_page` config is valid and visible.
         *
         * @return void
         */
        public function prepare_info_page(): void
        {
            if (
                !empty($this->info_page) &&
                !empty($this->info_page['elements']) &&
                $this->info_page['elements'] !== 'hidden'
            ) {
                new Subpage([
                    'subpage' => true,
                    'page_parameters' => [
                        'parent_slug' => $this->page_parameters['menu_slug'],
                        'page_title' => $this->info_page['label'] ?? 'Title missing',
                        'menu_title' => $this->info_page['label'] ?? 'Title missing',
                        'capability' => $this->page_parameters['capability'],
                        'menu_slug' => $this->page_parameters['menu_slug'],
                        'function' => $this->page_parameters['function'],
                        'position' => 1
                    ]
                ]);
            }
        }

        /**
         * Dynamically prepares subpages based on post types, taxonomies, or custom definitions.
         *
         * @return void
         */
        public function prepare_subpages(): void
        {
            $position = 1;

            foreach ($this->page_parameters['subpages'] ?? [] as $page) {

                // Determine if it's a post type
                if ($postType = get_post_type_object($page)) {
                    $objectName = $page;
                    $label = $postType->label;
                    $href = 'edit.php?post_type=' . $page;
                    $pageSlug = 'edit.php?post_type=' . $objectName;
                    $capabilities = 'edit_posts';

                    // Or a taxonomy
                } elseif ($taxonomy = get_taxonomy($page)) {

                    $countForPostType = '';
                    foreach($taxonomy->object_type as $singlePostType){
                        if(in_array($singlePostType, $this->page_parameters['subpages'])){
                            $countForPostType = '&post_type=' . $singlePostType;
                            break;
                        }
                    }

                    $objectName = $page;
                    $label = $taxonomy->label;
                    $href = 'edit-tags.php?taxonomy=' . $page;
                    $pageSlug = 'edit-tags.php?taxonomy=' . $objectName . $countForPostType;
                    $capabilities = 'edit_tags';

                    // Or a custom-defined item
                } else {
                    $objectName = false;
                    $label = is_array($page) && isset($page['label']) ? $page['label'] : (string)$page;
                    $href = 'admin.php?page=' . $this->main_slug;
                    $pageSlug = $this->main_slug . '_container';
                    $capabilities = 'edit_posts';
                }

                // Redirect first submenu if no info_page['elements'] exists
                if (empty($this->info_page['elements']) && $position === 1) {
                    add_action('admin_init', function () use ($pageSlug) {
                        global $plugin_page;
                        if ($plugin_page === $pageSlug) {
                            wp_redirect(admin_url($pageSlug));
                            exit;
                        }
                    });
                }

                // Create the subpage
                new Subpage([
                    'subpage' => true,
                    'page_parameters' => [
                        'parent_slug' => $this->page_parameters['menu_slug'],
                        'page_title' => $label,
                        'menu_title' => $label,
                        'capability' => $capabilities,
                        'menu_slug' => $pageSlug,
                        'function' => '', // Assumes this is handled externally
                        'position' => ++$position
                    ]
                ]);

                // Store subpage display metadata
                $this->subpage_data[$page] = [
                    'label' => $label,
                    'href' => $href,
                    'display_summary' => in_array(
                        $page,
                        is_array($this->info_page['elements'] ?? []) ? $this->info_page['elements'] : [],
                        true
                    )
                ];

                // Hook view filters if it's a known post type or taxonomy
                if ($objectName) {
                    add_filter("views_edit-$objectName", [$this, 'view_for_container_elements'], 10, 1);
                }
            }
        }

        /** @inheritdoc */
        function html(): void
        {
            ?>
            <div class="oes-page-wrap wrap">
            <!-- dummy for admin notices -->
            <h2 class="oes-display-none"></h2>
            <h1 class="wp-heading-inline"><?php echo $this->page_parameters['menu_title']; ?></h1>
            <hr class="wp-header-end"><?php
            echo $this->html_tabs();
            $this->display_page_content();
            ?>
            </div><?php
        }

        /**
         * Display the page content.
         * @return void
         */
        protected function display_page_content(): void
        {
            echo $this->info_page_html();
        }

        /**
         * Generate the HTML representation of the info page, including summaries of posts and taxonomies.
         *
         * @param array $additionalArgs Optional arguments passed to WP_Query or get_terms().
         * @return string HTML table displaying content summaries.
         */
        public function info_page_html(array $additionalArgs = []): string
        {
            if (empty($this->subpage_data)) {
                return '';
            }

            $headerHTML = '';
            $bodyHTML = '';

            foreach ($this->subpage_data as $pageKey => $page) {
                if (empty($page['display_summary'])) {
                    continue;
                }

                $cleanKey = str_starts_with($pageKey, 'modified_') ? substr($pageKey, 9) : $pageKey;

                $postType = get_post_type_object($cleanKey);
                $taxonomy = get_taxonomy($cleanKey);

                if ($postType) {
                    [$header, $body] = $this->render_post_type_summary($postType, $additionalArgs);
                } elseif ($taxonomy) {
                    [$header, $body] = $this->render_taxonomy_summary($taxonomy);
                } else {
                    continue;
                }

                $headerHTML .= "<th>$header</th>";
                $bodyHTML .= "<td>$body</td>";
            }

            return '<table class="oes-menu-container wp-list-table widefat fixed striped table-view-list">' .
                '<tr>' . $headerHTML . '</tr>' .
                '<tr>' . $bodyHTML . '</tr>' .
                '</table>';
        }

        /**
         * Render the header and post list for a given post type.
         *
         * @param \WP_Post_Type $postType
         * @param array $page Subpage config.
         * @param array $additionalArgs
         * @return array [headerHtml, bodyHtml]
         */
        protected function render_post_type_summary($postType, array $additionalArgs = []): array
        {
            global $oes;

            $headerParts = [];
            $bodyParts = [];

            $defaults = [
                'post_type' => $postType->name,
                'number_of_posts' => 10,
                'numberposts' => 10,
                'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'],
                'orderby' => 'post_modified'
            ];
            $args = array_merge($defaults, $additionalArgs['wp_parse_args'] ?? []);

            $queriedPosts = get_posts($args);
            $posts = $this->modify_posts($queriedPosts, $postType->name, $additionalArgs);

            $button = '';
            if (empty($oes->post_types[$postType->name]['parent'])) {
                $button = oes_get_html_anchor(
                    esc_html($postType->labels->add_new),
                    esc_url('post-new.php?post_type=' . $postType->name),
                    false,
                    'oes-container-page-title-action page-title-action'
                );
            }

            $title = esc_html($postType->label);
            $headerParts[] = '<h3>' . $title . '</h3>' . $button;

            if (!empty($posts)) {
                $postList = '<ul>';
                foreach ($posts as $post) {

                    $postList .= sprintf(
                        '<li><a href="%s">%s</a><span class="oes-container-after-item">%s</span></li>',
                        esc_url(get_edit_post_link($post->ID)),
                        esc_html($post->post_title),
                        esc_html(get_post_status_object($post->post_status)->label)
                    );
                }

                $moreUrl = admin_url('edit.php?post_type=' . $postType->name);

                $postList .= '<li class="oes-container-more-string">'
                    . oes_get_html_anchor(__('more...', 'oes'), esc_url($moreUrl))
                    . '</li></ul>';

                $bodyParts[] = $postList;
            } else {
                $bodyParts[] = '<span>' . esc_html__('No posts found.', 'oes') . '</span>';
            }

            return [implode('', $headerParts), implode('', $bodyParts)];
        }

        /**
         * Override to modify posts.
         */
        protected function modify_posts(array $posts, string $postType, array $args): array
        {
            return $posts;
        }

        /**
         * Render the header and term list for a given taxonomy.
         *
         * @param \WP_Taxonomy $taxonomy
         * @return array [headerHtml, bodyHtml]
         */
        protected function render_taxonomy_summary($taxonomy): array
        {
            $button = oes_get_html_anchor(
                esc_html($taxonomy->labels->add_new_item),
                esc_url('edit-tags.php?taxonomy=' . $taxonomy->name),
                false,
                'oes-container-page-title-action page-title-action'
            );

            $header = '<h3>' . esc_html($taxonomy->label) . '</h3>' . $button;

            $args = [
                'taxonomy' => $taxonomy->name,
                'hide_empty' => false,
                'number' => 10
            ];

            $queriedTerms = get_terms($args);
            $terms = $this->modify_terms($queriedTerms, $taxonomy->name);

            if (empty($terms) || is_wp_error($terms)) {
                $body = '<span>' . esc_html__('No terms found', 'oes') . '</span>';
                return [$header, $body];
            }

            $termList = '<ul>';
            foreach ($terms as $term) {
                $termList .= sprintf(
                    '<li><a href="%s">%s</a><span class="oes-container-after-item"></span></li>',
                    esc_url(get_edit_term_link($term->term_id)),
                    esc_html($term->name)
                );
            }

            $termList .= '<li class="oes-container-more-string">'
                . oes_get_html_anchor(__('more...', 'oes'), esc_url('edit-tags.php?taxonomy=' . $taxonomy->name))
                . '</li></ul>';

            return [$header, $termList];
        }

        /**
         * Override to modify terms.
         */
        protected function modify_terms(array $terms, string $taxonomy): array
        {
            return $terms;
        }

        /**
         * Generates the HTML tab navigation for the current container page.
         * Used in subpages to display the associated post type/taxonomy tabs.
         *
         * @return string HTML markup for the tab navigation.
         */
        public function html_tabs(): string
        {
            $activeTab = $_GET['taxonomy'] ?? $_GET['post_type'] ?? false;

            $tabString = '<h2 class="nav-tab-wrapper">';

            // Info tab
            if (!empty($this->info_page['elements']) && $this->info_page['elements'] !== 'hidden') {
                $label = esc_html($this->info_page['label'] ?? 'Title missing');
                $url = esc_url('admin.php?page=' . $this->page_parameters['menu_slug']);
                $classes = ($activeTab ? '' : 'nav-tab-active ') . 'nav-tab';

                $tabString .= oes_get_html_anchor($label, $url, false, $classes);
            }

            // Subpage tabs
            foreach ($this->subpage_data as $key => $page) {
                $label = esc_html($page['label'] ?? $key);
                $url = esc_url($page['href'] ?? '#');
                $classes = ($activeTab === $key ? 'nav-tab-active ' : '') . 'nav-tab';

                $tabString .= oes_get_html_anchor($label, $url, false, $classes);
            }

            $tabString .= '</h2>';

            return $tabString;
        }

        /**
         * Adds tab navigation to the edit screen of a container post type or taxonomy.
         * Hooked via the `views_edit-{post_type}` filter.
         *
         * @param array $views The list of view links above the table.
         * @return array The same $views array, unchanged.
         */
        public function view_for_container_elements(array $views): array
        {
            echo $this->html_tabs();
            return $views;
        }

        /**
         * Alters the parent menu highlight in the admin for subpages (post types or taxonomies).
         * Hooked via the `parent_file` filter.
         *
         * @param string $parent_file The current parent file.
         * @return string Modified parent file for correct menu highlighting.
         */
        public function modify_parent_file(string $parent_file): string
        {
            global $current_screen;

            // Only adjust on relevant admin screens
            if (!in_array($current_screen->base, ['post', 'edit', 'edit-tags'], true)) {
                return $parent_file;
            }

            $key = $current_screen->taxonomy ?? $current_screen->post_type ?? null;
            if (!$key || !isset($this->subpage_data[$key])) {
                return $parent_file;
            }

            return $this->page_parameters['menu_slug'];
        }

        /**
         * Alters the submenu highlight in the admin for subpages (post types or taxonomies).
         * Hooked via the `submenu_file` filter.
         *
         * @param mixed $submenu_file The current submenu file.
         * @return mixed Modified parent file for correct menu highlighting.
         */
        public function modify_submenu_file($submenu_file)
        {
            global $current_screen;

            // Only adjust on relevant admin screens
            if (!in_array($current_screen->base, ['post', 'edit', 'edit-tags'], true)) {
                return $submenu_file;
            }

            $keyTaxonomy = $current_screen->taxonomy ?? null;
            $keyPostType = $current_screen->post_type ?? null;
            if($keyTaxonomy && $keyPostType){
                return 'edit-tags.php?taxonomy=' . $keyTaxonomy . '&post_type=' . $keyPostType;
            }
            return $submenu_file;
        }
    }
endif;