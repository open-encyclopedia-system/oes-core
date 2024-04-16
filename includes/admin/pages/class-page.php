<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Page')) :

    /**
     * Class Page
     *
     * Create pages and subpages inside the editorial layer to store information and settings.
     */
    class Page
    {

        /** @var array The page parameters. */
        protected array $page_parameters = [];

        /** @var string The file name with html representation */
        protected $view_file_name = '';

        /** @var string The full path file name with html representation */
        protected $view_file_name_full_path = '';

        /** @var bool If true, the page is part of the core pages. */
        protected bool $is_core_page = false;

        /** @var string Add separator before or after menu. Valid values are 'before' or 'after'. */
        protected $separator = '';


        /**
         * Page constructor.
         */
        function __construct(array $args = [])
        {
            $this->set_parameters($args);
            $this->set_additional_parameters();
            add_action('admin_menu', [$this, 'admin_menu']);
        }


        /**
         * The function validates parameters.
         * @param array $args Page parameters.
         * @return void
         */
        function set_parameters(array $args = []): void
        {
            foreach ($args as $parameterKey => $parameter)
                if (property_exists($this, $parameterKey)) {
                    if ($parameterKey === 'page_parameters') $this->set_page_parameters($parameter);
                    elseif(gettype($this->$parameterKey) == gettype($parameter)) $this->$parameterKey = $parameter;
                }
        }


        /**
         * Validate and set the page parameters.
         * @param array $args The page parameters.
         * @return void
         */
        function set_page_parameters(array $args = []): void
        {
            $this->page_parameters = array_merge([
                'page_title' => 'Page Title',
                'menu_title' => 'Menu title',
                'capability' => 'edit_posts',
                'function' => [$this, 'html'],
                'icon_url' => plugins_url(OES_BASENAME . '/assets/images/oes_cubic_18x18_second.png'),
                'position' => 80.1
            ], $args);

            if(isset($args['icon_url']) && in_array($args['icon_url'], ['default', 'second', 'parent', 'admin']))
                $this->page_parameters['icon_url'] = oes_get_menu_icon_path($args['icon_url']);
        }


        /**
         * Validate the page position. Calculate position if not set.
         *
         * @param string|float|bool $position The current position.
         * @return void
         */
        function validate_page_position($position): void
        {
            /* check if position needs to be computed */
            if ($position == 'compute') {

                /* get next available position in menu and leave positions for spaces */
                global $menu;
                $newPosition = 0.0;
                while (isset($menu["$newPosition"]) && $newPosition >= 0 && $newPosition <= 100.1)
                    $newPosition = $newPosition + 0.2;

                $position = "$newPosition";
            }
            $this->page_parameters['position'] = $position;
        }


        /**
         * The function sets additional page parameters.
         * @return void
         */
        function set_additional_parameters(): void
        {
        }


        /**
         * Generates the menu page.
         */
        function admin_menu(): void
        {
            /* validate page parameters and add separators if needed */
            $this->validate_page_position($this->page_parameters['position']);
            if ($this->separator) $this->add_separator();

            /* add page and set capabilities */
            if ($this->add_page()) {
                get_role('administrator')->add_cap($this->page_parameters['capability']);
                get_role('editor')->add_cap($this->page_parameters['capability']);
            }
        }


        /**
         * Add separator in menu structure.
         * @return void
         */
        function add_separator(): void
        {
            global $menu;

            /* calculate position */
            $position = $this->page_parameters['position'] + ($this->separator === 'before' ? 0.1 : -0.1);
            while (isset($menu["$position"]) && $position >= 0 && $position <= 100.1)
                $position = $position + ($this->separator === 'before' ? 0.1 : -0.1);

            /* add separator at position */
            $index = 0;
            foreach ($menu as $offset => $section) {
                if (substr($section[2], 0, 9) == 'separator') $index++;
                if ($offset >= $position) {
                    $menu[(int) $position] = ['', 'read', "separator$index", '', 'wp-menu-separator'];
                    break;
                }
            }
            ksort($menu);
        }


        /**
         * Add admin page.
         * @return string|bool Return false on error.
         */
        function add_page()
        {
            return add_menu_page(
                $this->page_parameters['page_title'],
                $this->page_parameters['menu_title'],
                $this->page_parameters['capability'],
                $this->page_parameters['menu_slug'],
                $this->page_parameters['function'],
                $this->page_parameters['icon_url'],
                $this->page_parameters['position']
            );
        }


        /**
         * Display html representation of page.
         * @return void
         */
        function html(): void
        {
            if (!empty($this->view_file_name_full_path) || !empty($this->view_file_name)) :?>
                <div class="oes-page-wrap">
                <!-- dummy for admin notices -->
                <h2 class="oes-display-none"></h2><?php

                if(!empty($this->view_file_name_full_path)){
                    if (file_exists($this->view_file_name_full_path)) include($this->view_file_name_full_path);
                }
                else{
                    $this->is_core_page ?
                        oes_get_view($this->view_file_name) :
                        oes_get_project_view($this->view_file_name);
                }
                ?>
                </div><?php
            endif;
        }
    }
endif;