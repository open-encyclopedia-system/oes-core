<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Page')) :

    /**
     * Class Page
     *
     * Create pages and subpages inside the editorial layer to store information and settings.
     * This class calls the WordPress functions add_menu_page and add_submenu_page.
     */
    class Page
    {

        /** @var array The page parameters.
         * Further information for valid parameters see
         * https://developer.wordpress.org/reference/functions/add_menu_page/
         * and
         * https://developer.wordpress.org/reference/functions/add_submenu_page/
         */
        protected array $page_parameters = [];

        /** @var string The page hook. */
        protected string $page_hook = '';

        /** @var bool Boolean identifying the page as subpage. If true, the page is a subpage. */
        protected bool $sub_page = false;

        /** @var string String containing the main slug for the page. Default is the option page main slug. */
        protected string $main_slug = 'oes_settings';

        /** @var float minimum position for pages */
        protected float $min_position = 0.0;

        /** @var string|bool The file name with html representation */
        protected $view_file_name = false;

        /** @var bool If true, the page contains postboxes, include the postbox scripts (only necessary if no admin page). */
        protected bool $include_postbox = false;

        /** @var bool|string Add separator before or after menu. Valid values are 'before' or 'after'. */
        protected $separator = false;

        /** @var array Register options for this page. */
        protected array $settings = [];


        /**
         * OES_Includes_Admin_Page constructor.
         */
        function __construct($args = [])
        {
            /* get page parameters from args */
            foreach ($args as $parameterKey => $parameter)
                if (property_exists($this, $parameterKey)) $this->$parameterKey = $parameter;
            $this->validate_parameters();

            add_action('admin_menu', [$this, 'admin_menu']);
        }


        /**
         * The function validates the page parameters and sets the page parameters class variable.
         */
        protected function validate_parameters()
        {
            /* default position */
            $position = '80.1';

            if ($this->sub_page)
                $param = wp_parse_args($this->page_parameters, [
                    'parent_slug' => $this->main_slug,
                    'page_title' => 'Page Title',
                    'menu_title' => 'Menu Title',
                    'capability' => 'edit_posts',
                    'menu_slug' => $this->main_slug . '_subpage',
                    'function' => [$this, 'html'],
                    'position' => 0
                ]);
            else
                $param = wp_parse_args($this->page_parameters, [
                    'page_title' => 'Page Title',
                    'menu_title' => 'Menu title',
                    'capability' => 'edit_posts',
                    'menu_slug' => $this->main_slug,
                    'function' => [$this, 'html'],
                    'icon_url' => plugins_url(OES()->basename . '/assets/images/oes_cubic_18x18_second.png'),
                    'position' => "$position"
                ]);

            /* check for icon_url */
            if (isset($param['icon_url']) && in_array($param['icon_url'], ['default', 'second', 'parent', 'admin']))
                $param['icon_url'] = oes_get_menu_icon_path($param['icon_url']);

            $this->page_parameters = $param;
        }


        /**
         * The function generates a menu page and hooks the load function.
         *
         * @return string Returns the generated page.
         */
        function admin_menu(): string
        {
            /* check if position needs to be computed */
            if ($this->page_parameters['position'] == 'compute') {

                /* check if min position is set by class */
                if ($this->min_position) {

                    /* get next available position in menu and leave positions for spaces */
                    global $menu;
                    $position = $this->min_position;
                    while (isset($menu["$position"]) && $position >= 0 && $position <= 100.1)
                        $position = $position + 0.2;

                    $this->page_parameters['position'] = "$position";
                } else $this->page_parameters['position'] = 0;
            }

            /* check if separator */
            if ($this->separator) {

                global $menu;
                $position = $this->page_parameters['position'] + ($this->separator === 'before' ? 0.1 : -0.1);
                while (isset($menu["$position"]) && $position >= 0 && $position <= 100.1)
                    $position = $position + ($this->separator === 'before' ? 0.1 : -0.1);


                $index = 0;
                foreach ($menu as $offset => $section) {
                    if (substr($section[2], 0, 9) == 'separator') $index++;
                    if ($offset >= $position) {
                        $menu[$position] = ['', 'read', "separator$index", '', 'wp-menu-separator'];
                        break;
                    }
                }
                ksort($menu);

            }

            /* add page */
            if ($this->sub_page) $page = add_submenu_page(
                $this->page_parameters['parent_slug'],
                $this->page_parameters['page_title'],
                $this->page_parameters['menu_title'],
                $this->page_parameters['capability'],
                $this->page_parameters['menu_slug'],
                $this->page_parameters['function'],
                $this->page_parameters['position']
            );
            else $page = add_menu_page(
                $this->page_parameters['page_title'],
                $this->page_parameters['menu_title'],
                $this->page_parameters['capability'],
                $this->page_parameters['menu_slug'],
                $this->page_parameters['function'],
                $this->page_parameters['icon_url'],
                $this->page_parameters['position']
            );

            /* set page hook */
            $this->page_hook = $page;

            /* add load page action */
            add_action('load-' . $page, [$this, 'load']);

            /* add capabilities to admin and editor */
            get_role('administrator')->add_cap($this->page_parameters['capability']);
            get_role('editor')->add_cap($this->page_parameters['capability']);

            return $page;
        }


        /**
         * Callback function for the generated page. Supplies the html for the generated page.
         */
        function html()
        {
            if ($this->view_file_name) {
                ?><div class="wrap">
                <!-- dummy for admin notices -->
                <h2 class="dummy-admin-notices"></h2><?php
                oes_get_view($this->view_file_name);?>
                </div><?php
            }
        }


        /**
         * Runs when generated page is loaded.
         */
        function load()
        {
            /* enqueue scripts for postboxes */
            if ($this->include_postbox) wp_enqueue_script('postbox');
        }

    }


    /* initialize the OES Settings pages */
    add_action('oes/datamodel_registered', 'OES\Admin\initialize_admin_menu_pages');


    /**
     * Initialize the OES Settings pages
     */
    function initialize_admin_menu_pages(){

        $adminMenuPages = [
            'settings' => [
                'page_parameters' => [
                    'page_title' => 'OES Settings',
                    'menu_title' => 'OES Settings',
                    'position' => 55
                ],
                'separator' => 'before'
            ],
            'information' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Information',
                    'menu_title' => 'Information',
                    'menu_slug' => 'oes_settings',
                ],
                'view_file_name' => 'view-settings-information'
            ],
            'datamodel' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Datamodel',
                    'menu_title' => 'Datamodel',
                    'menu_slug' => 'oes_settings_datamodel',
                    'position' => 1
                ],
                'view_file_name' => 'view-settings-datamodel'
            ],
            'writing' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Writing',
                    'menu_title' => 'Writing',
                    'menu_slug' => 'oes_settings_writing',
                    'position' => 2
                ],
                'view_file_name' => 'view-settings-writing'
            ],
            'reading' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Reading',
                    'menu_title' => 'Reading',
                    'menu_slug' => 'oes_settings_reading',
                    'position' => 3
                ],
                'view_file_name' => 'view-settings-reading'
            ],
            'cache' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Cache',
                    'menu_title' => 'Cache',
                    'menu_slug' => 'oes_settings_cache',
                    'position' => 4
                ],
                'view_file_name' => 'view-settings-cache'
            ],
            'lod' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Linked Open Data',
                    'menu_title' => 'Linked Open Data',
                    'menu_slug' => 'oes_settings_lod',
                    'position' => 6
                ],
                'view_file_name' => 'view-settings-lod'
            ],
            /* TODO @nextRelease: in development
            'export' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Export Formats',
                    'menu_title' => 'Export Formats',
                    'menu_slug' => 'oes_settings_export',
                    'position' => 7
                ],
                'view_file_name' => 'view-settings-export'
            ],*/
            'tools' => [
                'sub_page' => true,
                'page_parameters' => [
                    'page_title' => 'Tools',
                    'menu_title' => 'Tools',
                    'menu_slug' => 'oes_tools',
                    'position' => 8
                ],
                'view_file_name' => 'view-tools'
            ]
        ];

        /* add admin page for admin user */
        if (oes_user_is_oes_admin()) $adminMenuPages['admin'] = [
            'sub_page' => true,
            'page_parameters' => [
                'page_title' => 'Admin',
                'menu_title' => 'Admin',
                'menu_slug' => 'oes_admin',
                'position' => 9
            ],
            'view_file_name' => 'view-settings-admin'
        ];


        /**
         * Filters the OES settings pages.
         *
         * @param array $settings The OES settings pages.
         */
        if (has_filter('oes/admin_menu_pages'))
            $adminMenuPages = apply_filters('oes/admin_menu_pages', $adminMenuPages);

        /* initialize pages */
        foreach ($adminMenuPages as $adminMenuPage) new Page($adminMenuPage);
    }

endif;