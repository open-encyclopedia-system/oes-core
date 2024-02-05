<?php

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

        /** @var array|string[] Default color scheme */
        public array $default_colors = [
            '--oes-text-black' => '#2a3130',
            '--oes-primary' => '#004589',
            '--oes-primary-contrast' => '#fff',
            '--oes-link' => '#337ab7',
            '--oes-contrast' => '#b22222',
            '--oes-background' => '#e8e8e8',
            '--oes-background-second' => '#e8e8e8',
            '--oes-dark' => '#bcbcbc',
            '--oes-darker' => '#999999'
        ];


        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Colors</b> allows you to overwrite colors for the OES theme. The colors must be in HEX format.', 'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $currentColors = [];
            if ($colorJson = get_option('oes_theme-colors')) $currentColors = json_decode($colorJson, true);


            /* merge with defaults */
            $colorDataArray = [
                '--oes-text-black' => [
                    'color' => $currentColors['--oes-text-black'] ?: $this->default_colors['--oes-text-black'],
                    'title' => __('Text color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-primary' => [
                    'color' => $currentColors['--oes-primary'] ?: $this->default_colors['--oes-primary'],
                    'title' => __('Primary color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-primary-contrast' => [
                    'color' => $currentColors['--oes-primary-contrast'] ?: $this->default_colors['--oes-primary-contrast'],
                    'title' => __('Contrast for primary color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-link' => [
                    'color' => $currentColors['--oes-link'] ?: $this->default_colors['--oes-link'],
                    'title' => __('Link color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-contrast' => [
                    'color' => $currentColors['--oes-contrast'] ?: $this->default_colors['--oes-contrast'],
                    'title' => __('Contrast color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-background' => [
                    'color' => $currentColors['--oes-background'] ?: $this->default_colors['--oes-background'],
                    'title' => __('Background color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-background-second' => [
                    'color' => $currentColors['--oes-background-second'] ?: $this->default_colors['--oes-background-second'],
                    'title' => __('Secondary background color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-dark' => [
                    'color' => $currentColors['--oes-dark'] ?: $this->default_colors['--oes-dark'],
                    'title' => __('Dark color', 'oes'),
                    'additional-info' => ''
                ],
                '--oes-darker' => [
                    'color' => $currentColors['--oes-darker'] ?: $this->default_colors['--oes-darker'],
                    'title' => __('Darker color', 'oes'),
                    'additional-info' => ''
                ],

                '--oes-page-color' => [
                    'color' => $currentColors['--oes-page-color'] ?: $this->default_colors['--oes-page-color'],
                    'title' => __('Page color (Background)', 'oes'),
                    'additional-info' => ''
                ]
            ];


            /**
             * Filters the color data for the OES theme.
             *
             * @param array $colorDataArray The color for the OES theme.
             */
            $colorDataArray = apply_filters('oes/theme_colors', $colorDataArray);


            $rows = [];
            foreach ($colorDataArray as $color => $colorData)
                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' =>
                                '<span class="oes-p-5px"  style="background-color: ' . $colorData['color'] . '"></span>' .
                                '<strong>' . ($colorData['title'] ?? '') . '</strong>' .
                                '<code class="oes-object-identifier">' . $color . '</code>' .
                                (empty($colorData['additional-info'] ?? '') ?
                                    '' :
                                    '<div>' . $colorData['additional-info'] . '</div>')
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' =>
                                oes_html_get_form_element('text',
                                    'oes_theme-colors[' . $color . ']',
                                    'oes_theme-colors_' . $color,
                                    $colorData['color'] ?? ''
                                )
                        ]
                    ]
                ];

            $this->table_data = [['rows' => $rows]];
        }


        //Implement parent
        function admin_post_tool_action(): void
        {
            /* validate input */
            $prepareData = $_POST['oes_theme-colors'] ?? [];
            if (!empty($prepareData))
                foreach ($prepareData as $key => $color)
                    if (empty($color)) $prepareData[$key] = $this->default_colors[$key] ?? 'transparent';

            /* update option */
            if (!oes_option_exists('oes_theme-colors')) add_option('oes_theme-colors', json_encode($prepareData));
            else update_option('oes_theme-colors', json_encode($prepareData));
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Colors', 'theme-colors');
endif;