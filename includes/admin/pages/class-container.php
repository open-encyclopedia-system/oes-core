<?php

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

        //Overwrite
        protected bool $is_core_page = true;

        /** @var string The page menu title. */
        protected string $menu_title = '';

        /** @var array The subpage keys. */
        protected array $subpages = [];

        /** @var array The subpage information. */
        protected array $subpage_data = [];

        /** @var array Parameters for an info page.*/
        public array $info_page = [];

        /** @var string The main slug for the container page.*/
        public string $main_slug = '';

        /** @var array The default parameters for the post query. */
        protected array $get_posts_defaults = [
            'number_of_posts' => 10,
            'numberposts' => 10,
            'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'],
            'orderby' => 'post_modified'
        ];

        /** @var array The default parameters for the term query. */
        protected array $get_terms_defaults = [
            'hide_empty' => false,
            'number' => 10
        ];


        //Overwrite
        function set_additional_parameters(): void
        {

            /* set slug */
            $this->main_slug = $this->page_parameters['menu_slug'];

            /* create info page */
            if (!empty($this->info_page)) {
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
                    ]]);
            }

            /* create subpages */
            $position = 1;
            foreach ($this->subpages as $page) {

                /* prepare subpage data */
                if ($postType = get_post_type_object($page)) {
                    $objectName = $page;
                    $label = $postType->label;
                    $href = 'edit.php?post_type=' . $page;
                    $pageSlug = 'edit.php?post_type=' . $objectName;
                    $capabilities = 'edit_posts';
                } elseif ($taxonomy = get_taxonomy($page)) {
                    $objectName = $page;
                    $label = $taxonomy->label;
                    $href = 'edit-tags.php?taxonomy=' . $page;
                    $pageSlug = 'edit-tags.php?taxonomy=' . $objectName;
                    $capabilities = 'edit_tags';
                } else {
                    $objectName = false;
                    $label = $page['label'] ?? $page;
                    $href = 'admin.php?page=' . $this->main_slug;
                    $pageSlug = $this->main_slug . '_container';
                    $capabilities = 'edit_posts';
                }

                /* check if element replaces info page */
                if (!$this->info_page['elements'] && $position === 1) {

                    /* redirect first sub-menu. @oesDevelopment Is there a better way to do this? */
                    $pageSlug = $this->page_parameters['menu_slug'];
                    add_action('admin_init', function () use ($pageSlug) {
                        global $plugin_page;
                        if ($plugin_page === $pageSlug) {
                            wp_redirect(admin_url($pageSlug));
                            exit;
                        }
                    });
                }

                /* check if post type exists multiple times on page (depending on field) */
                $columns = [];
                if (isset($page['field']) && $fieldObject = oes_get_field_object($page['field']))
                    if ($fieldObject['type'] == 'select' && !empty($fieldObject['choices'])) {

                        /* check for filter */
                        if (isset($page['valid_values'])) {
                            foreach ($page['valid_values'] as $value)
                                if (isset($fieldObject['choices'][$value]))
                                    $columns[$value] = $fieldObject['choices'][$value];
                        } else $columns = $fieldObject['choices'];
                    }

                /* create sub pages */
                $args = [
                    'subpage' => true,
                    'page_parameters' => [
                        'parent_slug' => $this->page_parameters['menu_slug'],
                        'page_title' => $label,
                        'menu_title' => $label,
                        'capability' => $capabilities,
                        'menu_slug' => $pageSlug,
                        'function' => '',
                        'position' => ++$position
                    ]];
                new Subpage($args);

                /* add info for tab display */
                $this->subpage_data[$page] = [
                    'label' => $label,
                    'href' => $href,
                    'display_summary' => in_array($page, $this->info_page['elements'] ?: []),
                    'columns' => $columns,
                    'field' => $page['field'] ?? false
                ];

                /* hook tabs to display of subpages */
                if ($objectName) add_filter('views_edit-' . $objectName, [$this, 'view_for_container_elements'], 10, 1);
            }

            add_filter('parent_file', [$this, 'modify_parent_file']);
        }


        //Overwrite
        function html(): void
        {

            /**
             * Filters the custom arguments.
             *
             * @param array $args Additional arguments.
             * @param string $slug The page slug.
             */
            $args = apply_filters('oes/menu_container_custom_html', [], $this->main_slug);


            /**
             * Filters the page content for admin view.
             *
             * @param array $pageContent The page content.
             * @param string $slug The page slug.
             * @param Container $this The container class.
             */
            $pageContent = apply_filters('oes/page_container_content',
                $this->prepare_info_page($args),
                $this->main_slug,
                $this);

            ?>
            <div class="oes-page-wrap wrap">
            <!-- dummy for admin notices -->
            <h2 class="oes-display-none"></h2>
            <h1 class="wp-heading-inline"><?php echo $this->page_parameters['menu_title']; ?></h1>
            <hr class="wp-header-end"><?php
            echo $this->html_tabs();
            echo $pageContent;
            ?>
            </div><?php
        }


        /**
         * Prepare the info page content.
         *
         * @param array $additionalArgs Additional arguments.
         * @return string Return the html representation of the info page.
         */
        function prepare_info_page(array $additionalArgs = []): string
        {

            /* prepare page content for info page */
            $pageContent = '<table class="oes-menu-container wp-list-table widefat fixed striped table-view-list">';

            /* prepare data */
            if (!empty($this->subpage_data)) {

                $tableDataHeader = '';
                $tableData = '';


                /**
                 * Filters the subpages
                 *
                 * @param array $subPages The subpages.
                 * @param string $slug The page slug.
                 */
                $subPages = apply_filters('oes/menu_container_subpages', $this->subpage_data, $this->main_slug);


                /* loop through post types */
                foreach ($subPages as $pageKey => $page)
                    if (isset($page['display_summary']) && $page['display_summary']) {

                        /* check if modified post type */
                        if (oes_starts_with($pageKey, 'modified_')) $pageKey = substr($pageKey, 9);

                        /* check if post type or taxonomy */
                        $postType = false;
                        $taxonomy = false;
                        if (get_post_type_object($pageKey)) $postType = get_post_type_object($pageKey);
                        elseif (is_string($page) && get_post_type_object($page))
                            $postType = get_post_type_object($page);
                        elseif (get_taxonomy($pageKey)) $taxonomy = get_taxonomy($pageKey);
                        elseif (is_string($page) && get_taxonomy($page)) $taxonomy = get_taxonomy($page);

                        /* get post type object */
                        if ($postType) {

                            /* get all posts of this post type and prepare data */
                            $postArray = [];
                            $parseArgs = array_merge($this->get_posts_defaults, $additionalArgs['wp_parse_args'] ?? []);
                            $args = wp_parse_args(['post_type' => $postType->name], $parseArgs);
                            if ($posts = get_posts($args))
                                foreach ($posts as $post) {

                                    /* store information */
                                    $key = preg_replace('/[^a-z0-9_]/',
                                        '',
                                        strtolower($post->post_title));
                                    $postArray[$key] = [
                                        'id' => $post->ID,
                                        'text' => $post->post_title,
                                        'after-text' => get_post_status_object($post->post_status)->label
                                    ];
                                }

                            /* check if multiple columns */
                            $columns = empty($page['columns']) ? ['ignore' => true] : $page['columns'];
                            foreach ($columns as $selectValue => $selectLabel) {

                                /* prepare edit button */
                                $button = '';
                                if (!(OES()->post_types[$pageKey]['parent'] ?? false))
                                    $button = oes_get_html_anchor($postType->labels->add_new,
                                        'post-new.php?post_type=' . $postType->name,
                                        false,
                                        'oes-container-page-title-action page-title-action'
                                    );


                                /**
                                 * Filters the page content for admin view.
                                 *
                                 * @param array $pageContent The page content.
                                 * @param string $slug The page slug.
                                 * @param string $selectValue The select value (if applicable).
                                 */
                                $tableDataHeaderString = apply_filters('oes/menu_container_posts_header',
                                    ('<h3>' . $postType->label .
                                        ($selectValue === 'ignore' ?
                                            '' :
                                            ' (' . $selectLabel . ')') . '</h3>' . $button),
                                    $pageKey,
                                    $selectValue);

                                $tableDataHeader .= '<th>' . $tableDataHeaderString . '</th>';

                                /* loop through posts */
                                if ($postArray) {


                                    /**
                                     * Filters the posts for admin view.
                                     *
                                     * @param array $postArray The array containing the posts.
                                     * @param string $postType The post type.
                                     * @param string $selectValue The select value (if applicable).
                                     * @param array $additionalArgs Additional arguments.
                                     */
                                    $postArray = apply_filters('oes/menu_container_posts',
                                            $postArray, $postType, $selectValue, $additionalArgs);

                                    /* display posts */
                                    $postInfo = '<ul>';
                                    foreach ($postArray as $postItem) {

                                        /* check if post meets filter */
                                        if ($selectValue !== 'ignore' && isset($page['field']) &&
                                            $post = get_post($postItem['id'])) {
                                            $fieldValue = oes_get_field_display_value(
                                                    $page['field'],
                                                    $post->ID,
                                                    ['value-is-link' => false]
                                            );
                                            if ($fieldValue != $selectLabel) continue;
                                        }

                                        $postInfo .= sprintf(
                                            '<li><a href="%s">%s</a>' .
                                            '<span class="oes-container-after-item">%s</span></li>',
                                            get_edit_post_link($postItem['id']),
                                            $postItem['text'],
                                            $postItem['after-text']);
                                    }

                                    /* further link */
                                    $moreString = oes_get_html_anchor(__('more...', 'oes'),
                                        admin_url('edit.php?post_type=' . $postType->name) .
                                        ((isset($page['field']) && $page['field']) ?
                                            '&' . $page['field'] . '=' . $selectValue : false)
                                    );

                                    $postInfo .= '<li class="oes-container-more-string">' . $moreString . '</li>';
                                    $postInfo .= '</ul>';

                                    $tableData .= '<td>' . $postInfo . '</td>';
                                } else $tableData .= '<td><span>' .
                                    __('No posts found.', 'oes') . '</span></td>';
                            }
                        } elseif ($taxonomy) {

                            /* set label as header */
                            $button = oes_get_html_anchor($taxonomy->labels->add_new_item,
                                'edit-tags.php?taxonomy=' . $taxonomy->name,
                                false,
                                'oes-container-page-title-action page-title-action'
                            );
                            $tableDataHeader .= '<th><h3>' . $taxonomy->label . '</h3>' . $button . '</th>';

                            /* get all terms of this taxonomy */
                            $args = wp_parse_args(['taxonomy' => $taxonomy->name], $this->get_terms_defaults);
                            if ($terms = get_terms($args)) {

                                /* prepare data (to sort) */
                                $termArray = [];
                                foreach ($terms as $term) {

                                    /* store information */
                                    $key = preg_replace('/[^a-z0-9_]/', '', strtolower($term->name));
                                    $termArray[$key] = [
                                        'id' => $term->term_id,
                                        'text' => $term->name,
                                        'after-text' => ''
                                    ];
                                }


                                /**
                                 * Filters the terms for admin view.
                                 *
                                 * @param array $termArray The array containing the terms.
                                 * @param string $postType The post type.
                                 * @param string $pageKey The page key.
                                 */
                                $termArray = apply_filters('oes/menu_container_terms',
                                        $termArray,
                                        $postType,
                                        $pageKey);


                                /* display posts */
                                $postInfo = '<ul>';
                                foreach ($termArray as $termItem)
                                    $postInfo .= sprintf(
                                        '<li><a href="%s">%s</a>' .
                                        '<span class="oes-container-after-item">%s</span></li>',
                                        get_edit_term_link($termItem['id']),
                                        $termItem['text'],
                                        $termItem['after-text']);


                                /**
                                 * Filters the "read more" string.
                                 *
                                 * @param array $moreString The "read more" string.
                                 * @param string $taxonomy The taxonomy name.
                                 * @param string $pageKey The page key.
                                 */
                                $moreString = apply_filters('oes/menu_container_terms_more_string',
                                    oes_get_html_anchor(__('more...', 'oes'),
                                        admin_url('edit-tags.php?taxonomy=' . $taxonomy->name)
                                    ),
                                    $taxonomy,
                                    $pageKey);

                                $postInfo .= '<li class="oes-container-more-string">' . $moreString . '</li>';
                                $postInfo .= '</ul>';

                                $tableData .= '<td>' . $postInfo . '</td>';
                            } else $tableData .= '<td><span>' .
                                __('No terms found', 'oes') . '</span></td>';
                        }
                    }

                $pageContent .= '<tr>' . $tableDataHeader . '</tr><tr>' . $tableData . '</tr></table>';
            }

            return $pageContent;
        }


        /**
         * Display the container tabs (is called in subpages)
         */
        function html_tabs(): string
        {
            /* get current tab */
            $activeTab = false;
            if (isset($_GET['post_type'])) $activeTab = $_GET['post_type'];
            elseif (isset($_GET['taxonomy'])) $activeTab = $_GET['taxonomy'];

            $tabString = '<h2 class="nav-tab-wrapper">';

            /* add info tab */
            if (!empty($this->info_page['elements']))
                $tabString .= oes_get_html_anchor(
                    $this->info_page['label'] ?? 'Title missing',
                    'admin.php?page=' . $this->page_parameters['menu_slug'],
                    false,
                    ($activeTab ? '' : 'nav-tab-active ') . 'nav-tab'
                );

            foreach ($this->subpage_data as $key => $page)
                $tabString .= oes_get_html_anchor(
                    $page['label'] ?? $key,
                    $page['href'] ?? '#',
                    false,
                    ($activeTab == $key ? 'nav-tab-active ' : '') . 'nav-tab'
                );
            $tabString .= '</h2>';

            return $tabString;
        }


        /**
         * Filter Call: Modify admin view for post type that are inside the container.
         */
        function view_for_container_elements($array)
        {
            echo $this->html_tabs();
            return $array;
        }


        /**
         * Filter Call: Modify parent file for post types and taxonomies in admin menu.
         */
        function modify_parent_file($parent_file)
        {
            /* get current screen */
            global $current_screen;
            if (in_array($current_screen->base, ['post', 'edit', 'edit-tags'])
                && (isset($this->subpage_data[$current_screen->post_type]) ||
                    isset($this->subpage_data[$current_screen->taxonomy])))
                $parent_file = $this->page_parameters['menu_slug'];
            return $parent_file;
        }
    }
endif;