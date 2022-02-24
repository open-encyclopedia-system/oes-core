<?php

namespace OES\Admin;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Assets')) :

    /**
     * Class Assets
     *
     * Register and enqueue styles and scripts.
     */
    class Assets
    {

        /** @var array Array containing the scripts to be registered */
        public array $scripts = [];

        /** @var array Array containing the styles to be registered */
        public array $styles = [];


        /**
         * Assets constructor.
         */
        function __construct()
        {
            add_action('init', [$this, 'register_scripts_and_styles']);
            add_action('admin_enqueue_style', [$this, 'load_css'], 30);
            add_action('admin_enqueue_scripts', [$this, 'load_js'], 30);
        }


        /**
         * Add script to be registered.
         *
         * @param string $handle A string containing the name of the script.
         * @param string $src A string containing the full url of the script. If false, script is alias.
         * @param array $depends Optional array containing registered script handles that this script depends on.
         * @param string|boolean $ver Optional string containing the script version number.
         * @param boolean $in_footer Optional boolean indicating whether to enqueue the script before body.
         */
        function add_script(string $handle, string $src, array $depends = [], $ver = false, bool $in_footer = true)
        {
            $this->scripts[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(OES()->basename . $src),
                'depends' => $depends,
                'ver' => $ver,
                'in_footer' => $in_footer
            ];
        }


        /**
         * Add project script to be registered.
         *
         * @param string $handle A string containing the name of the script.
         * @param string $src A string containing the full url of the script. If false, script is alias.
         * @param array $depends Optional array containing registered script handles that this script depends on.
         * @param string|boolean $ver Optional string containing the script version number.
         * @param boolean $in_footer Optional boolean indicating whether to enqueue the script before body.
         */
        function add_project_script(string $handle, string $src, array $depends = [], $ver = false, bool $in_footer = true)
        {
            $this->scripts[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(basename(OES()->path_project_plugin) . $src),
                'depends' => $depends,
                'ver' => $ver,
                'in_footer' => $in_footer
            ];
        }


        /**
         * Add style to be registered.
         *
         * @param string $handle A string containing the name of the style.
         * @param string $src A string containing the full url of the style. If false, style is alias.
         * @param array $deps Optional array containing registered style handles that this style depends on.
         * @param string|null|boolean $ver Optional string containing the style version number.
         * @param string $media Optional string containing the media for which this stylesheet has been defined.
         */
        function add_style(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all')
        {
            $this->styles[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(OES()->basename . $src),
                'deps' => $deps,
                'ver' => is_null($ver) ? oes_get_version() : $ver,
                'media' => $media
            ];
        }


        /**
         * Add project style to be registered.
         *
         * @param string $handle A string containing the name of the style.
         * @param string $src A string containing the full url of the style. If false, style is alias.
         * @param array $deps Optional array containing registered style handles that this style depends on.
         * @param string|null|boolean $ver Optional string containing the style version number.
         * @param string $media Optional string containing the media for which this stylesheet has been defined.
         */
        function add_project_style(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all')
        {
            $this->styles[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(basename(OES()->path_project_plugin) . $src),
                'deps' => $deps,
                'ver' => is_null($ver) ? oes_get_version() : $ver,
                'media' => $media
            ];
        }


        /**
         * Register all scripts and styles.
         */
        function register_scripts_and_styles()
        {
            foreach ($this->scripts as $script) {
                wp_register_script($script['handle'], $script['src'], $script['depends'], $script['ver'], $script['in_footer']);
                wp_enqueue_script($script['handle']);
            }

            foreach ($this->styles as $style) {
                wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
                wp_enqueue_style($style['handle']);
            }
        }


        /**
         * Enqueue scripts and styles.
         */
        function enqueue_scripts()
        {
            add_action('wp_enqueue_styles', [$this, 'load_css'], 0);
            add_action('wp_enqueue_scripts', [$this, 'load_js'], 0);
        }


        /**
         * Load css styles.
         */
        function load_css()
        {
            foreach ($this->styles as $style) wp_enqueue_style($style['handle']);
        }


        /**
         * Load js scripts.
         */
        function load_js()
        {
            foreach ($this->scripts as $script) wp_enqueue_script($script['handle']);
        }
    }

    /* instantiate */
    $oes = OES();
    $oes->assets = new Assets();
    $oes->assets->add_style('oes-select2', '/assets/css/select2.css', [], '4.1.0-rc.0');
    $oes->assets->add_style('oes-select2-overwrite', '/assets/css/select2-overwrite.css', [], '4.1.0-rc.0');
    $oes->assets->add_script('oes-select2', '/assets/js/select2.js', ['jquery'], '4.1.0-rc.0');
    $oes->assets->add_script('oes-select-init', '/assets/js/select2-init.js', ['jquery'], '4.1.0-rc.0');
    $oes->assets->add_style('oes-admin', '/assets/css/admin.css');
    $oes->assets->add_style('oes-theme', '/assets/css/theme.css');
    $oes->assets->add_script('oes-admin', '/assets/js/admin.js', ['jquery']);
    $oes->assets->add_script('oes-theme', '/assets/js/theme.js', ['jquery']);

endif;