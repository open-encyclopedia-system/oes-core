<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Panel')) {

    /**
     * Class OES_Panel
     *
     * This class prepares an OES panel.
     */
    class OES_Panel
    {

        /** @var string $type The panel type (implemented and used by child classes). */
        public string $type = 'panel';

        /** @var string $id The panel id. */
        public string $id = '';

        /** @var string $caption The panel caption. */
        public string $caption = '';

        /** @var bool $fore_header Display header even if caption is empty. */
        public bool $force_header = false;

        /** @var bool $is_expanded Display the panel as expanded (active). */
        public bool $is_expanded = true;

        /** @var bool $is_pdf_mode Display for pdf. */
        public bool $is_pdf_mode = false;


        /**
         * OES_Panel constructor.
         *
         * @param array $args Additional arguments.
         */
        public function __construct(array $args = [])
        {
            foreach ($args as $parameterKey => $parameter)
                if (property_exists($this, $parameterKey) && gettype($this->$parameterKey) == gettype($parameter)) {
                    $this->$parameterKey = $parameter;
                }
            $this->validate_parameters();
            $this->set_additional_parameters($args);
        }


        /**
         * Validate the panel parameters.
         * @return void
         */
        function validate_parameters(): void
        {
            $this->calculate_anchor_id();
        }


        /**
         * Check if id is set or can be calculated from caption.
         *
         * @return void
         */
        function calculate_anchor_id(): void
        {
            if (empty($this->id) && !empty($this->caption)) {
                $id = preg_replace('/\s+/', '_', $this->caption);
                $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
                if (!empty($id)) $this->id = 'panel_' . strtolower($id);
            }
        }


        /**
         * Set additional parameters (implemented by child classes).
         *
         * @param array $args The panel arguments passed by constructor.
         * @return void
         */
        function set_additional_parameters(array $args): void
        {
        }


        /**
         * Get html representation of OES panel.
         *
         * @param string $content The panel content.
         * @param array $args Additional parameters.
         * @return string Return the html representation of the OES panel.
         */
        function html(string $content = '', array $args = []): string
        {
            return $this->is_pdf_mode ? $this->get_html_pdf($content, $args) : $this->get_html($content);
        }


        /**
         * Get html representation of OES panel.
         *
         * @param string $content The panel content.
         * @return string Return the html representation of the OES panel.
         */
        function get_html(string $content = ''): string
        {
            return '<div class="oes-panel-container oes-panel-container-' . $this->type . '" id="' . $this->id . '">' .
                '<div class="oes-accordion-wrapper">' . $this->get_html_header() .
                '<div class="oes-accordion-panel oes-panel' . ($this->is_expanded ? ' active': '') . '">' .
                $this->get_html_content($content) .
                '</div>' .
                '</div>' .
                '</div>';
        }


        /**
         * Get the html representation of the OES panel header.
         *
         * @return string Return the html representation of the OES panel.
         */
        function get_html_header(): string
        {
            /* return early if header is empty */
            $caption = $this->get_html_caption();
            if(empty($caption) && !$this->force_header) return '';

            return '<a class="oes-toggle-down-after oes-panel-header oes-accordion' .
                ($this->is_expanded ? ' active': '') . '" role="button">' .
                '<div class="oes-panel-title">' .
                '<span class="oes-caption-container">' . $caption . '</span>' .
                '</div>' .
                '</a>';
        }


        /**
         * Get the header caption.
         *
         * @return string Return the header caption.
         */
        function get_html_caption(): string
        {
            if(empty($this->caption) && !$this->force_header) return '';
            return $this->get_html_caption_prefix() .
                '<span class="oes-caption-title">' . $this->caption . '</span>' .
                '<span class="oes-toggle-down-after oes-toggle-icon"></span>';
        }


        /**
         * Get the html representation of the OES panel caption prefix.
         *
         * @return string Return the caption prefix.
         */
        function get_html_caption_prefix(): string
        {
            return '';
        }


        /**
         * Get the html representation of the OES panel content.
         *
         * @param string $content The content.
         * @return string Return the html representation of the OES panel content.
         */
        function get_html_content(string $content = ''): string
        {
            return $content;
        }


        /**
         * Get html representation of OES panel for pdf display.
         *
         * @param string $content The panel content.
         * @param array $args Additional parameters.
         * @return string Return the html representation of the OES panel.
         */
        function get_html_pdf(string $content = '', array $args = []): string
        {
            return '<div class="oes-pdf-panel-container oes-panel-container oes-panel-container-' . $this->type . '">' .
                '<div class="oes-panel-wrapper">' .
                '<div class="' . ($args['pdf_title_class'] ?? 'oes-pdf-panel-title') . '">' .
                '<span class="oes-caption-title">' . $this->get_html_caption_pdf() . '</span>' .
                '</div>' .
                '<div class="oes-pdf-panel-box">' . $this->get_html_content_pdf($content) . '</div>' .
                '</div>' .
                '</div>';
        }


        /**
         * Get the header caption for pdf display.
         *
         * @return string Return the header caption.
         */
        function get_html_caption_pdf(): string
        {
            return $this->get_html_caption();
        }


        /**
         * Get the html representation of the OES panel content for pdf display.
         *
         * @param string $content The content.
         * @return string Return the html representation of the OES panel content.
         */
        function get_html_content_pdf(string $content = ''): string
        {
            return $this->get_html_content($content);
        }
    }
}