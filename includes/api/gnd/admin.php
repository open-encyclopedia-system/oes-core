<?php

namespace OES\API;

use function OES\ACF\oes_get_field;
use function OES\ACF\oes_get_field_object;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/* action for ajax form in post box */
add_action('wp_ajax_oes_gnd_search_query', '\OES\API\gnd_search_query');


/**
 * Execute LOBID query for gnd entries.
 */
function gnd_search_query()
{
    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_gnd_nonce'))
        die('Invalid nonce.' . var_export($_POST, true));

    $response = [];
    if (!empty($_POST['param']) && isset($_POST['param']['search_term']) && !empty($_POST['param']['search_term'])) {

        /* prepare options */
        $options = [];
        if (isset($_POST['param']['size'])) $options['size'] = $_POST['param']['size'];
        if (isset($_POST['param']['type']) && $_POST['param']['type'] != 'all')
            $options['filter'] = 'type:' . $_POST['param']['type'];

        /* post request */
        $gndAPI = new Lobid_API();
        $response['response'] = $gndAPI->get_data([
            'search_term' => $_POST['param']['search_term'],
            'options' => $options,
            'field' => $_POST['param']['field'] ?? false
        ]);
    } else $response['response'] = json_encode(['error' => 'No search term.']);


    /* prepare copy to post options */
    $post_type = $_POST['param']['post_type'] ?? null;
    $oes = OES();

    $lodOptions = $oes->post_types[$post_type]['lod_box'] ?? ['shortcode'];

    $availableApis = [];
    $prepareApis = $oes->apis;
    foreach ($prepareApis as $apiKey => $args) {
        $availableApis[$apiKey] = $args;
        $availableApisLabels[] = $args->label ?? $apiKey;
    }

    $copyOptions = '';
    if(in_array('post', $lodOptions)){

        $fieldOptions = $oes->post_types[$post_type]['field_options'] ?? false;
        if ($fieldOptions)
            foreach ($availableApis as $apiKey => $args)
                if (!empty($args->copy_options))
                    foreach ($fieldOptions as $fieldKey => $fieldParams)
                        foreach ($fieldParams as $paramKey => $param)
                            if (in_array($paramKey, $args->copy_options))
                                foreach ($param as $apiFieldKey)
                                    if ($apiFieldKey != 'base-timestamp') {

                                        /* get field object */
                                        $fieldObject = oes_get_field_object($fieldKey);
                                        $copyOptions .= '<li class="oes-lod-copy-option oes-lod-copy-option-' .
                                            $apiKey . '">' .
                                            oes_html_get_form_element(
                                                'checkbox',
                                                'oes-lod-' . $apiKey . '-copy[' . $apiFieldKey . '][' . $fieldKey . ']',
                                                $apiFieldKey,
                                                false,
                                                [
                                                    'class' => 'oes-' . $apiKey . '-field-checkbox',
                                                    'label' => ($fieldObject ? $fieldObject['label'] : $fieldKey)
                                                ]
                                            ) . ': <span id="' . $apiFieldKey .
                                            '_value" class="oes-lod-copy-value">-</span></li>';
                                    }

    }

    if(!empty($copyOptions)) $response['copy_options'] = $copyOptions;

    /* prepare return value */
    header("Content-Type: application/json");
    echo json_encode($response);

    /* exit ajax function */
    exit();
}


/* action for ajax form in post box */
add_action('wp_ajax_oes_gnd_add_post_meta', '\OES\API\gnd_add_post_meta');


/**
 * Add gnd properties as post meta values.
 */
function gnd_add_post_meta()
{

    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_gnd_nonce'))
        die('Invalid nonce.' . var_export($_POST, true));

    /* prepare response */
    $response = [];
    if (!empty($_POST['param'])) {

        /* exit early if post is not publish or draft */
        if (!in_array(get_post($_POST['post_id'])->post_status, ['publish', 'draft']))
            $response['error'] = 'Post must be published or saved as draft.';
        elseif (!empty($_POST['post_id']) && $post = get_post($_POST['post_id'])) {

            /* get field options */
            $fieldOptions = OES()->post_types[$post->post_type]['field_options'] ?? [];

            /* check for matching fields */
            $timestamp = [];
            $timestampField = false;
            foreach ($fieldOptions as $fieldKey => $fieldParams)
                if (isset($fieldParams['gnd_properties']))
                    foreach ($fieldParams['gnd_properties'] as $gndKey) {

                        /* check if new value for this field available, update and store if timestamp field found */
                        if ($gndKey === 'base-timestamp') $timestampField = $fieldKey;
                        elseif (isset($_POST['param'][$gndKey . '_value'])) {

                            /* prepare new value */
                            $newValue = false;
                            $fieldObjectOriginal = oes_get_field_object($fieldKey);

                            /* only relevant for acf pro fields TODO @nextRelease: multiple subfields - take only first one */
                            if ($fieldObjectOriginal['type'] === 'repeater')
                                $fieldObject = $fieldObjectOriginal['sub_fields'][0] ?? false;
                            else $fieldObject = $fieldObjectOriginal;

                            $rawValue = $_POST['param'][$gndKey . '_value'];
                            switch ($fieldObject['type']) {

                                case 'text':
                                case 'textarea':
                                case 'wysiwyg' :
                                case 'email' :
                                case 'url' :
                                    $newValue = $rawValue;
                                    break;

                                case 'number' :
                                case 'range' :
                                    $newValue = intval($rawValue) ?? false;
                                    break;

                                case 'true_false' :
                                    $newValue = (bool)$rawValue;
                                    break;

                                case 'checkbox' :
                                case 'radio' :
                                case 'select' :
                                    //TODO @nextRelease: what if multiple values are to be added at once
                                    $choices = $fieldObject['choices'];
                                    if(isset($choices[$rawValue])) $newValue = $rawValue;
                                    elseif(in_array(strtolower($rawValue), $choices))
                                        $newValue = array_search(strtolower($rawValue), $choices);
                                    break;

                                case 'taxonomy':

                                    /* get new value */
                                    $prepareValue = preg_split("/\r\n|\n|\r/", $rawValue);

                                    /* loop through all values */
                                    if (!empty($prepareValue))
                                        foreach ($prepareValue as $singleValue) {

                                            /* check the term if it does not already exist */
                                            $termID = get_term_by('name',
                                                    $singleValue,
                                                    $fieldObject['taxonomy'])->term_id ??
                                                false;

                                            if (!$termID)
                                                $termID = wp_insert_term($singleValue,
                                                        $fieldObject['taxonomy'])['term_id'] ?? false;

                                            /* prepare the new value */
                                            if ($termID)
                                                if ($fieldObject['field_type'] === 'multi_select' ||
                                                    $fieldObjectOriginal['type'] === 'repeater') $newValue[] = $termID;
                                                else {

                                                    /* exit early after the first value if field does not allow multiple
                                                    values */
                                                    $newValue = $termID;
                                                    break;
                                                }
                                        }
                                    break;

                                case 'date_picker' :
                                case 'date_time_picker' :
                                case 'time_picker' :
                                    //TODO @nextRelease: check for different time formats
                                    $newValue = date($rawValue) ?? false;
                                    break;

                                case 'link' :
                                    //TODO @nextRelease: check if title different from url
                                    $newValue = [
                                        'title' => $rawValue,
                                        'url' => $newValue,
                                        'target' => '_blank'
                                    ];
                                    break;

                                case 'post_object' :
                                case 'relationship' :
                                case 'image' :
                                case 'file' :
                                case 'password' :
                                case 'google_map' :
                                default :
                                    $newValue = false;
                                    break;
                            }

                            $updated = false;
                            if ($newValue)
                                if ($fieldObjectOriginal['type'] === 'repeater'){

                                    /* split value into new values */
                                    $repeaterValue = [];
                                    if(is_array($newValue))
                                    foreach($newValue as $singleValue)
                                        $repeaterValue[] = [
                                            $fieldObject['name'] => $singleValue
                                        ];
                                    elseif(is_string($newValue)){
                                        $splitValueString = preg_split("/\r\n|\n|\r/", $rawValue);
                                        foreach($splitValueString as $singleValue)
                                            $repeaterValue[] = [
                                                $fieldObject['name'] => $singleValue
                                            ];
                                    }
                                    $updated = update_field($fieldKey, $repeaterValue, $_POST['post_id']);
                                }
                                else $updated = update_field($fieldKey, $newValue, $_POST['post_id']);
                            if ($updated) $timestamp[] = $fieldKey . ':' . $rawValue;
                        }
                    }

            /* update timestamp */
            if ($timestampField && !empty($timestamp)) {
                $oldValue = oes_get_field($timestampField, $_POST['post_id']);
                $newValue = date('d.m.Y h:i:s') .
                    " Update fields:\r\n" . implode(",\r\n", $timestamp) .
                    (empty($oldValue) ? '' : "\r\n\r\n") . $oldValue;
                update_field($timestampField, $newValue, $_POST['post_id']);
            }
        } else $response['error'] = 'Post ID missing.';
    } else $response['error'] = 'Error.';

    /* prepare return value */
    header("Content-Type: application/json");
    echo json_encode($response);

    /* exit ajax function */
    exit();
}