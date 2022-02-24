<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* add shortcode */
add_shortcode('gndlink', '\OES\API\gnd_shortcode');

/**
 * Generate link text from shortcode.
 *
 * @param array $args The shortcode parameters.
 * @param string $content The shortcode content.
 * @return string Return the generated html link.
 */
function gnd_shortcode(array $args, string $content = ""): string
{
    /* get gnd object */
    if ($gndID = $args['id'] ?? false)
        return '<span class="gnd-container">' .
            sprintf('<a href="javascript:void(0)" class="oes-gndlink" data-gnd="%s">%s%s</a>',
                $gndID,
                $args['label'] ?? $gndID,
                oes_get_html_img(plugins_url(OES()->basename . '/includes/api/gnd/icon_gnd.gif'), 'gnd-icon')
            ). '</span>';
    else return $content;
}


/* action for ajax form in frontend */
add_action('wp_ajax_oes_gnd_box', '\OES\API\gnd_box');


/**
 * Execute LOBID query for gnd box.
 */
function gnd_box() {

    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_gnd_nonce_frontend'))
        die('Invalid nonce.' . var_export($_POST, true));

    $response = [];
    if (!empty($_POST['param'])) {

        /* post request */
        $gndAPI = new Lobid_API();
        $gndAPI->get_data(['gnd_id' => $_POST['param']]);
        $response['html'] = $gndAPI->get_data_for_display();
        $response['id'] = $_POST['param'];
    } else
        $response['response'] = 'No search term.';

    /* prepare return value */
    header("Content-Type: application/json");
    echo json_encode($response);

    /* exit ajax function */
    exit();
}


/**
 * Retrieve data information (id and label) from shortcode.
 *
 * @param string $shortcode The shortcode.
 * @return array Return data as ['label', 'id']
 */
function gnd_retrieve_data_from_shortcode(string $shortcode): array
{
    /* get label and id */
    preg_match('/label="([^\"]*)"/', $shortcode, $label);
    preg_match('/id="([^\"]*)"/', $shortcode, $id);

    /* prepare return */
    return [
        'label' => $label[1] ?? false,
        'id' => $id[1] ?? false
    ];
}


/**
 * Modified the displayed gnd data in frontend.
 * 
 * @param array $entry The GND entry.
 * @param array $types The GND types.
 * @return array Return the modified entry.
 */
function gnd_modify_table_data(array $entry, array $types): array
{
    $modifiedEntries = [];

    /* add preferred name and name variants (max 10) for all types */
    if (isset($entry['preferredName']))
        $modifiedEntries[] = [
            'label' => '<i class="fa fa-user"></i>',
            'value' => $entry['preferredName']['value']
        ];

    if (isset($entry['variantName'])) {

        /* resize array */
        $variantNames = $entry['variantName']['raw'];
        if (isset($entry['variantName']['raw']) && sizeof($entry['variantName']['raw']) > 10) {
            $variantNames = array_slice($entry['variantName']['raw'], 0, 10);
            $variantNames[] = '& more';
        }

        $modifiedEntries[] = [
            'label' => '<i class="fa fa-user"></i>',
            'value' => '<ul class="oes-gnd-box-list"><li>' .
                implode('</li><li>', $variantNames) . '</li></ul>'
        ];
    }


    /**
     * Person:
     * date + place of birth
     * date + place of death
     * profession or occupation
     */
    if (in_array('Person', $types)) {

        if (isset($entry['dateOfBirth']) || isset($entry['placeOfBirth']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-asterisk"></i>',
                'value' => (isset($entry['dateOfBirth']['value']) ?
                        date_format(date_create($entry['dateOfBirth']['raw'][0]), 'd.m.Y') :
                        '') .
                    (isset($entry['placeOfBirth']['value']) ?
                        (',<span class="oes-gnd-table-additional">' . $entry['placeOfBirth']['value'] . '</span>') :
                        '')
            ];

        if (isset($entry['dateOfDeath']) || isset($entry['placeOfDeath']))
            $modifiedEntries[] = [
                'label' => '<strong>†</strong>',
                'value' => (isset($entry['dateOfDeath']['value']) ?
                        date_format(date_create($entry['dateOfDeath']['raw'][0]), 'd.m.Y') :
                        '') .
                    (isset($entry['placeOfDeath']['value']) ?
                        (',<span class="oes-gnd-table-additional">' . $entry['placeOfDeath']['value'] . '</span>') :
                        '')
            ];

        if (isset($entry['professionOrOccupation']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-briefcase"></i>',
                'value' => $entry['professionOrOccupation']['value']
            ];
    } /**
     * Place:
     * latitude + longitude
     */
    elseif (in_array('PlaceOrGeographicName', $types)) {

        if (isset($entry['hasGeometry']['raw'][0][0])) {
            preg_match('#\((.*?)\)#', $entry['hasGeometry']['raw'][0][0], $coordinates);
            if (isset($coordinates[1]))
                $modifiedEntries[] = [
                    'label' => '<i class="fa fa-globe"></i>',
                    'value' => gnd_convert_coordinates(trim($coordinates[1]))
                ];
        }
    } /**
     * Conference:
     * date + place of conference
     * topic
     */
    elseif (in_array('ConferenceOrEvent', $types)) {

        if (isset($entry['dateOfConferenceOrEvent']) || isset($entry['placeOfConferenceOrEvent']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-calendar"></i>',
                'value' => (isset($entry['dateOfConferenceOrEvent']['value']) ?
                        date_format(date_create($entry['dateOfConferenceOrEvent']['raw'][0]), 'd.m.Y') :
                        '') .
                    (isset($entry['placeOfConferenceOrEvent']['value']) ?
                        (',<span class="oes-gnd-table-additional">' .
                            $entry['placeOfConferenceOrEvent']['value'] . '</span>') :
                        '')
            ];

        if (isset($entry['topic']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-tags"></i>',
                'value' => $entry['topic']['value']
            ];

    } /**
     * Work:
     * category
     * topic
     */
    elseif (in_array('Work', $types)) {

        if (isset($entry['gndSubjectCategory']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-tags"></i>',
                'value' => $entry['gndSubjectCategory']['value']
            ];

    } /**
     * Institution:
     * abbreviation
     * location
     * superior
     * homepage
     */
    elseif (in_array('CorporateBody', $types)) {

        if (isset($entry['abbreviatedNameForTheCorporateBody']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-user"></i>',
                'value' => $entry['abbreviatedNameForTheCorporateBody']['value']
            ];

        if (isset($entry['hierarchicalSuperiorOfTheCorporateBody']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-sitemap"></i>',
                'value' => $entry['hierarchicalSuperiorOfTheCorporateBody']['value']
            ];

        if (isset($entry['placeOfBusiness']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-map-marker"></i>',
                'value' => $entry['placeOfBusiness']['value']
            ];

        if (isset($entry['homepage']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-home"></i>',
                'value' => $entry['homepage']['value']
            ];

    } /**
     * Conference:
     * date + place of conference
     * topic
     */
    elseif (in_array('SubjectHeading', $types)) {

        if (isset($entry['gndSubjectCategory']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-tags"></i>',
                'value' => $entry['gndSubjectCategory']['value']
            ];
        if (isset($entry['broaderTermGeneral']))
            $modifiedEntries[] = [
                'label' => '<i class="fa fa-tags"></i>',
                'value' => $entry['broaderTermGeneral']['value']
            ];

    } elseif (in_array('Familie', $types)) {

    }


    /* add geographic information for all */
    if (isset($entry['geographicAreaCode']))
        $modifiedEntries[] = [
            'label' => '<i class="fa fa-map-marker"></i>',
            'value' => $entry['geographicAreaCode']['value']
        ];

    return $modifiedEntries;

}


/**
 * Display GND entry in box.
 *
 * @param string $title The title string.
 * @param string $table The table string.
 * @param string $image The html image string.
 * @param array $entry The considered entry.
 *
 * @return string Return the modified html display.
 */
function gnd_display_entry(string $title, string $table, string $image, array $entry): string
{

    $additionalInfo = '';
    if (isset($entry['biographicalOrHistoricalInformation']))
        $additionalInfo .= '<div><h4 class="oes-content-table-header4">' .
            __('Biographical or historical Information', 'oes') . '</h4>' .
            $entry['biographicalOrHistoricalInformation']['value'] . '</div>';

    if (isset($entry['definition']))
        $additionalInfo .= '<div><h4 class="oes-content-table-header4">' . __('Definition', 'oes') . '</h4>' .
            $entry['definition']['value'] . '</div>';

    if (isset($entry['id']))
        $additionalInfo .= '<div><h4 class="oes-content-table-header4">' .
            __('GND Link', 'oes') . '</h4>' .
            $entry['id']['value'] . '</div>';

    if (isset($entry['sameAs']))
        $additionalInfo .= '<h4 class="oes-content-table-header4">' .
            __('Further Links', 'oes') . '</h4>' . $entry['sameAs']['value'];

    return sprintf('<div class="oes-gnd-box-title"><h3 class="oes-content-table-header3">%s</h3></div>' .
        '<div class="oes-gnd-box-container-inner">' .
        '<div class="oes-gnd-box-image-inner">%s</div>' .
        '<div class="oes-gnd-box-content">%s</div>' .
        '</div>' .
        '<div class="oes-gnd-additional-info">%s</div>',
        $title,
        $image,
        $table,
        $additionalInfo
    );
}


/**
 * Convert coordinates from decimal to degree.
 *
 * @param string $points The coordinates as decimal string.
 * @return string Return the coordinates in degree.
 */
function gnd_convert_coordinates(string $points): string
{
    $coordinatesArray = [];
    $coordinates = explode(' ', $points);

    if (sizeof($coordinates) === 2)
        for ($i = 0; $i < 2; $i++) {
            $floatValue = floatval($coordinates[$i]);
            $d = intval($floatValue);
            $diff = abs($floatValue - $d);
            $m = intval(60 * $diff);
            $s = intval(3600 * $diff - 60 * $m);
            $coordinatesArray[] = sprintf('%s° %s′ %s″ %s', $d, $m, $s, ($i ? 'N' : 'W'));
        }

    return implode(' ', $coordinatesArray);
}
