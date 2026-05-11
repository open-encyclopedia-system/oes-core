<?php

namespace OES\Admin\Health;

if (!defined('ABSPATH')) exit;


if (!class_exists('\OES\Admin\Health\Site_Health')) {

    /**
     * The OES Site Health class responsible for populating OES debug information in WordPress Site Health.
     *
     * @oesDevelopment Add for WP Cli
     */
    class Site_Health
    {

        /** @var string The option name used to store site health data. */
        public string $option_name = 'oes_site_health';
        
        const ENABLED = 'enabled';
        const DISABLED = 'disabled';

        /**
         * Gets the stored site health information.
         *
         * @return array
         */
        public function get_site_health(): array
        {
            $value = get_option($this->option_name);

            if (is_array($value)) {
                return $value;
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return [];
        }

        /**
         * Updates the site health information.
         *
         * @param array $data An array of site health information to update.
         * @return boolean
         */
        public function update_site_health(array $data = array()): bool
        {
            return update_option($this->option_name, wp_json_encode($data), false);
        }

        /**
         * Stores debug data in the OES site health option.
         *
         * @param array $data Data to update with (optional).
         * @return boolean
         */
        public function update_site_health_data(array $data = []): bool
        {
            $values = $data ?: $this->get_site_health_values();

            if (empty($values)) {
                return false;
            }

            $updated = [
                'last_updated' => time(),
            ];

            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $updated[$key] = $value['debug'] ?? $value['value'] ?? null;
                } else {
                    $updated[$key] = $value;
                }
            }

            return $this->update_site_health($updated);
        }

        /**
         * Appends the OES section to the "Info" tab of the WordPress Site Health screen.
         *
         * @param array $info The current debug info for site health.
         * @return array
         */
        public function render_tab_content(array $info = []): array
        {
            $data = $this->get_site_health_values();

            if (!empty($data)) {
                $this->update_site_health_data($data);
            }

            $info['oes'] = [
                'label' => __('OES', 'oes'),
                'description' => __(
                    'Debug information about the OES configuration. This data helps diagnose issues and should be shared with support when troubleshooting.',
                    'oes'
                ),
                'fields' => $data,
            ];

            return $info;
        }

        /**
         * Gets the values for all data in the OES site health section.
         *
         * @return array
         */
        public function get_site_health_values(bool $extended = false): array
        {

            /*
             *
             * Datamodel
             * gitLab commit?
             *Schema?
             * enabled lod?
             * enabled features?
             * available REST APIs
             *
             *
             * Database cache etc
             * Constants
             *
             *     'site_url' => get_site_url(),
    'php_version' => phpversion(),
    'wp_version' => get_bloginfo('version'),
    'active_plugins' => get_option('active_plugins'),
    'memory_usage' => memory_get_usage(),
    'errors' => [...],
    'timestamp' => time()
             * */

            $oes = OES();

            $fields['version'] = [
                'label' => __('Plugin Version', 'oes'),
                'value' => $oes->version
            ];

            $name = oes_get_application_name(null, false);
            $fields['application'] = [
                'label' => __('Application', 'oes'),
                'value' => strtoupper(str_replace('-', ' ', $name)),
                'raw' => $name
            ];

            $this->get_environment_values($fields, $extended);
            $this->get_theme_values($fields, $extended);
            $this->get_plugin_values($fields, $extended);


            return $fields;
        }
        
        public function get_environment_values(&$fields, bool $extended = false): void
        {
            global $wpdb;
            $oes = OES();

            $fields['wp_version'] = [
                'label' => __('WordPress Version', 'oes'),
                'value' => get_bloginfo('version'),
            ];

            $fields['mysql_version'] = [
                'label' => __('MySQL Version', 'oes'),
                'value' => $wpdb->db_server_info(),
            ];

            $fields['acf_version'] = [
                'label' => __('ACF Plugin Version', 'oes'),
                'value' => defined('ACF_VERSION') ? ACF_VERSION : '',
            ];

            $fields['acf_pro'] = [
                'label' => __('ACF Pro', 'oes'),
                'value' => $oes->acf_pro ? self::ENABLED : self::DISABLED,
            ];

            $fields['site_url'] = [
                'label' => __('Site URL', 'oes'),
                'value' => get_site_url(),
            ];

            $fields['php_version'] = [
                'label' => __('PHP Version', 'oes'),
                'value' => phpversion(),
            ];

            $fields['memory'] = [
                'label' => __('Memory Usage', 'oes'),
                'value' => memory_get_usage(),
            ];

            /*$fields['memory_limit'] = [
                'label' => __('Memory Limit', 'oes'),
                'value' => 'TODO: read from phpinit?',
            ];

            $fields['errors'] = [
                'label' => __('ERrors', 'oes'),
                'value' => 'TODO: read from debug.log?',
            ];*/

            $fields['timestamp'] = [
                'label' => __('Timestamp', 'oes'),
                'value' => time()
            ];
        }

        public function get_theme_values(&$fields, bool $extended = false): void
        {
            $activeTheme = wp_get_theme();
            $parentTheme = $activeTheme->parent();

            $fields['active_theme'] = [
                'label' => __('Active Theme', 'oes'),
                'value' => $extended ?[
                    'name' => $activeTheme->get('Name'),
                    'version' => $activeTheme->get('Version'),
                    'theme_uri' => $activeTheme->get('ThemeURI'),
                    'stylesheet' => $activeTheme->get('Stylesheet'),
                ] : [
                    'name' => $activeTheme->get('Name'),
                    'version' => $activeTheme->get('Version')
                ],
            ];

            $fields['block_theme'] = [
                'label' => __('Active Theme is Block Theme', 'oes'),
                'value' => $activeTheme->is_block_theme() ? self::ENABLED : self::DISABLED,
            ];

            if ($parentTheme) {
                $fields['parent_theme'] = [
                    'label' => __('Parent Theme', 'oes'),
                    'value' => $extended ? [
                        'name' => $parentTheme->get('Name'),
                        'version' => $parentTheme->get('Version'),
                        'theme_uri' => $parentTheme->get('ThemeURI'),
                        'stylesheet' => $parentTheme->get('Stylesheet'),
                    ] : [
                        'name' => $parentTheme->get('Name'),
                        'version' => $parentTheme->get('Version')
                    ],
                ];
            }
        }

        public function get_plugin_values(&$fields, bool $extended = false): void
        {
            $activePlugins = [];
            $plugins = get_plugins();

            $pluginCategories = [
                'module'  => __('OES Modules', 'oes'),
                'application'  => __('OES Application', 'oes'),
                'other'  => __('Other Plugins', 'oes'),
            ];


            $application = defined('OES_BASENAME_APPLICATION') ? OES_BASENAME_APPLICATION :  '';

            foreach ($plugins as $pluginPath => $plugin) {
                if (!is_plugin_active($pluginPath)) {
                    continue;
                }

                //TODO differentiate OES Module, OES Projekte, others

                $category = 'other';
                $name = $plugin['Name'];
                if(str_starts_with($name, 'OES ')) {

                    $category = 'module';
                    if($name == $application) {
                        $category = 'application';
                    }
                }


                $activePlugins[$category][] = $extended ? [
                    'name' => $plugin['Name'],
                    'version' => $plugin['Version'],
                    'plugin_uri' => empty($plugin['PluginURI']) ? '' : $plugin['PluginURI'],
                ] : $plugin['Name'];
            }

            foreach ($pluginCategories as $category => $categoryLabel) {
                if(isset($activePlugins[$category])) {
                    $fields['active_plugins_' . $category] = [
                        'label' => __('Active Plugins', 'oes') . ' - ' . $categoryLabel,
                        'value' => $activePlugins[$category],
                    ];
                }
            }
        }
    }
}