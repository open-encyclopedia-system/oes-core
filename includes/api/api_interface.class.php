<?php

namespace OES\API;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('API_Interface')) {
    class API_Interface
    {

        /** @var string The api identifier. */
        public string $identifier = 'api';

        /** @var string The api label. */
        public string $label = '';

        /** @var array Options for admin (ajax) scripts. */
        public array $admin_script_args = [];

        /** @var array Options for frontend (ajax) scripts. */
        public array $frontend_script_args = [];

        /** @var bool Include css files. Path is /includes/api/[API key]/[API key].css. */
        public bool $css = false;

        /** @var array The admin options that are considered when copying data from API to post. */
        public array $copy_options = [];

        /** @var array The search options when using the LOD search interface. */
        public array $search_options = [];

        /** @var array The config options when using the LOD search interface. */
        public array $config_options = [];

        /** @var array The table header for the result table in the LOD search interface */
        public array $result_table_header = ['lod_checkbox', 'Name', 'lod_link'];


        /**
         * API_Interface constructor.
         */
        function __construct(string $apiKey = '')
        {

            /* set parameters */
            $this->identifier = $apiKey;
            $this->set_parameters();

            /* get global variable */
            $oes = OES();

            /* register styles */
            if ($this->css)
                $oes->assets->add_style('oes-' . $apiKey, '/includes/api/' . $apiKey . '/' . $apiKey . '.css');

            /* add config options to datamodel */
            if ($apiConfigOptions = $this->config_options)
                add_filter('oes/datamodel_lod_fields', function ($lodOptions) use ($apiKey, $apiConfigOptions) {
                    $lodOptions[$apiKey] = $apiConfigOptions;
                    return $lodOptions;
                });

            /* register scripts for admin pages */
            $scriptArgs = $this->admin_script_args;
            if ($scriptArgs && !empty($scriptArgs))
                add_action('admin_enqueue_scripts', function () use ($apiKey, $scriptArgs, $oes) {

                    /* enqueue general js */
                    wp_register_script('oes-api',
                        plugins_url($oes->basename . '/includes/api/api-admin.js'),
                        ['jquery'], false, true);
                    wp_localize_script(
                        'oes-api',
                        'oesApiAJAX',
                        []
                    );
                    wp_enqueue_script('oes-api');

                    /* only display on post admin pages */
                    global $post;
                    if ($post) {
                        if (isset($scriptArgs['post_id'])) $scriptArgs['post_id'] = $post->ID;

                        wp_register_script('oes-' . $apiKey,
                            plugins_url(OES()->basename . '/includes/api/' . $apiKey . '/' . $apiKey . '-admin.js'),
                            ['jquery'], false, true);
                        wp_localize_script(
                            'oes-' . $apiKey,
                            'oes' . ucfirst($apiKey) . 'AJAX',
                            $scriptArgs
                        );
                        wp_enqueue_script('oes-' . $apiKey);
                    }
                });

            /* register scripts for frontend */
            $scriptArgsFrontend = $this->frontend_script_args;
            if ($scriptArgsFrontend && !empty($scriptArgsFrontend))
                add_action('wp_enqueue_scripts', function () use ($apiKey, $scriptArgsFrontend) {
                    wp_register_script('oes-' . $apiKey,
                        plugins_url(OES()->basename . '/includes/api/' . $apiKey . '/' . $apiKey . '.js'),
                        ['jquery'], false, true);
                    wp_localize_script(
                        'oes-' . $apiKey,
                        'oes' . ucfirst($apiKey) . 'AJAX',
                        $scriptArgsFrontend
                    );
                    wp_enqueue_script('oes-' . $apiKey);
                });


            /* register sidebar for block editor */
            add_action('init', '\OES\API\oes_lod_register_sidebar');
            add_action('enqueue_block_editor_assets', '\OES\API\oes_lod_sidebar_assets');
        }

        function set_parameters()
        {
        }
    }
}


/**
 * Register lod sidebar script for block editor TODO @nextRelease: handle through OES Assets class
 */
function oes_lod_register_sidebar() {
    wp_register_script(
        'oes-lod-sidebar',
        plugins_url(OES()->basename . '/includes/api/lod-sidebar.js'),
        ['wp-plugins', 'wp-edit-post', 'wp-element']
    );
}


/**
 * Enqueue lod sidebar scripts.
 */
function oes_lod_sidebar_assets() {
    wp_enqueue_script('oes-lod-sidebar');
}