<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('Admin_Editor')) :

    /**
     * Class Admin_Editor
     *
     * Implement the config tool for admin menu configurations.
     */
    class Admin_Editor extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can modify appearance and behaviour of the edit screen for your custom ' .
                    'post types and custom taxonomies (the edit screen are the website pages inside the WordPress ' .
                    'editorial layer you use to create and modify data).',
                    'oes') .
                '</p><p>' .
                __('The post type parameter <strong>Show in rest</strong> enables the REST API for the ' .
                    'custom post type. This is required for the Gutenberg editor (or block editor). Custom taxonomies ' .
                    'are only available ' .
                    'for a custom post type using the Gutenberg editor if they are available for the REST API as well ' .
                    '(the <strong>Show in rest</strong> parameter is set for the taxonomy). ',
                    'oes') . '<br>' .
                __('If the parameter <strong>Hierarchical</strong> is enabled the custom post or custom ' .
                    'taxonomy can be organized hierarchical (a parent can be defined for the post or term).',
                    'oes') . '<br>' .
                __('The parameter <strong>Supports</strong> enables core features and the associated functional ' .
                    'areas of the edit screen for the custom post type (e.g. the editor).',
                    'oes') . '<br>' .
                __('The taxonomies that are available for a custom post type are defined in the parameter ' .
                    '<strong>Taxonomies</strong>. Note that there might be fields for the custom post type that ' .
                    'make taxonomies available through fields rather than this option.',
                    'oes') .
                '</p><p>' .
                sprintf(__('Find more information %shere%s and %shere%s.',
                    'oes'),
                    '<a href="https://developer.wordpress.org/reference/functions/register_post_type/" target="_blank">',
                    '</a>',
                    '<a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">',
                    '</a>'
                )  .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {

            $oes = OES();

            /* post type header --------------------------------------------------------------------------------------*/
            $this->table_data[] = [
                'type' => 'thead',
                'rows' => [
                    [
                        'class' => 'oes-config-table-separator',
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '',
                                'class' => 'oes-expandable-row-20'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Post Types', 'oes') . '</strong>'
                            ]
                        ]
                    ]
                ]
            ];


            /* post types --------------------------------------------------------------------------------------------*/
            foreach ($oes->post_types as $postTypeKey => $postType) {

                /* get post type object */
                $postTypeObject = get_post_type_object($postTypeKey);

                /* Editorial Layer -----------------------------------------------------------------------------------*/

                /* prepare data */
                $taxonomiesOptions = [];
                if (property_exists($oes, 'taxonomies') && !empty($oes->taxonomies))
                    foreach ($oes->taxonomies as $taxonomyKey => $taxonomy)
                        $taxonomiesOptions[$taxonomyKey] = $taxonomy['label'] ?? $taxonomyKey;

                $supports = [];
                global $_wp_post_type_features;
                foreach ($_wp_post_type_features[$postTypeKey] ?? [] as $support => $ignore) $supports[] = $support;

                $supportsOptions = [
                    'title' => 'title',
                    'editor' => 'editor',
                    'comments' => 'comments',
                    'revisions' => 'revisions',
                    'trackbacks' => 'trackbacks',
                    'author' => 'author',
                    'excerpt' => 'excerpt',
                    'page-attributes' => 'page-attributes',
                    'thumbnail' => 'thumbnail',
                    'custom-fields' => 'custom-fields',
                    'post-formats' => 'post-formats'
                ];

                /* Add to Table --------------------------------------------------------------------------------------*/
                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . ($postType['label'] ?? $postTypeKey) . '</strong>' .
                                        '<code class="oes-object-identifier">' . $postTypeKey . '</code>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => [
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Show in Rest', 'oes') .
                                                        '</strong><div>' . __('Enable block editor (Gutenberg).', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('checkbox',
                                                        'post_types[' . $postTypeKey . '][register_args][show_in_rest]',
                                                        'post_types-' . $postTypeKey . '-register_args-show_in_rest',
                                                        $postTypeObject->show_in_rest ?? false,
                                                        ['hidden' => true])
                                                ]
                                            ]
                                        ],
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Supports', 'oes') .
                                                        '</strong><div>' . __('Core feature(s) the post type supports.', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('select',
                                                        'post_types[' . $postTypeKey . '][register_args][supports]',
                                                        'post_types-' . $postTypeKey . '-register_args-supports',
                                                        $supports ?? [],
                                                        ['options' => $supportsOptions, 'multiple' => true, 'class' => 'oes-replace-select2'])
                                                ]
                                            ]
                                        ],
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Hierarchical', 'oes') .
                                                        '</strong><div>' . __('Posts can have parents or child posts of the same post type.', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('checkbox',
                                                        'post_types[' . $postTypeKey . '][register_args][hierarchical]',
                                                        'post_types-' . $postTypeKey . '-register_args-hierarchical',
                                                        $postTypeObject->hierarchical ?? '',
                                                        ['hidden' => true])
                                                ]
                                            ]
                                        ],
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Taxonomies', 'oes') .
                                                        '</strong><div>' . __('Taxonomies used for this post type.', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('select',
                                                        'post_types[' . $postTypeKey . '][register_args][taxonomies]',
                                                        'post_types-' . $postTypeKey . '-register_args-taxonomies',
                                                        $postTypeObject->taxonomies ?? false,
                                                        ['options' => $taxonomiesOptions, 'multiple' => true, 'class' => 'oes-replace-select2'])
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }

            /* taxonomies header -------------------------------------------------------------------------------------*/
            $this->table_data[] = [
                'type' => 'thead',
                'rows' => [
                    [
                        'class' => 'oes-config-table-separator',
                        'cells' => [
                            [
                                'type' => 'th',
                                'value' => '',
                                'class' => 'oes-expandable-row-20'
                            ],
                            [
                                'type' => 'th',
                                'value' => '<strong>' . __('Taxonomies', 'oes') . '</strong>'
                            ]
                        ]
                    ]
                ]
            ];


            /* taxonomies --------------------------------------------------------------------------------------------*/
            foreach ($oes->taxonomies as $taxonomyKey => $taxonomy) {

                $taxonomyObject = get_taxonomy($taxonomyKey);


                /* Add to Table --------------------------------------------------------------------------------------*/
                $this->table_data[] = [
                    'rows' => [
                        [
                            'type' => 'trigger',
                            'cells' => [
                                [
                                    'value' => '<strong>' . ($taxonomy['label'] ?? $taxonomyKey) . '</strong>' .
                                        '<code class="oes-object-identifier">' . $taxonomyKey . '</code>'
                                ]
                            ]
                        ],
                        [
                            'type' => 'target',
                            'nested_tables' => [
                                [
                                    'rows' => [
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Show in Rest', 'oes') .
                                                        '</strong><div>' . __('Enable block editor (Gutenberg).', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('checkbox',
                                                        'taxonomies[' . $taxonomyKey . '][register_args][show_in_rest]',
                                                        'taxonomies-' . $taxonomyKey . '-register_args-show_in_rest',
                                                        $taxonomyObject->show_in_rest ?? false,
                                                        ['hidden' => true])
                                                ]
                                            ]
                                        ],
                                        [
                                            'cells' => [
                                                [
                                                    'type' => 'th',
                                                    'value' => '<strong>' . __('Hierarchical', 'oes') .
                                                        '</strong><div>' . __('Terms can have parents or child terms of the same taxonomy.', 'oes') . '</div>'
                                                ],
                                                [
                                                    'class' => 'oes-table-transposed',
                                                    'value' => oes_html_get_form_element('checkbox',
                                                        'taxonomies[' . $taxonomyKey . '][register_args][hierarchical]',
                                                        'taxonomies-' . $taxonomyKey . '-register_args-hierarchical',
                                                        $taxonomyObject->hierarchical ?? '',
                                                        ['hidden' => true])
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Admin_Editor', 'admin-editor');

endif;