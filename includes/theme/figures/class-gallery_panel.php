<?php

/**
 * @file
 * @reviewed 2.4.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Gallery_Panel')) {

    /**
     * Class OES_Gallery_Panel
     *
     * This class prepares an OES gallery panel.
     */
    class OES_Gallery_Panel extends OES_Panel
    {

        /** @var bool $add_number Add the figure numbers to panel caption. */
        protected bool $add_number = true;

        /** @var string $number_prefix The prefix for the figure numbers in caption. */
        protected string $number_prefix = '';

        /** @var array $numbers An array containing all image number for this gallery. */
        protected array $numbers = [];

        /** @var array $figures The figures of this gallery. */
        protected array $figures = [];

        /** @var array $prepared_data An array of prepared data containing the figures and figure information. */
        protected array $prepared_data = [];

        /** @var bool $add_modal Add the figure modal (as overlay popup). Default is true. */
        protected bool $add_modal = true;

        /** @var bool $add_slider Add slider to gallery (in panel and in overlay popup). Default is true. */
        protected bool $add_slider = true;

        /** @var string $figcaptionHTML The collected captions as string. */
        protected string $figcaptionHTML = '';

        /** @var string $carouselHTML The carousel HTML representation. */
        protected string $carouselHTML = '';

        /** @var string $tableHTML The collected table data (for modal). */
        protected string $tableHTML = '';

        /** @inheritdoc */
        protected function validate_parameters(): void
        {
            $this->type = 'gallery';
            $this->calculate_anchor_id();
            $this->validate_caption();
            $this->prepare_data();
        }

        /** @inheritdoc */
        protected function validate_caption(): void
        {
            if ($this->caption === 'none') {
                $this->caption = '';
            }
        }

        /** @inheritdoc */
        protected function get_html_content(string $content = ''): string
        {
            if (sizeof($this->prepared_data) < 1) {
                return '';
            }
            return oes_get_modal_gallery($this->prepared_data, [
                'image_html' => $this->get_image_html(),
                'modal_html' => $this->get_image_modal_html(),
                'figcaption_html' => $this->figcaptionHTML,
                'carousel_html' => $this->carouselHTML,
                'tables_html' => $this->tableHTML
            ]);
        }

        /** @inheritdoc */
        protected function get_html_caption_prefix(): string
        {
            if (!$this->add_number) {
                return '';
            }

            $numberString = '';
            if (!empty($this->numbers)) {
                sort($this->numbers);
                $numberString = $this->numbers[0];

                if (count($this->numbers) > 1) {
                    $numberString .= '-' . end($this->numbers);
                }
            }

            return '<span class="oes-panel-caption-text"><label>' .
                esc_html($this->number_prefix . $numberString) .
                '</label></span>';
        }

        /**
         * Prepare data by validating figures and gathering additional information for each figure.
         *
         * @return void
         */
        protected function prepare_data(): void
        {
            $first = true;

            foreach ($this->figures as $figure) {
                // Resolve figure to ACF attachment array if necessary
                if (is_numeric($figure)) {
                    $figure = acf_get_attachment($figure);
                } elseif (is_array($figure)) {
                    $figureID = $figure['ID'] ?? $figure['id'] ?? null;
                    if ($figureID) {
                        $figure = acf_get_attachment($figureID);
                    }
                }

                // Skip invalid or unresolved figures
                if (!is_array($figure) || empty($figure['ID'])) {
                    continue;
                }

                $modalData = \OES\Figures\oes_get_modal_image_data($figure);
                $this->prepared_data[] = [
                    'imageID' => $figure['ID'],
                    'image' => $figure,
                    'number' => $this->get_single_figure_number($figure),
                    'modal' => $modalData,
                    'subtitle' => ''
                ];

                $this->figcaptionHTML .= $this->get_image_figcaption_html($figure, $modalData, $first);
                $this->carouselHTML .= $this->get_image_gallery_carousel_item_html($figure, $first);

                if ($this->add_modal) {
                    $this->tableHTML .= $this->get_panel_image_modal_table_html(
                        $modalData,
                        $figure['ID'],
                        $first
                    );
                }

                $first = false;
            }
        }

        /**
         * Get the next figure number and store in global variable.
         *
         * @param array $figure
         * @return int
         */
        protected function get_single_figure_number(array $figure): int
        {
            global $oesListOfFigures, $post;

            /* get next number */
            $number = isset($oesListOfFigures[$post->ID]['number']) ?
                $oesListOfFigures[$post->ID]['number'] + 1 :
                1;

            /* add to global variable */
            if (intval($number)) $oesListOfFigures[$post->ID]['number'] = $number;

            $oesListOfFigures[$post->ID]['figures'][] = [
                'number' => $number,
                'figure' => $figure,
                'id' => $this->id,
                'type' => 'gallery'
            ];

            /* add to class variable */
            $this->numbers[] = $number;
            return $number;
        }

        /**
         * Get the image for panel as HTML string.
         *
         * @return string Return the image of panel as HTML string.
         */
        protected function get_image_html(): string
        {
            return oes_get_panel_image_HTML($this->figures[0],
                $this->add_modal,
                $this->add_slider,
                ['slider' => $this->get_gallery_panel_slider_html()]);
        }

        /**
         * Get the gallery panel slider as HTML representation.
         *
         * @return string Return the gallery panel slider as HTML representation.
         */
        protected function get_gallery_panel_slider_html(): string
        {
            return oes_get_gallery_panel_slider_HTML();
        }

        /**
         * Get the image modal as HTML string.
         *
         * @return string Return the image modal as HTML string.
         */
        protected function get_image_modal_html(): string
        {
            if (!isset($this->figures[0])) return '';
            return $this->add_modal ?
                oes_get_panel_image_modal_container_HTML($this->figures[0],
                    $this->tableHTML,
                    $this->add_slider,
                    ['slider' => $this->get_gallery_panel_slider_html()]) : '';
        }

        /**
         * Get the image figcaption as HTML string.
         *
         * @param array $figure A single figure.
         * @param array $imageModalData The image modal data (including caption and table data).
         * @param bool $active Indicating if active figcaption. Default is true.
         * @return string Return the image figcaption as HTML string.
         */
        protected function get_image_figcaption_html(array $figure, array $imageModalData = [], bool $active = true): string
        {
            return oes_get_panel_image_figcaption_HTML($figure, $imageModalData, $active);
        }

        /**
         * Get the image carousel item as HTML string.
         *
         * @param array $figure A single figure.
         * @param bool $active Indicating if active figcaption. Default is true.
         * @return string Return the image carousel item as HTML string.
         */
        protected function get_image_gallery_carousel_item_html(array $figure, bool $active = true): string
        {
            return oes_get_panel_gallery_carousel_item_HTML($figure, $active);
        }

        /**
         * Get the image modal table as HTML string.
         *
         * @param array $figure A single figure.
         * @param string $imageID The image ID.
         * @param bool $active Indicating if active figcaption. Default is true.
         * @return string Return the image modal table as HTML string.
         */
        protected function get_panel_image_modal_table_html(array $figure, string $imageID, bool $active = true): string
        {
            return oes_get_panel_image_modal_table_HTML($figure, $imageID, $active);
        }
    }
}