<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Page')) :

    /**
     * Class Page
     *
     * Manages admin menu pages in the editorial layer for settings and tools.
     */
    class Page
    {
        /** @var array<string, mixed> Parameters used to configure the page. */
        protected array $page_parameters = [];

        /** @var string Filename of the view template. */
        protected string $view_file_name = '';

        /** @var string Full path to the view template file. */
        protected string $view_file_name_full_path = '';

        /** @var bool Whether the page is a core page. */
        protected bool $is_core_page = false;

        /** @var string The page hook. */
        protected string $page_hook = '';

        /** @var string Menu separator position: 'before' or 'after'. */
        protected string $separator = '';

        /** @var string Tool identifier used for rendering specific tool content. */
        protected string $tool = '';

        /** @var array<string, string> Navigation tabs: key => label. */
        protected array $tabs = [];

        /**
         * Page constructor.
         *
         * @param array<string, mixed> $args Optional parameters for initializing the page.
         */
        public function __construct(array $args = [])
        {
            $this->set_parameters($args);
            $this->validate_parameters();
            $this->set_additional_parameters();
            $this->prepare_actions();
        }

        /**
         * Prepare actions
         * @return void
         */
        protected function prepare_actions(): void
        {
            add_action('admin_menu', [$this, 'admin_menu']);
        }

        /**
         * Set parameters for the page instance.
         *
         * @param array<string, mixed> $args Parameters to apply.
         */
        public function set_parameters(array $args = []): void
        {
            foreach ($args as $key => $value) {
                if (property_exists($this, $key)) {
                    if ($key !== 'page_parameters' && gettype($this->$key) === gettype($value)) {
                        $this->$key = $value;
                    }
                }
            }

            if(!isset($args['separator'])) {
                $this->set_page_parameters($args['page_parameters'] ?? []);
            }
            else {
                $this->separator = 'before';
                $this->page_parameters['position'] = $args['position'] ?? 80.1;
            }
        }

        /**
         * Override to validate parameters.
         */
        protected function validate_parameters(): void
        {
        }

        /**
         * Set and validate default page parameters.
         *
         * @param array<string, mixed> $args Additional page parameters.
         */
        public function set_page_parameters(array $args = []): void
        {
            $defaults = $this->get_page_parameters_defaults();

            $this->page_parameters = array_merge($defaults, $args);

            if (!isset($this->page_parameters['page_title'])) {
                $this->page_parameters['page_title'] = $this->page_parameters['menu_title'];
            }

            $this->set_icon_path($args['icon_url'] ?? 'default');
        }

        /**
         * Get page parameters defaults.
         * @return array
         */
        protected function get_page_parameters_defaults(): array
        {
            return [
                'menu_title' => 'Menu Title',
                'capability' => 'edit_posts',
                'function' => [$this, 'html'],
                'position' => 80.1
            ];
        }

        /**
         * Set the icon path.
         * @param $icon
         * @return void
         */
        protected function set_icon_path($icon): void
        {
            $this->page_parameters['icon_url'] = oes_get_menu_icon_path($icon);
        }

        /**
         * Validates and optionally calculates a new menu position.
         *
         * @param string|float|bool $position Menu position or 'compute' for automatic placement.
         */
        public function validate_page_position($position): void
        {
            global $menu;
            if ($position === 'compute' || isset($menu[(string)$position])) {

                $newPosition = $position === 'compute' ? 0.0 : $position;
                while (isset($menu["$newPosition"]) && $newPosition <= 100.1) {
                    $newPosition += 0.2;
                }
                $position = (string)$newPosition;
            }

            $this->page_parameters['position'] = $position;
        }

        /**
         * Override to add more setup after setting parameters.
         */
        protected function set_additional_parameters(): void
        {
        }

        /**
         * Registers the admin menu page.
         */
        public function admin_menu(): void
        {
            $this->validate_page_position($this->page_parameters['position'] ?? 'compute');

            if (!empty($this->separator)) {
                $this->add_separator();
                return;
            }

            $this->additional_admin_menu();
            $this->add_page();
        }

        /**
         * Override to add more to admin menu.
         */
        public function additional_admin_menu(): void
        {

        }

        /**
         * Adds a separator in the admin menu.
         */
        protected function add_separator(): void
        {
            global $menu;

            $position = $this->page_parameters['position'] + ($this->separator === 'before' ? 0.1 : -0.1);

            while (isset($menu["$position"]) && $position <= 100.1) {
                $position += ($this->separator === 'before' ? 0.1 : -0.1);
            }

            $index = 0;
            foreach ($menu as $offset => $section) {
                if (str_starts_with($section[2], 'separator')) {
                    $index++;
                }

                if ($offset >= $position) {
                    $menu[(int)$position] = ['', 'read', "separator$index", '', 'wp-menu-separator'];
                    break;
                }
            }

            ksort($menu);
        }

        /**
         * Adds the admin page to WordPress.
         *
         * @return string|false Slug of the added page or false on failure.
         */
        protected function add_page()
        {
            $this->page_hook = add_menu_page(
                $this->page_parameters['page_title'],
                $this->page_parameters['menu_title'],
                $this->page_parameters['capability'],
                $this->page_parameters['menu_slug'],
                $this->page_parameters['function'],
                $this->page_parameters['icon_url'],
                $this->page_parameters['position']
            );
            return $this->page_hook;
        }

        /**
         * Renders the page content.
         */
        public function html(): void
        {
            if (!empty($this->view_file_name_full_path) && file_exists($this->view_file_name_full_path)) {
                include($this->view_file_name_full_path);
            } elseif (!empty($this->view_file_name)) {
                $this->is_core_page
                    ? oes_get_view($this->view_file_name)
                    : oes_get_application_view($this->view_file_name);
            } else {

                $this->check_for_tool();

                if (!empty($this->tool)) {
                    $this->tool_html();
                } else {
                    $this->default_html();
                }
            }
        }

        /**
         * Checks for a selected tool based on the current tab or default.
         */
        protected function check_for_tool(): void
        {
            if (!empty($this->tool)) {
                return;
            }

            $tab = $_GET['tab'] ?? null;
            $tool = $tab ?? array_key_first($this->tabs);

            global $oes;

            if ($tool && isset($oes->admin_tools[$tool])) {
                $this->tool = $tool;
            }
        }

        /**
         * Renders the tool content with navigation.
         */
        protected function tool_html(): void
        {
            ?>
            <div class="wrap">
                <div class="oes-page-header-wrapper">
                    <h1><?php echo esc_html($this->page_parameters['page_title']); ?></h1>
                    <h2 class="oes-display-none"></h2>
                    <div class="oes-page-navigation"><?php $this->nav_html(); ?></div>
                </div>
                <div class="oes-page-body" style="clear:both">
                    <?php \OES\Admin\Tools\display($this->tool); ?>
                </div>
            </div>
            <?php
        }

        /**
         * Renders the navigation tabs if defined.
         */
        public function nav_html(): void
        {
            if (empty($this->tabs)) return;

            $menuSlug = $this->page_parameters['menu_slug'] ?? 'oes_settings';
            $urlBase = admin_url('admin.php?page=' . urlencode($menuSlug) . '&tab=');
            $activeTab = $_GET['tab'] ?? $this->tool;

            //Todo remove oes-tabs-wrapper?
            echo '<ul class="subsubsub">';

            foreach ($this->tabs as $tab => $label) {
                $isActive = ($activeTab === $tab);
                $classes = 'oes-tab' . ($isActive ? ' current' : '');
                echo sprintf(
                    '<li class="%s"><a href="%s" class="%s">%s</a></li>',
                    esc_html($tab),
                    esc_url($urlBase . urlencode($tab)),
                    esc_attr($classes),
                    esc_html($label)
                );
            }

            echo '</ul>';
            echo '<div style="clear: both;"></div>';
            echo '<hr>';
        }

        /**
         * TODO method until OES 2.4.3
         * Renders the navigation tabs if defined.
         */
        public function nav_html_2_4_3(): void
        {
            if (empty($this->tabs)) return;

            $menuSlug = $this->page_parameters['menu_slug'] ?? 'oes_settings';
            $urlBase = admin_url('admin.php?page=' . urlencode($menuSlug) . '&tab=');
            $activeTab = $_GET['tab'] ?? $this->tool;

            echo '<nav class="oes-tabs-wrapper hide-if-no-js tab-count-' . esc_attr(count($this->tabs)) . '" aria-label="Secondary menu">';

            foreach ($this->tabs as $tab => $label) {
                $isActive = ($activeTab === $tab);
                $classes = 'oes-tab' . ($isActive ? ' active' : '');
                echo sprintf(
                    '<a href="%s" class="%s">%s</a>',
                    esc_url($urlBase . urlencode($tab)),
                    esc_attr($classes),
                    esc_html($label)
                );
            }

            echo '</nav>';
        }

        /**
         * Override to output default HTML content for the page.
         */
        protected function default_html(): void
        {
        }
    }
endif;
