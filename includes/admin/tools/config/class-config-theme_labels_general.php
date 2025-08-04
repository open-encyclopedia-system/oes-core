<?php

/**
 * @file
 * @reviewed 2.4.0
 */

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

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            if (empty($oes->theme_labels)) {
                return;
            }

            $this->set_language_row();

            $storeForSorting = $oes->theme_labels;
            ksort($storeForSorting);
            foreach ($storeForSorting as $key => $label) {
                if (!$oes->block_theme || (!isset($label['classic']) || !$label['classic'])) {

                    $this->add_table_row(
                        [
                            'title' => $label['name'],
                            'key' => 'oes_config[theme_labels][' . $key . ']',
                            'value' => $label ?? [],
                            'is_label' => true,
                            'location' => ($label['location'] ?? ''),
                            'label_key' => $key
                        ]
                    );
                }
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Theme_Labels_General', 'theme-labels-general');
endif;