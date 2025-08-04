<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Admin_Container')) :

    /**
     * Class Admin_Container
     *
     * Implement the config tool for admin menu configurations.
     */
    class Admin_Container extends Config
    {

        /** @inheritdoc */
        public bool $empty_allowed = true;

        /** @inheritdoc */
        public bool $empty_input = true;

        /** @inheritdoc */
        function additional_html(): string
        {
            return '<div class="oes-button-wrapper">' .
                '<input type="submit" name="add_new_container" id="add_new_container" ' .
                'class="button button-secondary button-large" ' .
                'value="' . __('Add New Container', 'oes') . '">' .
                '</div>';
        }

        /** @inheritdoc */
        function set_table_data_for_display()
        {
            global $oes;

            /* loop through container */
            foreach ($oes->admin_pages['container'] ?? [] as $containerKey => $container) {

                $containerName = $container['page_parameters']['menu_title'] ?? 'Recently worked on';

                /* prepare elements */
                $objectOptions = ['none' => '-'];
                foreach ($oes->post_types as $key => $postType) $objectOptions[$key] = $postType['label'];
                foreach ($oes->taxonomies as $key => $taxonomy) $objectOptions[$key] = $taxonomy['label'];

                $prefix = 'oes_config[container][' . $containerKey . ']';

                $this->add_table_header($containerName, 'trigger');

                $this->add_table_row(
                    [
                        'title' => __('Name', 'oes'),
                        'key'   => $prefix . '[page_parameters][menu_title]',
                        'value' => $containerName
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Position', 'oes'),
                        'key'   => $prefix . '[page_parameters][position]',
                        'value' => $container['page_parameters']['position'] ?? 20,
                        'type'  => 'number',
                        'args' => [
                            'min' => 1,
                            'max' => 101
                        ]
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Icon', 'oes'),
                        'key'   => $prefix . '[page_parameters][icon_url]',
                        'value' => $container['page_parameters']['icon_url'] ?? 'default'
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Elements', 'oes'),
                        'key'   => $prefix . '[page_parameters][subpages]',
                        'value' => $container['page_parameters']['subpages'] ?? [],
                        'type' => 'select',
                        'args' => [
                            'options' => $objectOptions,
                            'multiple' => true,
                            'class' => 'oes-replace-select2',
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Info Page Elements', 'oes'),
                        'key'   => $prefix . '[info_page][elements]',
                        'value' => $container['info_page']['elements'] ?? [],
                        'type' => 'select',
                        'args'  => [
                            'options' => $objectOptions,
                            'multiple' => true,
                            'class' => 'oes-replace-select2',
                            'reorder' => true,
                            'hidden' => true
                        ]
                    ]
                );

                $this->add_table_row(
                    [
                        'title' => __('Info Page Text', 'oes'),
                        'key'   => $prefix . '[info_page][label]',
                        'value' => $container['info_page']['label'] ?? 'Title missing'
                    ]
                );


                if (($container['generated'] ?? false) || ($container['hide'] ?? false)) {
                    $this->add_table_row(
                        [
                            'title' => __('Hide', 'oes'),
                            'key'   => $prefix . '[hide]',
                            'value' => $container['hide'] ?? false,
                            'type' => 'checkbox',
                            'args'  => [
                                'hidden' => true
                            ]
                        ]
                    );
                }
                else {
                    $value = '<a href="javascript:void(0);" ' .
                        'onClick="oesConfigTableDeleteRow(this)" ' .
                        'class="oes-highlighted button">' .
                        __('Delete This Container', 'oes') .
                        '</a>';
                    $this->add_cell($value);
                }
            }
        }

        /** @inheritdoc */
        function get_modified_post_data(array $data): array
        {
            if ($data['add_new_container'] ?? false) {
                $data['oes_config']['container'][] = [
                    'page_parameters' => [
                        'menu_title' => 'New Container Title',
                        'position' => 20,
                        'icon_url' => 'default'
                    ],
                    'subpages' => [],
                    'info_page' => [
                        'elements' => [],
                        'label' => 'Recently worked on'
                    ]
                ];
            }
            elseif ($data['oes_hidden'] && (empty($data['oes_config']['container']))) {
                $data['oes_config']['container'] = [];
            }


            /* replace hidden "hide" param */
            foreach ($data['oes_config']['container'] as $key => $container) {
                if (isset($container['hide'])) {
                    $data['oes_config']['container'][$key]['hide'] = (
                    $container['hide'] == 'hidden' ?
                        false :
                        $container['hide']);
                }
            }

            return $data;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Container', 'admin-container');

endif;