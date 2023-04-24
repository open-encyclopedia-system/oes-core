<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Theme_Logos')) :

    /**
     * Class Theme_Logos
     *
     * Implement the config tool for theme configurations: logos.
     */
    class Theme_Logos extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('The OES feature <b>Logo</b> allows you to add logos for the header menu as well as favicons for ' .
                    'your website.' . '<br>' .
                    'The <b>favicon</b> is also known as a shortcut icon, website icon, tab icon, URL icon, or bookmark icon. ' .
                    'Browsers that provide favicon support typically display a page\'s favicon in the browser\'s ' .
                    'address bar (sometimes in the history as well) and next to the page\'s name in a list of bookmarks.',
                    'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            $logoDataArray = [
                'oes_theme-header_logo' => [
                    'title' => __('Header Logo', 'oes'),
                    'description' => __('The logo will be displayed inside your header on the left ' .
                        'side and will redirect to the landing page ("Home Button").', 'oes'),
                    'favicon' => false,
                    'default' => 'oes-home-logo.png'
                ],
                'oes_theme-favicon' => [
                    'title' => __('Favicon', 'oes'),
                    'description' => __('The icon is used as standard icon and can be accessed by older browsers.', 'oes'),
                    'favicon' => true,
                    'default' => 'favicon.ico'
                ],
                'oes_theme-favicon_16_16' => [
                    'title' => __('Favicon (16x16)', 'oes'),
                    'description' => __('The icon is used by most modern browsers.', 'oes'),
                    'favicon' => true,
                    'default' => 'favicon-16x16.ico'
                ],
                'oes_theme-favicon_32_32' => [
                    'title' => __('Favicon (32x32)', 'oes'),
                    'description' => __('The icon is used by Windows (e.g. taskbar).', 'oes'),
                    'favicon' => true,
                    'default' => 'favicon-32x32.ico'
                ],
                'oes_theme-favicon_apple' => [
                    'title' => __('Favicon (apple touch)', 'oes'),
                    'description' => __('The icon is used by iOS.', 'oes'),
                    'favicon' => true,
                    'default' => 'apple-touch-icon.ico'
                ]
            ];


            /**
             * Filters the logos and favicons for the OES theme.
             *
             * @param array $logoDataArray The logos and favicons for the OES theme.
             */
            if (has_filter('oes/theme_logos'))
                $logoDataArray = apply_filters('oes/theme_logos', $logoDataArray);


            $rows = [];
            foreach ($logoDataArray as $optionKey => $logoData)
                $rows[] = [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => sprintf(
                                '<div class="oes-row"><div class="oes-column-75"><strong>%s</strong><div>%s</div></div><div class="oes-column-25">%s</div></div>',
                                $logoData['title'] ?? '',
                                $logoData['description'] ?? '',
                                sprintf('<img class="%s oes-column-image" src="%s" alt="%s">',
                                    ((isset($logoData['favicon']) && $logoData['favicon']) ? 'oes-favicon-preview' : ''),
                                    (empty(get_option($optionKey)) ?
                                        (get_template_directory_uri() . '/assets/images/' . ($logoData['default'] ?? 'favicon.png')) :
                                        get_option($optionKey)),
                                    $logoData['description'] ?? '')
                            )
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('text',
                                $optionKey,
                                $optionKey,
                                get_option($optionKey)
                            )
                        ]
                    ]
                ];

            $this->table_data = [['rows' => $rows]];
        }

        //Implement parent
        function admin_post_tool_action(): void
        {
            /* add or update option */
            foreach ([
                         'oes_theme-header_logo',
                         'oes_theme-favicon',
                         'oes_theme-favicon_16_16',
                         'oes_theme-favicon_32_32',
                         'oes_theme-favicon_apple'
                     ] as $option)
                if (!oes_option_exists($option)) add_option($option, $_POST[$option]);
                else update_option($option, $_POST[$option]);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Logos', 'theme-logos');
endif;