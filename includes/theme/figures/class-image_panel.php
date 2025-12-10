<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Image_Panel')) {

    /**
     * Class OES_Image_Panel
     *
     * This class prepares an OES image panel.
     */
    class OES_Image_Panel extends OES_Panel
    {

        /** @var bool $add_number Add the figure number to panel caption. Default is true. */
        protected bool $add_number = true;

        /** @var string $number The figure number. */
        protected string $number = '';

        /** @var string $number_prefix The prefix for the figure number. */
        protected string $number_prefix = '';

        /** @var int $figure_ID The figure (post) ID. */
        protected int $figure_ID = 0;

        /** @var array $figure The attachment array. */
        protected array $figure = [];

        /** @var bool $add_modal Add the figure modal (as overlay popup). Default is true. */
        protected bool $add_modal = true;

        /** @inheritdoc */
        protected function validate_parameters(): void
        {
            $this->type = 'image';

            if ($this->figure_ID && empty($this->figure)) {
                $figure = acf_get_attachment($this->figure_ID);
                if($figure) {
                    $this->figure = acf_get_attachment($this->figure_ID);
                }
            }

            $this->calculate_anchor_id();

            if ($this->add_number && empty($this->number)) {
                $this->set_number();
            }

            $this->validate_caption();
        }

        /** @inheritdoc */
        protected function calculate_anchor_id(): void
        {
            if (!empty($this->id)) return;

            if (!empty($this->caption) && $this->caption !== 'none') {
                $id = preg_replace('/\s+/', '_', $this->caption);
                $id = preg_replace('/[^a-zA-Z0-9_]/', '', oes_replace_umlaute($id));
                if (!empty($id)) {
                    $this->id = 'figure_' . strtolower($id);
                }
            } elseif ($this->figure_ID) {
                $this->id = 'figure_' . $this->figure_ID;
            }
        }

        /**
         * If caption is empty, try to get figure title. If caption is "none", set it to empty.
         */
        protected function validate_caption(): void
        {
            if (empty($this->caption)) {
                $this->caption = \OES\Figures\oes_get_image_field($this->figure);
            }

            if ($this->caption === 'none') {
                $this->caption = '';
            }
        }

        /**
         * Set the image number from global figure list.
         */
        protected function set_number(): void
        {
            if (is_admin()) {
                $this->number = '%';
                return;
            }

            global $oesListOfFigures, $post;

            if (!isset($post->ID)) return;

            $number = isset($oesListOfFigures[$post->ID]['number'])
                ? $oesListOfFigures[$post->ID]['number'] + 1
                : 1;

            if (intval($number)) {
                $oesListOfFigures[$post->ID]['number'] = $number;
            }

            $this->number = (string)$number;
        }

        /** @inheritdoc */
        protected function get_html_caption_prefix(): string
        {
            if ($this->number === 'none') {
                return '';
            }

            return '<span class="oes-panel-caption-text"><label>' .
                esc_html($this->number_prefix . $this->number) .
                '</label></span>';
        }

        /** @inheritdoc */
        protected function get_html_content(string $content = ''): string
        {
            $imageModalData = \OES\Figures\oes_get_modal_image_data($this->figure);

            $args = [
                'image_html'       => $this->get_image_html(),
                'modal_html'       => $this->get_image_modal_html($imageModalData),
                'figcaption_html'  => $this->get_image_figcaption_html($imageModalData),
            ];

            return oes_get_image_panel_content($this->figure, $args);
        }

        /** @inheritdoc */
        protected function get_html_content_pdf(string $content = ''): string
        {
            $url = esc_url($this->figure['url'] ?? '');
            $caption = esc_html($this->caption);

            return '<div class="oes-pdf-figure-box">' .
                '<div class="oes-pdf-image">' .
                '<img src="' . $url . '" alt="">' .
                '<div class="oes-pdf-text"><div class="oes-pdf-text-wrapper">' . $caption . '</div></div>' .
                '</div>' .
                '</div>';
        }

        /**
         * Get the image for panel as HTML string.
         *
         * @return string Return the image of panel as HTML string.
         */
        protected function get_image_html(): string
        {
            return oes_get_panel_image_HTML($this->figure, $this->add_modal);
        }

        /**
         * Get the image modal as HTML string.
         *
         * @param array $imageModalData The image modal data (including caption and table data).
         * @return string Return the image modal as HTML string.
         */
        protected function get_image_modal_html(array $imageModalData = []): string
        {
            return $this->add_modal ? oes_get_panel_image_modal_HTML($this->figure, $imageModalData) : '';
        }

        /**
         * Get the image figcaption as HTML string.
         *
         * @param array $imageModalData The image modal data (including caption and table data).
         * @return string Return the image figcaption as HTML string.
         */
        protected function get_image_figcaption_html(array $imageModalData = []): string
        {
            return oes_get_panel_image_figcaption_HTML($this->figure, $imageModalData);
        }
    }
}