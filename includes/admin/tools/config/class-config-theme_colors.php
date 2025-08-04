<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Theme_Colors')) :

    /**
     * Class Theme_Colors
     *
     * Implement the config tool for theme configurations: colors.
     */
    class Theme_Colors extends Config
    {

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            $currentColors = [];
            if ($colorJson = get_option('oes_theme-colors')) {
                $currentColors = json_decode($colorJson, true);
            }

            /* merge with defaults */
            $defaults = [
                '--oes-text-black' => [
                    'default' => '#2a3130',
                    'title' => __('Text color', 'oes')
                ],
                '--oes-primary' => [
                    'default' => '#004589',
                    'title' => __('Primary color', 'oes')
                ],
                '--oes-primary-contrast' => [
                    'default' => '#fff',
                    'title' => __('Contrast for primary color', 'oes')
                ],
                '--oes-link' => [
                    'default' => '#337ab7',
                    'title' => __('Link color', 'oes')
                ],
                '--oes-contrast' => [
                    'default' => '#b22222',
                    'title' => __('Contrast color', 'oes')
                ],
                '--oes-background' => [
                    'default' => '#e8e8e8',
                    'title' => __('Background color', 'oes')
                ],
                '--oes-background-second' => [
                    'default' => '#e8e8e8',
                    'title' => __('Secondary background color', 'oes')
                ],
                '--oes-dark' => [
                    'default' => '#bcbcbc',
                    'title' => __('Dark color', 'oes')
                ],
                '--oes-darker' => [
                    'default' => '#999999',
                    'title' => __('Darker color', 'oes')
                ],
                '--oes-page-color' => [
                    'default' => 'transparent',
                    'title' => __('Page color (Background)', 'oes')
                ]
            ];


            /**
             * Filters the color data for the OES theme.
             *
             * @param array $defaults The color for the OES theme.
             */
            $defaults = apply_filters('oes/theme_colors', $defaults);


            foreach ($defaults as $color => $colorData) {

                $currentColor = $currentColors[$color] ?? ($colorData['default'] ?? '');
                $title = '<span style="padding-right:5px; margin-right: 5px; background-color: ' . $currentColor. '"></span>' .
                    '<strong>' . ($colorData['title'] ?? '') . '</strong>' .
                    '<code class="oes-object-identifier">' . $color . '</code>';

                $this->add_table_row(
                    [
                        'title' => $title,
                        'key' => 'oes_theme-colors[' . $color . ']',
                        'value' => $currentColor
                    ]
                );
            }
        }

        /** @inheritdoc */
        function admin_post_tool_action(): void
        {
            $prepareData = $_POST['oes_theme-colors'] ?? [];

            if (!oes_option_exists('oes_theme-colors')) {
                add_option('oes_theme-colors', json_encode($prepareData));
            }
            else {
                update_option('oes_theme-colors', json_encode($prepareData));
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Colors', 'theme-colors');
endif;