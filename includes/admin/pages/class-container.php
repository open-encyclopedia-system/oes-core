<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\oes_get_field_object;
use function OES\ACF\get_field_display_value;

if (!class_exists('Container')) :

    /**
     * Class Container
     *
     * Create container page for multiple post types or taxonomies admin pages.
     */
    class Container extends Page
    {

        //Overwrite
        protected float $min_position = 25;

        /** @var string The page menu title. */
        protected string $menu_title = '';

        /** @var array The subpage information. */
        protected array $sub_pages = [];

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
        public function __construct(string $key, array $containerArgs = [])
        {
            /* prepare parameters */
            $args = $containerArgs['page_args'] ?? [];
            $pageTitle = $args['page_title'] ?? 'container_' . $key;
            $this->menu_title = $args['menu_title'] ?? $pageTitle;
            $mainSlug = $args['main_slug'] ?? $pageTitle;

            /* set parameters */
            $this->page_parameters = wp_parse_args($args, [
                'page_title' => $pageTitle,
                'menu_title' => $this->menu_title,
                'position' => 'compute',
                'icon_url' => plugins_url(OES()->basename . '/assets/images/oes_cubic_18x18_parent.png')
            ]);

            /* only register container if sub-pages exists */
            $subPages = $containerArgs['sub_pages'] ?? [];

            /* check if info page included per default */
            $infoPageElements = $containerArgs['info_page']['elements'] ?? false;

            /* set info page callback */
            $infoPageCallback = $containerArgs['info_page']['callback'] ??
                ($infoPageElements ? [$this, 'html'] : false);

            /* prepare main slug */
            if (!$infoPageCallback && !empty($subPages)) {

                /* if no info page is included, modify main slug to first subpage */
                $firstKey = array_key_first($subPages);
                $objectKey = is_string($subPages[$firstKey]) ? $subPages[$firstKey] : $firstKey;

                if (get_post_type_object($objectKey)) $mainSlug = 'edit.php?post_type=' . $objectKey;
                elseif (get_taxonomy($objectKey)) $mainSlug = 'edit-tags.php?taxonomy=' . $objectKey;
            } elseif (empty($mainSlug)) {
                $mainSlugTemp = str_replace(' ', '_', $pageTitle);
                $mainSlugTemp = preg_replace('/[^A-Za-z0-9_]/', '', strtolower($mainSlugTemp));
                $mainSlug = strval($mainSlugTemp);
            }
            $this->main_slug = $mainSlug;

            /* call parent */
            parent::__construct();

            /* check for get_post args */
            if (isset($containerArgs['get_posts_args']))
                $this->get_posts_defaults = wp_parse_args($containerArgs['get_posts_args'], $this->get_posts_defaults);

            /* check for get_post taxonomies */
            if (isset($containerArgs['get_terms_args']))
                $this->get_terms_defaults = wp_parse_args($containerArgs['get_terms_args'], $this->get_terms_defaults);

            /* create info sub-page */
            if ($infoPageCallback) {

                $label = $containerArgs['info_page']['label'] ?? __('Recently worked on');
                new Page([
                    'sub_page' => true,
                    'page_parameters' => [
                        'parent_slug' => $this->main_slug,
                        'menu_title' => $label,
                        'capability' => 'edit_posts',
                        'menu_slug' => $this->main_slug,
                        'function' => $infoPageCallback,
                        'position' => 1
                    ]]);

                $this->sub_pages[$this->main_slug] = [
                    'label' => $label,
                    'href' => 'admin.php?page=' . $this->main_slug
                ];
            }


            /* create sub-pages */
            $position = 1;
            foreach ($subPages as $key => $page) {

                $label = $page['label'] ?? $key;
                $href = 'admin.php?page=' . $this->main_slug;
                $pageSlug = $this->main_slug . '_container';
                $capabilities = 'edit_posts';

                /* check if post type or taxonomy */
                $objectName = false;
                if ($postType = get_post_type_object($key)) {
                    $objectName = $postType->name;
                    $label = $postType->label;
                    $href = 'edit.php?post_type=' . $key;
                    $pageSlug = 'edit.php?post_type=' . $objectName;
                } elseif (is_string($page) && $postType = get_post_type_object($page)) {
                    $objectName = $postType->name;
                    $key = $page;
                    $label = $postType->label;
                    $href = 'edit.php?post_type=' . $page;
                    $pageSlug = 'edit.php?post_type=' . $objectName;
                } elseif ($taxonomy = get_taxonomy($key)) {
                    $objectName = $taxonomy->name;
                    $label = $taxonomy->label;
                    $href = 'edit-tags.php?taxonomy=' . $key;
                    $pageSlug = 'edit-tags.php?taxonomy=' . $objectName;
                    $capabilities = 'edit_tags';
                } elseif (is_string($page) && $taxonomy = get_taxonomy($page)) {
                    $objectName = $taxonomy->name;
                    $key = $page;
                    $label = $taxonomy->label;
                    $href = 'edit-tags.php?taxonomy=' . $page;
                    $pageSlug = 'edit-tags.php?taxonomy=' . $objectName;
                    $capabilities = 'edit_tags';
                }

                /* check if element replaces info page */
                if (!$infoPageElements && $position === 1) {

                    /* redirect first sub-menu. @oesDevelopment Is there a better way to do this? */
                    $pageSlug = $this->main_slug;
                    add_action('admin_init', function () use ($pageSlug) {
                        global $plugin_page;
                        if ($plugin_page === $pageSlug) {
                            wp_redirect(admin_url($pageSlug));
                            exit;
                        }
                    });
                }

                /* check if post type multiple times, depending on field */
                $columns = [];
                if (isset($page['field']) && $fieldObject = oes_get_field_object($page['field']))
                    if ($fieldObject['type'] == 'select' && !empty($fieldObject['choices'])) {

                        /* check if filter */
                        if (isset($page['valid_values'])) {
                            foreach ($page['valid_values'] as $value)
                                if (isset($fieldObject['choices'][$value]))
                                    $columns[$value] = $fieldObject['choices'][$value];
                        } else $columns = $fieldObject['choices'];
                    }

                /* add for info tab ----------------------------------------------------------------------------------*/
                $this->sub_pages[$key] = [
                    'label' => $label,
                    'href' => $href,
                    'display_summary' => in_array($key, $infoPageElements ?: []),
                    'columns' => $columns,
                    'field' => $page['field'] ?? false
                ];

                /* create sub pages ----------------------------------------------------------------------------------*/
                $args = [
                    'sub_page' => true,
                    'page_parameters' => [
                        'parent_slug' => $this->main_slug,
                        'page_title' => $label,
                        'menu_title' => $label,
                        'capability' => $capabilities,
                        'menu_slug' => $pageSlug,
                        'function' => '',
                        'position' => ++$position
                    ]];
                new Page($args);

                /* hook tabs to display of sub pages  ----------------------------------------------------------------*/
                if ($objectName)
                    add_filter('views_edit-' . $objectName, [$this, 'view_for_container_elements'], 10, 1);
            }

            /* modify admin menu if necessary (to show active menu)*/
            add_filter('parent_file', [$this, 'modify_parent_file']);

        }


        //Overwrite
        function html(): void
        {

            /**
             * Filters the custom html.
             *
             * @param bool $customHTML Identifying if custom html is rendered.
             * @param string $slug The page slug.
             */
            $customHTML = false;
            if (has_filter('oes/menu_container_custom_html'))
                $customHTML = apply_filters('oes/menu_container_custom_html', $customHTML, $this->main_slug);

            /* end execution if custom html is rendered. */
            if($customHTML) return;


            /**
             * Filters the custom arguments.
             *
             * @param array $args Additional arguments.
             * @param string $slug The page slug.
             */
            $args = [];
            if (has_filter('oes/menu_container_custom_html'))
                $args = apply_filters('oes/menu_container_custom_html', $args, $this->main_slug);


            /* prepare page content for info page */
            $pageContent = $this->get_page_content_html($args);


            /**
             * Filters the page content for admin view.
             *
             * @param array $pageContent The page content.
             * @param string $slug The page slug.
             * @param Container $this The container class.
             */
            if (has_filter('oes/page_container_content'))
                $pageContent = apply_filters('oes/page_container_content', $pageContent, $this->main_slug, $this);

            ?>
            <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $this->menu_title; ?></h1>
            <hr class="wp-header-end"><?php
            echo $this->html_tabs();
            echo $pageContent;
            ?>
            </div><?php
        }


        function get_page_content_html(array $additionalArgs = []): string
        {

            /* prepare page content for info page */
            $pageContent = '<table class="oes-menu-container wp-list-table widefat fixed striped table-view-list">';

            /* prepare data */
            if (!empty($this->sub_pages)) {

                $tableDataHeader = '';
                $tableData = '';


                /**
                 * Filters the subpages
                 *
                 * @param array $subPages The subpages.
                 * @param string $slug The page slug.
                 */
                $subPages = $this->sub_pages;
                if (has_filter('oes/menu_container_subpages'))
                    $subPages = apply_filters('oes/menu_container_subpages', $subPages, $this->main_slug);


                /* loop through post types */
                foreach ($subPages as $pageKey => $page)
                    if (isset($page['display_summary']) && $page['display_summary']) {

                        /* check if modified post type */
                        if(oes_starts_with($pageKey, 'modified_'))
                            $pageKey = substr($pageKey, 9);

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

                                /* set label as header */
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
                                $tableDataHeaderString = '<h3>' . $postType->label .
                                    ($selectValue === 'ignore' ? '' : ' (' . $selectLabel . ')') . '</h3>' . $button;
                                if (has_filter('oes/menu_container_posts_header'))
                                    $tableDataHeaderString = apply_filters('oes/menu_container_posts_header',
                                        $tableDataHeaderString, $pageKey, $selectValue);
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
                                    if (has_filter('oes/menu_container_posts'))
                                        $postArray = apply_filters('oes/menu_container_posts',
                                            $postArray, $postType, $selectValue, $additionalArgs);

                                    /* display posts */
                                    $postInfo = '<ul>';
                                    foreach ($postArray as $postItem) {

                                        /* check if post meets filter */
                                        if ($selectValue !== 'ignore' && isset($page['field']) &&
                                            $post = get_post($postItem['id'])) {
                                            $fieldValue = get_field_display_value($page['field'], $post->ID, ['value-is-link' => false]);
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
                                if (has_filter('oes/menu_container_terms'))
                                    $termArray = apply_filters('oes/menu_container_terms', $termArray, $postType, $pageKey);


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
                                $moreString = oes_get_html_anchor(__('more...', 'oes'),
                                    admin_url('edit-tags.php?taxonomy=' . $taxonomy->name)
                                );
                                if (has_filter('oes/menu_container_terms_more_string'))
                                    $moreString = apply_filters('oes/menu_container_terms_more_string',
                                        $moreString, $taxonomy, $pageKey);

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
            elseif (isset($_GET['page'])) $activeTab = $_GET['page'];

            $tabString = '<h2 class="nav-tab-wrapper">';
            foreach ($this->sub_pages as $key => $page)
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
                && (isset($this->sub_pages[$current_screen->post_type]) ||
                    isset($this->sub_pages[$current_screen->taxonomy])))
                $parent_file = $this->main_slug;
            return $parent_file;
        }
    }
endif;