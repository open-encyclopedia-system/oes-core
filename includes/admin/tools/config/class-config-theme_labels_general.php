<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Theme_Labels', false)) oes_include('admin/tools/config/class-config-theme_labels.php');

if (!class_exists(__NAMESPACE__ . '\Theme_Labels_General', false)) {

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
            $storeForSorting = oes_get_theme_labels();
            if (empty($storeForSorting)) {
                return;
            }

            $this->set_language_row();

            ksort($storeForSorting);
            foreach ($storeForSorting as $key => $label) {
                if (!oes_is_block_theme() || (!isset($label['classic']) || !$label['classic'])) {

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

    oes_add_tool(__NAMESPACE__ . '\Theme_Labels_General', 'theme-labels-general');

}
