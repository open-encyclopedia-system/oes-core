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
            add_action('admin_enqueue_scripts', [$this, 'load_assets']);
        }


        /**
         * Add script to be registered.
         *
         * @param string $handle A string containing the name of the script.
         * @param string $src A string containing the full url of the script. If false, script is alias.
         * @param array $depends Optional array containing registered script handles that this script depends on.
         * @param string|boolean $ver Optional string containing the script version number.
         * @param boolean $in_footer Optional boolean indicating whether to enqueue the script before body.
         * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
         * @return void
         */
        function add_script(string $handle, string $src, array $depends = [], $ver = false, bool $in_footer = true, bool $admin = false): void
        {
            $this->scripts[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(OES()->basename . $src),
                'depends' => $depends,
                'ver' => $ver,
                'in_footer' => $in_footer,
                'admin' => $admin
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
         * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
         * @return void
         */
        function add_project_script(string $handle, string $src, array $depends = [], $ver = false, bool $in_footer = true, bool $admin = false): void
        {
            $this->scripts[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(basename(OES()->path_project_plugin) . $src),
                'depends' => $depends,
                'ver' => $ver,
                'in_footer' => $in_footer,
                'admin' => $admin
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
         * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
         * @return void
         */
        function add_style(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all', bool $admin = false): void
        {
            $this->styles[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(OES()->basename . $src),
                'deps' => $deps,
                'ver' => is_null($ver) ? oes_get_version() : $ver,
                'media' => $media,
                'admin' => $admin
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
         * @param bool $admin Optional boolean identifying if style is enqueued only for admin.
         * @return void
         */
        function add_project_style(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all', bool $admin = false): void
        {
            $this->styles[$handle] = [
                'handle' => $handle,
                'src' => plugins_url(basename(OES()->path_project_plugin) . $src),
                'deps' => $deps,
                'ver' => is_null($ver) ? oes_get_version() : $ver,
                'media' => $media,
                'admin' => $admin
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
         * @return void
         */
        function add_project_admin_style(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all'): void
        {
            $this->add_project_style($handle, $src, $deps, $ver, $media, true);
        }


        /**
         * Register all scripts and styles.
         * @return void
         */
        function register_scripts_and_styles(): void
        {
            foreach ($this->scripts as $script)
                if (!$script['admin']) {
                    wp_register_script($script['handle'], $script['src'], $script['depends'], $script['ver'], $script['in_footer']);
                    wp_enqueue_script($script['handle']);
                }

            foreach ($this->styles as $style)
                if (!$style['admin']) {
                    wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
                    wp_enqueue_style($style['handle']);
                }
        }


        /**
         * Enqueue scripts and styles.
         * @return void
         */
        function enqueue_scripts(): void
        {
            add_action('wp_enqueue_scripts', [$this, 'load_assets'], 0);
        }


        /**
         * Load js scripts.
         * @return void
         */
        function load_assets(): void
        {
            foreach ($this->styles as $style) {
                wp_register_style($style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media']);
                wp_enqueue_style($style['handle']);
            }

            foreach ($this->scripts as $script) {
                wp_register_script($script['handle'], $script['src'], $script['depends'], $script['ver'], $script['in_footer']);
                wp_enqueue_script($script['handle']);
            }
        }
    }

    /* instantiate */
    $oes = OES();
    $oes->assets = new Assets();
    $oes->assets->add_style('oes-select2', '/assets/css/select2.css', [], '4.1.0-rc.0');
    $oes->assets->add_style('oes-select2-overwrite', '/assets/css/select2-overwrite.css', [], '4.1.0-rc.0');
    $oes->assets->add_script('oes-select2', '/assets/js/select2.js', ['jquery'], '4.1.0-rc.0');
    $oes->assets->add_script('oes-select-init', '/assets/js/select2-init.js', ['jquery'], '4.1.0-rc.0');
    $oes->assets->add_style('oes-admin', '/assets/css/admin.css', [], null, 'all', true);
    $oes->assets->add_style('oes-theme', '/assets/css/theme.css');
    $oes->assets->add_script('oes-admin', '/assets/js/admin.js', ['jquery'], false, true, true);
    $oes->assets->add_script('oes-theme', '/assets/js/theme.js', ['jquery']);

endif;