<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Theme_Labels')) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists('Theme_Labels_General')) :

    /**
     * Class Theme_Labels_General
     *
     * Implement the config tool for theme configurations.
     */
    class Theme_Labels_General extends Theme_Labels
    {

        //Overwrite parent
        function set_table_data_for_display()
        {

            /* get global OES instance */
            $oes = OES();
            $languages = array_keys($oes->languages);
            $this->set_language_row();


            $rows = [];
            if (!empty($oes->theme_labels)) {
                $storeForSorting = $oes->theme_labels;
                ksort($storeForSorting);
                foreach ($storeForSorting as $key => $label)
                    if (!$oes->block_theme || (!isset($label['classic']) || !$label['classic'])) {

                        $cells = [[
                            'type' => 'th',
                            'value' => '<div><strong>' . $label['name'] . '</strong></div><div><em>' .
                                __('Location: ', 'oes') . '</em>' . ($label['location'] ?? '') .
                                '</div>' .
                                '<div><code class="oes-object-identifier-br">' . $key . '</code></div>'
                        ]];

                        foreach ($languages as $language)
                            $cells[] = [
                                'class' => 'oes-table-transposed',
                                'value' => oes_html_get_form_element('text',
                                    'oes_config[theme_labels][' . $key . '][' . $language . ']',
                                    'oes_config-theme_labels-' . $key . '-' . $language,
                                    $label[$language] ?? '')
                            ];

                        $rows[]['cells'] = $cells;
                    }
            }
            $this->table_data[]['rows'] = array_merge($this->language_row, $rows);
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels_General', 'theme-labels-general');
endif;