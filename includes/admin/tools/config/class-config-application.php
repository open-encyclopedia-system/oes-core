<?php

/**
 * @file
 * @reviewed 3.0.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config', false)) oes_include('admin/tools/config/class-config.php');

if (class_exists('Application', false)) exit;

/**
 * Class Application
 *
 * Implement the config tool for application configurations.
 */
class Application extends Config
{

    /** @var array The application options. The options must have the following format:
     *  %option_key% => [
     *      'label' => (optional) Option Label,
     *      'description' => (optional) Option Description,
     *      'type' => (optional) Option Type. Default is 'text'.
     * ]
     * The option key must be unique!
     */
    public array $application_options = [];

    /** @inheritdoc */
    function initialize_parameters($args = []): void
    {
        $this->form_action = admin_url('admin-post.php');

        $this->application_options = \OES\Admin\get_application_settings($this->application_options);
    }

    /** @inheritdoc */
    function set_table_data_for_display()
    {
        foreach ($this->application_options as $optionKey => $option) {

            $value = get_option($optionKey) ?? 0;
            if ($option['json'] ?? false) {
                $value = esc_attr($value);
                if (!is_string($value)) {
                    $value = '';
                }
            }

            $this->add_table_row(
                [
                    'title' => ($option['label'] ?? $optionKey),
                    'key' => $optionKey,
                    'value' => $value,
                    'type' => ($option['type'] ?? 'text'),
                    'args' => $option['args'] ?? []
                ],
                [
                    'subtitle' => ($option['description'] ?? '')
                ]
            );
        }
    }

    /** @inheritdoc */
    function admin_post_tool_action(): void
    {
        foreach ($this->application_options as $optionKey => $option) {

            $value = $_POST[$optionKey] ?? '';
            if ($option['json'] ?? false) {
                $value = wp_unslash($value);
            }

            if (!oes_option_exists($optionKey)) {
                add_option($optionKey, $value);
            } else {
                update_option($optionKey, $value);
            }
        }
    }
}

// initialize
register_tool('\OES\Admin\Tools\Application', 'application');
