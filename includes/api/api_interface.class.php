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

        /** @var string The database link. */
        public string $database_link = '';

        /** @var array Options for admin (ajax) scripts. */
        public array $admin_script_args = [];

        /** @var array Options for frontend (ajax) scripts. */
        public array $frontend_script_args = [];

        /** @var bool Include css files. Path is /includes/api/[API key]/[API key].css. */
        public bool $css = false;

        /** @var array The search options when using the LOD search interface. */
        public array $search_options = [];

        /** @var array The config options when using the LOD search interface. */
        public array $config_options = [];

        /** @var bool Rest API requires credentials. */
        public bool $credentials = false;

        /* The properties for matching and configuration. */
        const PROPERTIES = [];

        /** The search parameters for the LOD interface. */
        const SEARCH_PARAMETERS = [];


        /**
         * API_Interface constructor.
         */
        function __construct(string $apiKey = '')
        {
            /* set parameters */
            $this->identifier = $apiKey;
            $this->set_parameters();
            $this->search_options = $this->set_search_options();
            $this->config_options = $this->set_config_options();

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
            add_action('admin_enqueue_scripts', function () use ($apiKey, $scriptArgs, $oes) {

                /* enqueue general js */
                global $post;
                wp_register_script('oes-api',
                    plugins_url($oes->basename . '/includes/api/api-admin.js'),
                    ['jquery'], false, true);
                wp_localize_script(
                    'oes-api',
                    'oesLodAJAX',
                    [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'ajax_nonce' => wp_create_nonce('oes_lod_nonce'),
                        'post_id' => $post ? $post->ID : false
                    ]
                );
                wp_enqueue_script('oes-api');

                /* only display on post admin pages */
                if ($scriptArgs && !empty($scriptArgs))
                    if ($post && file_exists($oes->path_core_plugin . '/includes/api/' . $apiKey . '/' . $apiKey . '-admin.js')) {
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
            add_action('wp_enqueue_scripts', function () use ($apiKey, $scriptArgsFrontend) {

                /* general */
                wp_register_script('oes-api-frontend',
                    plugins_url(OES()->basename . '/includes/api/api-frontend.js'),
                    ['jquery'], false, true);
                wp_localize_script(
                    'oes-api-frontend',
                    'oesLodAJAX',
                    [
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'ajax_nonce' => wp_create_nonce('oes_lod_nonce')
                    ]
                );
                wp_enqueue_script('oes-api-frontend');


                /* apis */
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
            });


            /* register sidebar for block editor */
            add_action('init', '\OES\API\oes_lod_register_sidebar');
            add_action('enqueue_block_editor_assets', '\OES\API\oes_lod_sidebar_assets');

            /* add shortcode */
            add_shortcode($apiKey . 'link', [$this, 'render_shortcode']);
        }


        /**
         * Generate link text from shortcode.
         *
         * @param array $args The shortcode parameters.
         * @param string $content The shortcode content.
         * @return string Return the generated html link.
         */
        function render_shortcode(array $args, string $content = ""): string
        {
            /* get gnd object */
            if ($lodID = $args['id'] ?? false) {

                /* get global OES instance parameter */
                $oes = OES();

                $iconPath = '/includes/api/' . $this->identifier . '/icon_' . $this->identifier . '.png';
                $iconPathAbsolute = file_exists($oes->path_core_plugin . $iconPath) ?
                    plugins_url($oes->basename . $iconPath) :
                    plugins_url($oes->basename . '/includes/api/icon_lod_preview.png');

                return '<span class="oes-lod-container">' .
                    sprintf('<a href="javascript:void(0)" class="oes-lodlink" data-api="%s" data-lodid="%s">%s%s</a>',
                        $this->identifier,
                        $lodID,
                        $args['label'] ?? $lodID,
                        oes_get_html_img($iconPathAbsolute, 'oes-' . $this->identifier . '-icon')
                    ) . '</span>';

            } else return $content;
        }


        /**
         * Set optional parameters (implement)
         */
        function set_parameters()
        {
        }


        /**
         * Set search options for LOD search interface. Valid Format is an array with 'value' and 'form' pair.
         */
        function set_search_options(): array
        {
            $searchOptions = [];
            foreach (static::SEARCH_PARAMETERS as $option) {

                /* prepare class name */
                $classNameArray = ['oes-' . $this->identifier . '-search-options', 'oes-lod-search-options'];
                if (isset($option['args']['class'])) {
                    $currentClasses = explode(' ', $option['args']['class']);
                    $classNameArray = array_merge($currentClasses, $classNameArray);
                }
                $option['args']['class'] = implode(' ', array_unique($classNameArray));

                /* prepare form element */
                $searchOptions[] = [
                    'label' => '<label for="' . $option['id'] . '">' . $option['label'] . '</label>',
                    'form' => oes_html_get_form_element(
                        $option['type'] ?? 'checkbox',
                        $option['id'] ?? 'name-missing',
                        $option['id'] ?? 'id-missing',
                        $option['value'] ?? false,
                        $option['args'])
                ];
            }

            return $searchOptions;
        }


        /**
         * Set config options for editorial layer.
         */
        function set_config_options(): array
        {
            /* prepare options for admin configuration : loop through all properties and store as option */
            $properties = [];
            foreach (static::PROPERTIES as $key => $property)
                $properties[$key] = ($property['label']['german'] ?? '[Label missing]') . '/' .
                    ($property['label']['english'] ?? '[Label missing]') . ' (' . $key . ')';
            asort($properties);

            return [
                'properties' => [
                    'label' => $this->label . ' Properties',
                    'type' => 'select',
                    'multiple' => true,
                    'capabilities' => ['backend', 'fields', 'lod'],
                    'skip_admin_config' => true,
                    'options' => (empty(static::PROPERTIES) ? [] :
                        [
                            $this->identifier => [
                                'label' => $this->label . ' Basic',
                                'options' => [
                                    'base-timestamp' => $this->label . ' Timestamp'
                                ]
                            ],
                            $this->identifier . '_properties' => [
                                'label' => $this->label . ' Properties',
                                'options' => $properties
                            ]
                        ]),
                    'description' => 'Enable copying from ' . $this->label .
                        ' entry to field. Only relevant if LOD Options "Copy To Post" is set.'
                ]
            ];
        }
    }
}


/* add style */
OES()->assets->add_style('oes-api', '/includes/api/api.css');

/**
 * Register lod sidebar script for block editor TODO @nextRelease: handle through OES Assets class
 */
function oes_lod_register_sidebar()
{
    wp_register_script(
        'oes-lod-sidebar',
        plugins_url(OES()->basename . '/includes/api/lod-sidebar.js'),
        ['wp-plugins', 'wp-edit-post', 'wp-element']
    );
}


/**
 * Enqueue lod sidebar scripts.
 */
function oes_lod_sidebar_assets()
{
    wp_enqueue_script('oes-lod-sidebar');
}
