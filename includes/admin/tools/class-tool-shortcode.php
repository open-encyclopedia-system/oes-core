<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Admin\Tools\Config')) oes_include('admin/tools/config/class-config.php');

if (!class_exists('Shortcode')) :

    /**
     * Class Shortcode
     *
     * A class to create, edit and store shortcodes.
     */
    class Shortcode extends \OES\Admin\Tools\Config
    {
        /** @var string Option prefix used for shortcode options. */
        public string $option_prefix = '';

        /** @var string Prefix used for shortcode names. */
        public string $shortcode_prefix = '';

        /** @var array Parameters available for the shortcode. */
        public array $shortcode_parameters = [];

        /** @var string Currently selected option name. */
        public string $selected_option = '';

        /** @var int|bool Selected option index or false if none selected. */
        public $selected_nr = 0;

        /** @var array Additional submit buttons to display in the form. Format: [key => label] */
        public array $additional_submits = [];


        /** @inheritdoc */
        public function initialize_parameters($args = []): void
        {
            add_action('admin_enqueue_scripts', [$this, 'add_page_scripts']);

            $this->form_action = admin_url('admin-post.php');
            $this->selected_option = sanitize_text_field($_GET['selected'] ?? '');
            $this->selected_nr = isset($_GET['nr']) ? (int) $_GET['nr'] : false;
        }


        /** @inheritdoc */
        public function information_html(): string
        {
            if (empty($this->selected_option)) {
                return '';
            }

            return '<div class="oes-shortcode-container">' .
                \OES\Shortcode\get_shortcode_from_option(
                    $this->selected_option,
                    $this->shortcode_prefix,
                    $this->shortcode_parameters,
                    $this->selected_nr
                ) .
                '</div>';
        }


        /** @inheritdoc */
        public function submit_html(): string
        {
            $html = '<div class="oes-shortcode-buttons"><p>';

            $html .= '<input type="hidden" name="oes_shortcode_nr" value="' . esc_attr($this->selected_nr) . '">';
            $html .= $this->additional_submits();

            if ($this->selected_option) {
                $html .= '<input type="submit" name="shortcode_delete" id="shortcode_delete" class="button oes-highlighted" ' .
                    'value="Delete Shortcode" onclick="return confirm(\'You are about to delete a shortcode. Do you want to proceed?\');">';
            }

            $html .= '<input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Save Shortcode">';
            $html .= '</p></div>';

            return $html;
        }


        /** @inheritdoc */
        public function admin_post_tool_action(): void
        {
            $post = $_POST;
            $shortcode_data = $post['oes_shortcode'] ?? [];
            $shortcode_nr = $post['oes_shortcode_nr'] ?? null;
            $archiveFlag = $shortcode_data['replace_archive'] ?? false;

            $isArchive = (!empty($archiveFlag) && $archiveFlag !== 'none');
            $option = 'oes_shortcode-' . $this->shortcode_prefix . ($isArchive ? '-' . $archiveFlag : '');

            if (isset($post['shortcode_delete'])) {
                \OES\Shortcode\delete_shortcode($option, $shortcode_nr ?: false);
                return;
            }

            if (isset($post['shortcode_add'])) {
                foreach ($post['shortcode_add'] as $nested => $_) {
                    $shortcode_data[$nested][] = $shortcode_data[$nested][count($shortcode_data[$nested] ?? [])] ?? [];
                }
            }

            if ($isArchive) {
                $value = $shortcode_data;
            } elseif ($shortcode_nr !== null && is_numeric($shortcode_nr)) {
                $value = \OES\Shortcode\get_shortcode_option($option);
                $value[(int)$shortcode_nr] = $shortcode_data;
            } else {
                $value[] = $shortcode_data;
            }

            \OES\Shortcode\store_shortcode($option, is_array($value) ? $value : []);
        }


        /**
         * Enqueues required scripts/styles on the admin page.
         *
         * Intended for hooking into 'admin_enqueue_scripts'.
         *
         * @return void
         */
        public function add_page_scripts(): void
        {
        }


        /**
         * Generates HTML for additional submit buttons.
         *
         * These buttons allow dynamic actions such as adding nested shortcode parameters.
         *
         * @return string HTML for additional submit buttons.
         */
        public function additional_submits(): string
        {
            $html = '';
            foreach ($this->additional_submits as $key => $label) {
                $html .= '<input type="submit" name="shortcode_add[' . esc_attr($key) . ']" id="shortcode_add-' . esc_attr($key) .
                    '" class="button" value="' . esc_attr($label) . '">';
            }
            return $html;
        }


        /**
         * Returns the currently selected shortcode option's value.
         *
         * @return mixed The stored shortcode data for the selected option and index.
         */
        public function get_current_selected_option_value(): mixed
        {
            return \OES\Shortcode\get_shortcode_option($this->selected_option, $this->selected_nr);
        }
    }

endif;
