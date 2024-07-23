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

        /** @var string The option prefix. */
        public string $option_prefix = '';

        /** @var string The selected option name. */
        public string $selected_option = '';

        /** @var bool|int The selected option number (if nested value). */
        public $selected_nr = 0;

        /** @var array Additional submit options. */
        public array $additional_submits = [];


        //Overwrite parent
        function initialize_parameters($args = []): void
        {

            add_action('admin_enqueue_scripts', [$this, 'add_page_scripts']);

            $this->form_action = admin_url('admin-post.php');
            $this->selected_option = $_GET['selected'] ?? '';
            $this->selected_nr = isset($_GET['nr']) ? ((int)$_GET['nr'] ?? 0) : false;
        }


        //Overwrite parent
        function information_html(): string
        {
            if (empty($this->selected_option)) return '';

            return '<div class="oes-shortcode-container">' .
                \OES\Shortcode\get_shortcode_from_option($this->selected_option,
                    OES_MAP_SHORTCODE_PREFIX,
                    OES_MAP_SHORTCODE_PARAMETER,
                    $this->selected_nr) .
                '</div>';
        }


        //Overwrite parent
        function submit_html(): string
        {
            return '<div class=" oes-shortcode-buttons"><p>' .
                '<input type="hidden" name="oes_shortcode_nr" value="' . $this->selected_nr . '">' .
                $this->additional_submits() .
                ($this->selected_option ?
                    '<input type="submit" name="shortcode_delete" id="shortcode_delete" class="button oes-highlighted" ' .
                    'value="Delete Shortcode" onclick="return confirm(\'You are about to delete a shortcode. ' .
                    'Do you want to proceed?\');">' :
                    '') .
                '<input type="submit" name="submit" id="submit" class="button button-primary button-large" value="Save Shortcode">' .
                '</p></div>';
        }


        //Overwrite parent
        function admin_post_tool_action(): void
        {

            /* prepare option name */
            $archiveFlag = $_POST['oes_shortcode']['replace_archive'] ?? false;
            $isArchiveOption = (!empty($archiveFlag) && $archiveFlag !== 'none');
            $option = 'oes_shortcode-map' . ($isArchiveOption ? '-' . $archiveFlag : '');


            /* delete option */
            if (isset($_POST['shortcode_delete']))
                \OES\Shortcode\delete_shortcode($option, $_POST['oes_shortcode_nr'] ?? false);

            /* create or update option */
            else {

                /* prepare new value */
                $newValue = $_POST['oes_shortcode'] ?? [];

                /* add new category */
                if (isset($_POST['shortcode_add']))
                    foreach ($_POST['shortcode_add'] as $nestedValue => $ignore)
                        $newValue[$nestedValue][] = $newValue[$nestedValue][sizeof($newValue[$nestedValue] ?? [])] ?? [];

                if ($isArchiveOption) $value = $newValue;
                elseif (isset($_POST['oes_shortcode_nr']) && isset($value[$_POST['oes_shortcode_nr']])) {
                    $value = \OES\Shortcode\get_shortcode_option($option);
                    $value[(int)$_POST['oes_shortcode_nr'] ?? 0] = $newValue;
                } else $value[] = $newValue;
                if (!is_array($value)) $value = [];

                \OES\Shortcode\store_shortcode($option, $value);
            }
        }


        /**
         * Enqueue script for an admin page.
         *
         * @return void
         */
        function add_page_scripts(): void
        {
        }


        /**
         * Add additional submit options to form.
         *
         * @return string Return additional submits.
         */
        function additional_submits(): string
        {
            $submits = '';
            foreach($this->additional_submits as $key => $label)
                $submits .= '<input type="submit" name="shortcode_add[' . $key . ']" id="shortcode_add-' . $key .
                    '" class="button" value="' . $label . '">';
            return $submits;
        }


        /**
         * Get the value of the current selected option.
         *
         * @return array|mixed Return the current selected option value.
         */
        function get_current_selected_option_value()
        {
            return \OES\Shortcode\get_shortcode_option($this->selected_option, $this->selected_nr);
        }
    }
endif;