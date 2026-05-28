<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config', false)) oes_include('admin/tools/config/class-config.php');

if (class_exists('Theme_Languages', false)) exit;

/**
 * Class Theme_Languages
 *
 * Implement the config tool for theme configurations.
 */
class Theme_Languages extends Config
{
    protected string $capability = 'oes_read_settings';

    /** @inheritdoc */
    function set_table_data_for_display()
    {
        foreach (OES()->languages ?? [] as $languageKey => $language) {

            $header = '<code>' . $languageKey . '</code>' .
                ($languageKey === 'language0' ?
                    __(' (Primary Language)', 'oes') :
                    '');
            $this->add_table_header($header, 'tag');

            $options = [
                'label' => __('Name', 'oes'),
                'abb' => __('Abbreviation', 'oes'),
                'locale' => __('Date Locale', 'oes')
            ];

            foreach ($options as $optionKey => $optionLabel) {
                $this->add_table_row(
                    [
                        'title' => $optionLabel,
                        'key' => 'oes_config[languages][' . $languageKey . '][' . $optionKey . ']',
                        'value' => $language[$optionKey] ?? ''
                    ]
                );
            }
        }
    }
}

// initialize
register_tool('\OES\Admin\Tools\Theme_Languages', 'theme-languages');
