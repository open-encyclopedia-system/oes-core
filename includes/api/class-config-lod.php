<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config/class-config.php');

if (!class_exists('LOD')) :

    /**
     * Class LOD
     *
     * Implement the config tool for LOD configurations.
     */
    class LOD extends Config
    {

        //Overwrite parent
        function information_html(): string
        {
            return '<div class="oes-tool-information-wrapper"><p>' .
                __('You can use the LOD interface inside a post object to look up LOD data and link your post object ' .
                    'to a LOD entry or copy data to your post object. Enable the LOD option <b>Shortcode</b> to ' .
                    'allow the generation of links to LOD entries inside your post object. The LOD option ' .
                    '<b>Copy To Post</b> allows you to copy norm data to your post object.', 'oes') . '<br>' .
                __('If one of the options is enabled for a post type, the LOD interface will be available for all ' .
                    'post objects of this post type.',
                    'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function set_table_data_for_display()
        {
            $oes = OES();
            $apiOptions = ['shortcode' => 'Shortcode', 'post' => 'Copy to Post'];

            $rows = [];
            foreach ($oes->post_types as $postTypeKey => $postType)
                $rows[] =                     [
                    'cells' => [
                        [
                            'type' => 'th',
                            'value' => '<strong>' . $postType['label'] .
                                '</strong><code class="oes-object-identifier">' . $postTypeKey . '</code>'
                        ],
                        [
                            'class' => 'oes-table-transposed',
                            'value' => oes_html_get_form_element('select',
                                'post_types[' . $postTypeKey . '][oes_args][lod_box]',
                                'post_types-' . $postTypeKey . '-oes_args-lod_box',
                                $postType['lod_box'] ?? [],
                                ['options' => $apiOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                                    'hidden' => true])
                        ]
                    ]
                ];

            $this->table_data = [[
                'rows' => $rows
            ]];
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LOD', 'lod');

endif;