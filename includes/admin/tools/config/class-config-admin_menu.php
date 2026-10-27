<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists(__NAMESPACE__ . '\Config', false)) oes_include('admin/tools/config/class-config.php');

if (!class_exists(__NAMESPACE__ . '\Admin_Menu', false)) :

    /**
     * Class Admin Menu
     *
     * Implement the config tool for admin menu configurations.
     */
    class Admin_Menu extends Config
    {

        /** @inheritdoc */
        public function set_table_data_for_display(): void
        {
            $this->add_table_row(
                [
                    'title' => __('Include in Menu', 'oes'),
                    'key' => 'oes_config[include_menu]',
                    'value' => oes_get_include_menu(),
                    'type' => 'select',
                    'args' => [
                        'options' => ['comments' => 'Comments', 'posts' => 'Posts'],
                        'multiple' => true,
                        'reorder' => true,
                        'hidden' => true
                    ]
                ]
            );

            $positions = array_map('strval', range(20, 50));
            $this->add_table_row(
                [
                    'title' => __('Separator Positions', 'oes'),
                    'key' => 'oes_config[separators]',
                    'value' => oes_get_separators(),
                    'type' => 'select',
                    'args' => [
                        'options' => array_combine($positions, $positions),
                        'multiple' => true,
                        'reorder' => false,
                        'hidden' => true
                    ]
                ]
            );
        }
    }

    oes_add_tool(__NAMESPACE__ . '\Admin_Menu', 'admin-menu');

endif;
