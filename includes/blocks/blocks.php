<?php

namespace OES\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Blocks')) {

    /**
     * Class Blocks
     *
     * This class registers OES blocks.
     */
    class Blocks
    {

        /** The default parameters for register_block_type() */
        const DEFAULT_BLOCK_PARAMS = [
            'icon' => [
                'background' => '#52accc',
                'foreground' => '#fff',
                'src' => 'book-alt'
            ],
            'category' => 'oes-blocks',
            'keywords' => ['OES']
        ];


        /**
         * Blocks constructor.
         */
        function __construct()
        {
            /* add OES Category */
            add_filter('block_categories_all', [$this, 'oes_block_category']);

            /* add registration to hook */
            add_action('init', [$this, 'oes_register_block_types']);
        }


        /**
         * Callback for init hook to register block types.
         */
        function oes_register_block_types()
        {

            /* loop through blocks that are stored in the global OES instance */
            $oes = OES();
            if (!empty($oes->blocks['core']))
                foreach ($oes->blocks['core'] as $blockID => $block) {

                    /* define handle */
                    $handle = 'oes-' . $blockID;

                    /* get block parameter */
                    $args = array_merge(self::DEFAULT_BLOCK_PARAMS, $block);

                    /* check if render template or callback exists */
                    if (!isset($args['render_callback']) && !isset($args['render_template']))
                        $args['render_callback'] = 'oes_block_render_' . str_replace('-', '_', $blockID);


                    /* check for script */
                    if (file_exists($oes->path_core_plugin . '/includes/blocks/oes-' . $blockID . '/block.js')) {

                        /* register script */
                        wp_register_script(
                            $handle,
                            plugins_url($oes->basename . '/includes/blocks/oes-' . $blockID . '/block.js'),
                            ['wp-blocks', 'wp-element', 'wp-components', 'wp-editor']
                        );
                        $args['editor_script'] = $handle;
                    }

                    /* check for styles */
                    if (file_exists($oes->path_core_plugin . '/includes/blocks/oes-' . $blockID . '/block.css')) {

                        /* register style */
                        wp_register_style(
                            $handle . '-css',
                            plugins_url($oes->basename . '/includes/blocks/oes-' . $blockID . '/block.css')
                        );
                        $args['style'] = $handle. '-css';
                    }

                    /* check for editor styles */
                    if (file_exists($oes->path_core_plugin . '/includes/blocks/oes-' . $blockID . '/block-editor.css')) {

                        /* register style */
                        wp_register_style(
                            $handle . '-editor-css',
                            plugins_url($oes->basename . '/includes/blocks/oes-' . $blockID . '/block-editor.css')
                        );
                        $args['editor_style'] = $handle . '-editor-css';
                    }

                    /* Register Block --------------------------------------------------------------------------------*/
                    register_block_type('oes/' . $handle, $args);
                }

            /* loop through project blocks */
            if (!empty($oes->blocks['project']))
                foreach ($oes->blocks['project'] as $blockID => $block) {

                    /* add acf pro blocks */
                    if($oes->acf_pro){

                        /* validate name */
                        if (!isset($block['name'])) continue;

                        $name = $block['name'];
                        $title = $block['title'] ?? $name;

                        /* get block parameter */
                        $args = array_merge(self::DEFAULT_BLOCK_PARAMS, [
                            'name' => $name,
                            'title' => $title
                        ], $block);

                        /* check if render template or callback exists */
                        if (!isset($args['render_callback']) && !isset($args['render_template']))
                            $args['render_callback'] = [$this, 'default_block_render'];

                        /* register block type */
                        acf_register_block_type($args);

                        /* register acf block with field groups */
                        if (isset($block['field_group'])) {

                            /* validate parameter */
                            $key = $block['field_group']['key'] ?? 'group_' . $name;
                            $fieldGroupTitle = $block['field_group']['title'] ?? $name;
                            $location = 'acf/' . str_replace("_", "-", $name);

                            /* get default field group parameter */
                            $fieldGroupArgs = array_merge([
                                'key' => $key,
                                'title' => $fieldGroupTitle,
                                'location' => [[[
                                    'param' => 'block',
                                    'operator' => '==',
                                    'value' => $location,
                                ]]]
                            ], $block['field_group']);

                            /* add field group -----------------------------------------------------------------------*/
                            acf_add_local_field_group($fieldGroupArgs);
                        }
                    }
                    else{

                        /* get block parameter */
                        $args = array_merge(self::DEFAULT_BLOCK_PARAMS, $block);

                        /* check if render template or callback exists */
                        if (!isset($args['render_callback']) && !isset($args['render_template']))
                            $args['render_callback'] = 'oes_block_render_' . str_replace('-', '_', $blockID);

                        /* check for script */
                        if(file_exists($oes->path_project_plugin . '/includes/blocks/' . $blockID . '/block.js')){

                            /* register script */
                            wp_register_script(
                                $blockID,
                                plugins_url($oes->basename_project . '/includes/blocks/' . $blockID . '/block.js'),
                                ['wp-blocks', 'wp-element', 'wp-components', 'wp-editor']
                            );
                            $args['editor_script'] = $blockID;

                        }

                        /* Register Block ----------------------------------------------------------------------------*/
                        register_block_type('oes/' . $blockID, $args);
                    }
                }

            /* loop through acf pro blocks */
            if ($oes->acf_pro){

                /* include acf pro blocks */
                oes_include('/includes/blocks/acf-pro/oes-post-content/block.php');

                /* include custom acf pro blocks (set by OES Project Plugin) */
                if (!empty($oes->blocks['acf_pro']))
                    foreach ($oes->blocks['acf_pro'] as $block) {

                        /* validate name */
                        if (!isset($block['name'])) continue;

                        $name = $block['name'];
                        $title = $block['title'] ?? $name;

                        /* get block parameter */
                        $args = array_merge(self::DEFAULT_BLOCK_PARAMS, [
                            'name' => $name,
                            'title' => $title
                        ], $block);

                        /* check if render template or callback exists */
                        if (!isset($args['render_callback']) && !isset($args['render_template']))
                            $args['render_callback'] = [$this, 'default_block_render'];

                        /* register block ----------------------------------------------------------------------------*/

                        /* register acf block with field groups */
                        if (isset($block['field_group'])) {

                            acf_register_block_type($args);

                            /* validate parameter */
                            $key = $block['field_group']['key'] ?? 'group_' . $name;
                            $fieldGroupTitle = $block['field_group']['title'] ?? $name;
                            $location = 'acf/' . str_replace("_", "-", $name);

                            /* get default field group parameter */
                            $fieldGroupArgs = array_merge([
                                'key' => $key,
                                'title' => $fieldGroupTitle,
                                'location' => [[[
                                    'param' => 'block',
                                    'operator' => '==',
                                    'value' => $location,
                                ]]]
                            ], $block['field_group']);

                            /* add field group -----------------------------------------------------------------------*/
                            acf_add_local_field_group($fieldGroupArgs);
                        }
                    }
            }
        }


        /**
         * Default render output if no callback defined.
         */
        function default_block_render()
        {
            echo 'No render callback or template defined.';
        }


        /**
         * Register new category for OES Blocks.
         */
        function oes_block_category($categories): array
        {
            return array_merge(
                $categories,
                [[
                    'slug' => 'oes-blocks',
                    'title' => __('OES', 'oes-blocks')
                ]]
            );
        }
    }


    /* include blocks */
    oes_include('/includes/blocks/oes-table-of-contents/block.php');
    oes_include('/includes/blocks/oes-card/block.php');
    oes_include('/includes/blocks/oes-featured-post/block.php');

    /* instantiate */
    new Blocks();
}