<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Panel')) {

    /**
     * Class OES_Panel
     *
     * Base class for rendering collapsible OES panels.
     */
    class OES_Panel
    {
        /** @var string Panel type, to be overridden by subclasses. */
        protected string $type = 'panel';

        /** @var string Unique panel ID (used as anchor). */
        protected string $id = '';

        /** @var string Caption to display in the panel header. */
        protected string $caption = '';

        /** @var bool Always display header, even if caption is empty. */
        protected bool $force_header = false;

        /** @var bool Whether the panel is expanded/open by default. */
        protected bool $is_expanded = true;

        /** @var bool Whether the panel is rendered in PDF mode. */
        protected bool $is_pdf_mode = false;

        /**
         * Constructor.
         *
         * @param array $args Optional panel arguments.
         */
        public function __construct(array $args = [])
        {
            foreach ($args as $key => $value) {
                if (property_exists($this, $key) && gettype($this->$key) === gettype($value)) {
                    $this->$key = $value;
                } elseif ($key === 'active') {
                    $this->is_expanded = $value;
                }
            }

            $this->validate_parameters();
            $this->set_additional_parameters($args);
        }

        /**
         * Validates input and prepares internal values.
         */
        protected function validate_parameters(): void
        {
            $this->calculate_anchor_id();
        }

        /**
         * Calculates ID from caption if not set.
         */
        protected function calculate_anchor_id(): void
        {
            if (empty($this->id) && !empty($this->caption)) {
                $id = preg_replace('/\s+/', '_', $this->caption);
                $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
                if (!empty($id)) {
                    $this->id = 'panel_' . strtolower($id);
                }
            }
        }

        /**
         * Allows subclasses to handle custom arguments.
         *
         * @param array $args Panel arguments.
         */
        protected function set_additional_parameters(array $args): void
        {
        }

        /**
         * Renders the panel HTML.
         *
         * @param string $content Panel inner HTML.
         * @param array $args Optional rendering options.
         * @return string
         */
        public function html(string $content = '', array $args = []): string
        {
            return $this->is_pdf_mode
                ? $this->get_html_pdf($content, $args)
                : $this->get_html($content);
        }

        /**
         * Standard (web) panel HTML output.
         *
         * @param string $content Panel content.
         * @return string
         */
        protected function get_html(string $content = ''): string
        {
            $header = $this->get_html_header();
            $expanded = empty($header) || $this->is_expanded;

            return sprintf(
                '<div class="oes-panel-container oes-panel-container-%s"%s>' .
                '<div class="oes-accordion-wrapper">%s' .
                '<div class="oes-accordion-panel oes-panel%s">%s</div>' .
                '</div></div>',
                esc_attr($this->type),
                $this->id ? ' id="' . esc_attr($this->id) . '"' : '',
                $header,
                $expanded ? ' active' : '',
                $this->get_html_content($content)
            );
        }

        /**
         * Panel header HTML.
         *
         * @return string
         */
        protected function get_html_header(): string
        {
            $caption = $this->get_html_caption();

            if (empty($caption) && !$this->force_header) {
                return '';
            }

            return sprintf(
                '<a class="oes-toggle-down-after oes-panel-header oes-accordion%s" role="button">' .
                '<div class="oes-panel-title"><span class="oes-caption-container">%s</span></div>' .
                '</a>',
                $this->is_expanded ? ' active' : '',
                $caption
            );
        }

        /**
         * Caption HTML inside the header.
         *
         * @return string
         */
        protected function get_html_caption(): string
        {
            if (empty($this->caption) && !$this->force_header) {
                return '';
            }

            return $this->get_html_caption_prefix() .
                '<span class="oes-caption-title">' . esc_html($this->caption) . '</span>' .
                '<span class="oes-toggle-down-after oes-toggle-icon"></span>';
        }

        /**
         * Optional caption prefix.
         *
         * @return string
         */
        protected function get_html_caption_prefix(): string
        {
            return '';
        }

        /**
         * Panel content HTML.
         *
         * @param string $content
         * @return string
         */
        protected function get_html_content(string $content = ''): string
        {
            return $content;
        }

        /**
         * PDF version of the panel.
         *
         * @param string $content
         * @param array $args
         * @return string
         */
        protected function get_html_pdf(string $content = '', array $args = []): string
        {
            $titleClass = $args['pdf_title_class'] ?? 'oes-pdf-panel-title';

            return sprintf(
                '<div class="oes-pdf-panel-container oes-panel-container oes-panel-container-%s">' .
                '<div class="oes-panel-wrapper">' .
                '<div class="%s"><span class="oes-caption-title">%s</span></div>' .
                '<div class="oes-pdf-panel-box">%s</div>' .
                '</div></div>',
                esc_attr($this->type),
                esc_attr($titleClass),
                $this->get_html_caption_pdf(),
                $this->get_html_content_pdf($content)
            );
        }

        /**
         * Caption for PDF.
         *
         * @return string
         */
        protected function get_html_caption_pdf(): string
        {
            return $this->get_html_caption();
        }

        /**
         * Panel content for PDF.
         *
         * @param string $content
         * @return string
         */
        protected function get_html_content_pdf(string $content = ''): string
        {
            return $this->get_html_content($content);
        }
    }
}
