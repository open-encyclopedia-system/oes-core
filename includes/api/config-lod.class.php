<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

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
                    'allow the generation of links to LOD entries inside your post object, . The LOD option ' .
                    '<b>Copy To Post</b> allows you to copy norm data to your post object.', 'oes') . '<br>' .
                __('If one of the options is enabled for a post type, the LOD interface will be available for all ' .
                    'post objects of this post type.',
                    'oes') .
                '</p></div>';
        }


        //Overwrite parent
        function data_html(): string
        {
            $oes = OES();

            /* api options */
            $apiOptions = ['shortcode' => 'Shortcode', 'post' => 'Copy to Post'];

            /* get data to be displayed */
            $innerTable = '';
            foreach ($oes->post_types as $postTypeKey => $postType)
                $innerTable .= '<tr><th>' . $postType['label'] .
                    '<code class="oes-object-identifier">' . $postTypeKey . '</code></th><td>' .
                    oes_html_get_form_element('select',
                        'post_types[' . $postTypeKey . '][oes_args][lod_box]',
                        'post_types-' . $postTypeKey . '-oes_args-lod_box',
                        $postType['lod_box'] ?? [],
                        ['options' => $apiOptions, 'multiple' => true, 'class' => 'oes-replace-select2',
                            'hidden' => true]) .
                    '</td></tr>';

            /* wrap tables if not empty*/
            $html = '';
            if (!empty($innerTable)) {
                $html = '<table class="oes-option-table oes-config-table oes-replace-select2-inside wp-list-table widefat fixed table-view-list">' .
                    '<thead><tr><th colspan="2"><strong>' .
                    __('LOD Options', 'oes') . '</strong></th></tr></thead><tbody>' . $innerTable . '</tbody></table>';
            }

            return $html;
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\LOD', 'lod');

endif;