<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Page')) oes_include('admin/pages/class-page.php');

if (!class_exists('Subpage')) :

    /**
     * Class Subpage
     *
     * Create pages and subpages inside the editorial layer to store information and settings.
     */
    class Subpage extends Page
    {

        // Overwrite parent
        function set_page_parameters(array $args = []): void
        {
            $this->page_parameters = array_merge([
                'parent_slug' => 'oes_settings',
                'page_title' => 'Page Title',
                'menu_title' => 'Menu Title',
                'capability' => 'edit_posts',
                'menu_slug' => 'oes_subpage',
                'function' => [$this, 'html'],
                'position' => 0
            ], $args);
        }


        // Overwrite parent
        function add_page() {
            return add_submenu_page(
                $this->page_parameters['parent_slug'],
                $this->page_parameters['page_title'],
                $this->page_parameters['menu_title'],
                $this->page_parameters['capability'],
                $this->page_parameters['menu_slug'],
                $this->page_parameters['function'],
                $this->page_parameters['position']
            );
        }
    }
endif;