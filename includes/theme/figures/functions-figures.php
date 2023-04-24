<?php

namespace OES\Figures;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\ACF\get_field_display_value;
use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;


/**
 * Get the html representation of a modal of an image.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 */
function oes_get_modal_image(array $image, array $args = []): string
{

    $modalHTML = '';
    if ($image['ID']) {

        /* prepare image data ----------------------------------------------------------------------------------------*/
        $imageModalData = isset($args['text-callback']) ?
            (isset($args['text-callback-args']) ?
                call_user_func_array($args['text-callback'], [$image, $args['text-callback-args']]) :
                call_user_func($args['text-callback'], $image)) :
            oes_get_modal_image_data($image);

        /* modal image -----------------------------------------------------------------------------------------------*/
        $imagePathArray = wp_get_attachment_image_src($image['ID'], 'full');
        $imagePath = $imagePathArray[0] ?? $image['url'];

        $expandIcon = '<span class="oes-expand-button oes-icon"></span>';


        /**
         * Filters the expand icon
         *
         * @param string $expandIcon The expand icon.
         * @param array $image The image.
         */
        if (has_filter('oes/modal_image_expand_image'))
            $expandIcon = apply_filters('oes/modal_image_expand_image', $expandIcon, $image);


        /* modal toggle */
        $modalToggle = '<div class="oes-modal-toggle">' .
            '<div class="oes-modal-toggle-container">' .
            '<img src="' . ($imagePath ?? '') . '" alt="' . ($image['alt'] ?? 'empty') . '">' .
            $expandIcon .
            '</div>' .
            '</div>';

        /* table */
        $tableRows = '';
        if (!empty($imageModalData['table'] ?? []))
            foreach ($imageModalData['table'] as $description => $value)
                $tableRows .= sprintf('<tr><th>%s</th><td>%s</td></tr>', $description, $value);
        $table = empty($tableRows) ? '' :
            '<div class="oes-modal-content-text"><div>' .
            ($imageModalData['modal_subtitle'] ?? '') .
            '<table class="oes-table-pop-up">' . $tableRows . '</table></div></div>';

        /* modal */
        $modal = '<div class="oes-modal-container">' .
            '<button class="oes-modal-close btn"></button>' .
            '<div class="oes-modal-image-container">' .
            '<img alt="' . ($image['alt'] ?? 'empty') . '" src="">' .
            '</div>' . $table .
            '</div>';

        /* prepare image modal */
        $modalHTML = '<figure class="oes-expand-image ' . ($args['figure-class'] ?? '') .
            '" id="' . (isset($args['figure-id']) ? ' id="' . $args['figure-id'] . '"' : '') . '">' .
            $modalToggle . $modal .
            '<figcaption>' . ($imageModalData['caption'] ?? '') . '</figcaption>' .
            '</figure>';
    }

    return $modalHTML;
}


/**
 * Get the text for a figure panel according to the set image options.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 *
 * @return array Return array of text, raw text and table representation of the text of the figure.
 */
function oes_get_modal_image_data(array $image, array $args = []): array
{
    /* get global OES instance parameter */
    global $oes, $oes_language;
    $caption = $oes->media_groups['credit_text'] ?? false;
    $creditText = $oes->media_groups['credit_label'] ?? '';
    $showInPanel = $oes->media_groups['show_in_panel'] ?? [];

    /* prepare information for modal panel ---------------------------------------------------------------------------*/
    if ($creditText) {

        /* check if image attribute or image field */
        if (isset($image[$creditText])) $caption .= ($creditText === 'date' ?
            date("j F Y", strtotime(str_replace('/', '-', $image['date']))) :
            $image[$creditText]);
        else $caption .= get_field_display_value($creditText, $image['ID']);
    }

    $table = [];
    if (!empty($showInPanel))
        foreach ($showInPanel as $fieldKey) {

            /* check if image attribute or image field */
            if (isset($image[$fieldKey])) {

                /* get label */
                $label = '';
                if(isset($oes->media_groups['image'][$fieldKey][$oes_language]))
                    $label = $oes->media_groups['image'][$fieldKey][$oes_language];

                if(empty($label)){
                    $labelMatch = [
                        'title' => 'Title',
                        'date' => 'Publication Date',
                        'caption' => 'Caption',
                        'description' => 'Description',
                        'alt' => 'Alternative Title'
                    ];
                    $label = $labelMatch[$fieldKey] ?? '';
                }

                $value = ($fieldKey === 'date' ?
                    date_i18n(get_option('date_format') ?? "j F Y", strtotime(str_replace('/', '-', $image['date'])))  :
                    $image[$fieldKey]);
            } else {

                $label = oes_get_field_object($fieldKey)['label'] ?? $fieldKey;
                if(isset($oes->media_groups['fields'])){
                    foreach($oes->media_groups['fields'] as $mediaField){
                        if(isset($mediaField[$oes_language]) &&
                            isset($mediaField['key']) &&
                            $mediaField['key'] === $fieldKey)
                            $label = $mediaField['label'];
                    }
                }

                $value = '';
                if(!empty(oes_get_field($fieldKey, $image['ID'])))
                    $value = get_field_display_value($fieldKey, $image['ID']);
            }

            if (!empty($value)) $table[$label] = $value;
        }


    /**
     * Filters the image model table data.
     *
     * @param array $table The image model table data.
     * @param array $image The image.
     * @param array $args Additional parameters.
     */
    if (has_filter('oes/modal_image_data_table'))
        $table = apply_filters('oes/modal_image_data_table', $table, $image, $args);


    /**
     * Filters the image model caption.
     *
     * @param string $title The modal caption.
     * @param array $table The image model table data.
     * @param array $image The image.
     * @param array $args Additional parameters.
     */
    if (has_filter('oes/modal_image_data_caption'))
        $caption = apply_filters('oes/modal_image_data_caption', $caption, $image, $table, $args);


    /**
     * Filters the image model subtitle (text appearing before the table).
     *
     * @param array $table The image model table data.
     * @param array $image The image.
     * @param array $args Additional parameters.
     */
    $modalSubtitle = '';
    if (has_filter('oes/modal_image_modal_subtitle'))
        $modalSubtitle = apply_filters('oes/modal_image_modal_subtitle', $table, $image, $args);


    return ['caption' => $caption, 'table' => $table, 'modal_subtitle' => $modalSubtitle];
}