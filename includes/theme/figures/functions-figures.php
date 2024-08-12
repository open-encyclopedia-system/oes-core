<?php

namespace OES\Figures;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Get the html representation of a modal of an image.
 *
 * @param array $image The image post as array.
 * @param array $args Additional parameters.
 */
function oes_get_modal_image(array $image, array $args = []): string
{
    if (!($image['ID'] ?? false)) return '';
    return oes_get_image_panel_content($image, $args);
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

    /**
     * Filters the image model table data.
     *
     * @param array $table The image model table data.
     * @param array $image The image.
     * @param array $args Additional parameters.
     */
    $table = apply_filters('oes/modal_image_data_table', oes_get_image_table_data($image), $image, $args);


    /**
     * Filters the image model caption.
     *
     * @param string $figcaption The modal caption.
     * @param array $image The image.
     * @param array $table The image model table data.
     * @param array $args Additional parameters.
     */
    $figcaption = apply_filters('oes/modal_image_data_caption', oes_get_image_figcaption($image), $image, $table, $args);


    /**
     * Filters the image model subtitle (text appearing before the table).
     *
     * @param array $table The image model table data.
     * @param array $image The image.
     * @param array $args Additional parameters.
     */
    $modalSubtitle = apply_filters('oes/modal_image_modal_subtitle', '', $table, $image, $args);


    return ['caption' => $figcaption, 'table' => $table, 'modal_subtitle' => $modalSubtitle];
}


/**
 * Get the image field according to the set image options.
 *
 * @param int|array $image The image ID or image post as array.
 *
 * @return string Return the image caption.
 */
function oes_get_image_field($image, string $option = 'title'): string
{
    if (is_int($image)) $image = acf_get_attachment($image);
    if (!is_array($image)) return '';

    global $oes, $oes_language;
    $fieldValue = '';
    if ($titleField = $oes->media_groups[$option] ?? false) {

        /* check if image attribute or image field */
        if (isset($image[$titleField])) $fieldValue = (($titleField === 'date' && !empty($image['date'] ?? '')) ?
            oes_convert_date_to_formatted_string($image['date']) :
            $image[$titleField]);
        else {

            /* modify field key */
            if ($oes_language !== 'language0' && isset($oes->media_groups['fields'][$titleField . '_' . $oes_language]))
                $titleField = $titleField . '_' . $oes_language;
            $fieldValue = oes_get_field_display_value($titleField, $image['ID']);
        }
    }
    return $fieldValue;
}


/**
 * Get the image figcaption according to the set image options.
 *
 * @param int|array $image The image ID or image post as array.
 *
 * @return string Return the image figcaption.
 */
function oes_get_image_figcaption($image): string
{
    if (is_int($image)) $image = acf_get_attachment($image);
    if (!is_array($image)) return '';

    $figcaption = '';
    if($title = oes_get_image_field($image))
        $figcaption = '<div class="oes-modal-figcaption">' . $title . '</div>';
    if($credit = oes_get_image_field($image, 'credit_label')) {
        $creditPrefix = '';
        if($creditPrefixItem = (OES()->media_groups['credit_text'] ?? false)) {
            $creditPrefixText = oes_get_language_label_from_string($creditPrefixItem);
            if(!empty($creditPrefixText))
                $creditPrefix = '<span class="oes-modal-figcaption-credit-prefix">' . $creditPrefixText . '</span>';
        }
        $figcaption .= '<div class="oes-modal-figcaption-credit">' . $creditPrefix . $credit . '</div>';
    }

    return $figcaption;
}


/**
 * Get the image table data according to the set image options.
 *
 * @param int|array $image The image ID or image post as array.
 *
 * @return array Return the image table data.
 */
function oes_get_image_table_data($image): array
{
    if (is_int($image)) $image = acf_get_attachment($image);
    if (!is_array($image)) return [];

    global $oes, $oes_language;
    $table = [];
    foreach ($oes->media_groups['show_in_panel'] ?? [] as $fieldKey) {

        /* check if image attribute or image field */
        if (isset($image[$fieldKey])) {

            /* get label */
            $label = $oes->media_groups['image'][$fieldKey][$oes_language] ?? '';
            if (empty($label))
                $label = [
                    'title' => 'Title',
                    'date' => 'Publication Date',
                    'caption' => 'Caption',
                    'description' => 'Description',
                    'alt' => 'Alternative Title'
                ][$fieldKey] ?? '';

            $value = (($fieldKey === 'date' && !empty($image['date'] ?? '')) ?
                oes_convert_date_to_formatted_string($image['date']) :
                $image[$fieldKey]);
        } else {

            /* modify field key */
            if ($oes_language !== 'language0' && isset($oes->media_groups['fields'][$fieldKey . '_' . $oes_language]))
                $fieldKey = $fieldKey . '_' . $oes_language;

            $fieldObject = oes_get_field_object($fieldKey);
            $label = $fieldObject['label_translation_' . $oes_language] ?? ($fieldObject['label'] ?? $fieldKey);
            foreach ($oes->media_groups['fields'] ?? [] as $mediaField)
                if (isset($mediaField[$oes_language]) &&
                    isset($mediaField['key']) &&
                    $mediaField['key'] === $fieldKey)
                    $label = $mediaField['label'];

            $value = '';
            if (!empty(oes_get_field($fieldKey, $image['ID'])))
                $value = oes_get_field_display_value($fieldKey, $image['ID']);
        }

        if (!empty($value)) $table[$fieldKey] = [
            'label' => $label,
            'value' => $value
        ];
    }

    return $table;
}