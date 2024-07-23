<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Enqueue admin scripts for API.
 * @return void
 */
function admin_scripts(): void
{
    global $post;
    wp_register_script(
        'oes-api',
        plugins_url(OES_BASENAME . '/includes/api/assets/api-admin.min.js'),
        ['jquery'],
        false,
        true);
    wp_localize_script(
        'oes-api',
        'oesLodAJAX',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('oes_lod_nonce'),
            'post_id' => $post ? $post->ID : false
        ]
    );
    wp_enqueue_script('oes-api');

    wp_register_style('oes-api', plugins_url(OES_BASENAME . '/includes/api/assets/api.css'));
    wp_enqueue_style('oes-api');
}


/**
 * Enqueue scripts and styles for frontend display of API elements.
 * @return void
 */
function scripts(): void
{
    wp_register_script('oes-api',
        plugins_url(OES_BASENAME . '/includes/api/assets/api-frontend.min.js'),
        ['jquery'], false, true);
    wp_localize_script(
        'oes-api',
        'oesLodAJAX',
        [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('oes_lod_nonce')
        ]
    );
    wp_enqueue_script('oes-api');

    wp_register_style('oes-api', plugins_url(OES_BASENAME . '/includes/api/assets/api-frontend.css'));
    wp_enqueue_style('oes-api');
}


/**
 * Enqueue lod sidebar scripts for block editor to display LoD Sidebar.
 */
function sidebar_enqueue(): void
{
    wp_register_script(
        'oes-lod-sidebar',
        plugins_url(OES_BASENAME . '/includes/api/assets/lod-sidebar.min.js'),
        ['wp-plugins', 'wp-edit-post', 'wp-element']
    );
    wp_enqueue_script('oes-lod-sidebar');
}


/**
 * Initialize api interfaces.
 * @return void
 */
function initialize(): void
{
    /* add st*/
    /* apis */
    oes_include('api/class-api_interface.php');
    oes_include('api/gnd/class-gnd_interface.php');
    oes_include('api/geonames/class-geonames_interface.php');
    oes_include('api/loc/class-loc_interface.php');

    /* configs and schema */
    oes_include('api/class-lod.php');
    oes_include('api/class-schema_lod.php');

    $oes = OES();
    if (!empty($oes->apis))
        foreach ($oes->apis as $apiKey => $apiData) {
            oes_include('api/' . $apiKey . '/class-' . $apiKey . '.php');
            oes_include('api/' . $apiKey . '/class-schema_' . $apiKey . '.php');
            oes_include('api/' . $apiKey . '/functions-' . $apiKey . '.php');
        }

    add_action('add_meta_boxes', '\OES\API\lod_add_meta_box');
}


/**
 * Add meta box for the LOD search interface if LOD option is set for the post type.
 *
 * @param string $post_type The post type.
 * @return void
 */
function lod_add_meta_box(string $post_type): void
{
    $oes = OES();
    if (!empty($oes->apis) &&
        isset($oes->post_types[$post_type]) &&
        function_exists('\OES\API\lod_post_box') &&
        !oes_check_if_gutenberg($post_type))
        add_meta_box('oes-api',
            __('OES Linked Open Data Search', 'oes'),
            '\OES\API\lod_post_box',
            null,
            'side',
            'high'
        );
}


/**
 * Callback for LOD post box.
 * @return void
 */
function lod_post_box(): void
{
    global $post_type;
    $oes = OES();

    $availableApis = [];
    $prepareApis = $oes->apis;
    foreach ($prepareApis as $apiKey => $args) $availableApis[$apiKey] = $args;

    if (!empty($availableApis)) :
        ?>
        <div>
        <div class="oes-lod-meta-box-title"><?php

            /* get all api links */
            $apiLinks = [];
            foreach ($availableApis as $apiArgs)
                $apiLinks[] = '<a href="' . $apiArgs->database_link . '" target="_blank">' . $apiArgs->label . '</a>';
            printf(__('Search in the %s database and create shortcodes or copy values to this post.', 'oes'),
                implode(', ', $apiLinks)
            );
            ?></div>
        <div class="oes-lod-meta-box-options-wrapper">
            <a href="javascript:void(0)" class="oes-lod-meta-box-api-toggle oes-lod-meta-box-toggle"
               onClick="oesLodAdmin.toggleOptions()">
                <span><?php _e('Options', 'oes'); ?></span>
            </a>
            <div class="oes-lod-meta-box-api-options-container oes-lod-meta-box-options-container oes-collapsed">
                <div class="oes-lod-authority-file-container">
                    <div><label for="oes-lod-authority-file"><?php
                            _e('Authority File', 'oes'); ?></label></div>
                    <select id="oes-lod-authority-file" name="oes-lod-authority-file" class="oes-lod-search-options"
                            onchange="oesLodAdmin.searchOptions(this)"><?php
                        $authorityOptions = '';
                        foreach ($availableApis as $apiKey => $args)
                            $authorityOptions .= '<option value="' . $apiKey . '">' .
                                ($args->label ?? $apiKey) . '</option>';
                        echo $authorityOptions;
                        ?></select>
                </div><?php

                foreach ($availableApis as $apiKey => $args)
                    if (!empty($args->search_options))
                        foreach ($args->search_options as $option)
                            echo '<div' .
                                ($apiKey !== array_key_first($availableApis) ? ' style="display:none"' : '') . '>' .
                                $option['label'] . $option['form'] . '</div>';
                ?>
            </div>
        </div>
        <div class="oes-lod-meta-box-search-wrapper">
            <label for="oes-lod-search-input" class="screen-reader-text">LOD Search</label>
            <input id="oes-lod-search-input" type="text" placeholder="<?php
            _e('Type to search', 'oes'); ?>" value="">
            <a id="oes-admin-popup-frame-show" class="button-primary" href="javascript:void(0);"
               onClick="oesLodAdmin.apiRequest()"><?php
                _e('Look Up Value', 'oes'); ?></a>
        </div>
        <div class="oes-lod-result-shortcode">
            <div class="oes-lod-shortcode-title"><strong><?php
                    _e('Shortcode:', 'oes'); ?></strong></div>
            <div class="oes-lod-meta-box-shortcode-container-wrapper">
                <div class="oes-code-container " id="oes-lod-shortcode-container">
                    <div id="oes-lod-shortcode"><?php
                        _e('No entry selected.', 'oes'); ?></div>
                </div>
            </div>
        </div>
        <?php

        /* Copy To Post ----------------------------------------------------------------------------------------------*/
        if ($oes->post_types[$post_type]['lod'] ?? false):?>
            <div class="oes-lod-result-copy">
            <a href="javascript:void(0)" class="oes-lod-meta-box-copy-options oes-lod-meta-box-toggle"
               onClick="oesLodAdmin.toggleCopyOptions()">
                <span><?php _e('Copy Options', 'oes'); ?></span>
            </a>
            <div class="oes-lod-meta-box-copy-options-container oes-lod-meta-box-options-container">
                <ul class="oes-lod-options-list"></ul>
            </div>
            <div class="oes-lod-meta-box-copy-options-button"><?php
                if (\OES\Rights\user_is_read_only()):?>
                    <span class="oes-disable-button button-primary"><?php
                    _e('Copy to post', 'oes'); ?></span><?php
                else:?><a id="oes-lod-copy-to-post" class="button-primary" href="javascript:void(0);"
                          onClick="oesLodAdmin.copyToPost()"><?php
                    _e('Copy to post', 'oes'); ?></a><?php
                endif;
                ?></div>
            </div><?php endif; ?>
        <div id="oes-admin-popup-frame" class="oes-lod-frame">
            <div class="oes-admin-popup-frame-content" role="document">
                <button type="button" id="oes-admin-popup-frame-close" onClick="oesLodAdmin.hidePanel()"><span></span>
                </button>
                <div class="oes-admin-popup-title"><h1><?php _e('Results', 'oes'); ?></h1></div>
                <div class="oes-admin-popup-content">
                    <div class="oes-lod-results">
                        <div class="oes-admin-popup-information"><?php
                            _e('You can find results for your search in the table below. Click on the ' .
                                'icon to get further information from the selected database. Click on the link on the ' .
                                'right to ' .
                                'get to the database page. Select an entry by clicking on the checkbox on the left. ' .
                                'If the post type support the LOD feature "Copy to Post" you will find a list of copy ' .
                                'options on the right side. Select the options you want to copy to your post and ' .
                                'confirm by pressing the button.', 'oes');
                            ?>
                        </div>
                        <div class="oes-lod-results-table-wrapper">
                            <table id="oes-lod-results-table">
                                <thead>
                                <tr class="oes-lod-results-table-header">
                                    <th></th>
                                    <th><?php _e('Name', 'oes'); ?></th>
                                    <th class="oes-lod-results-table-header-type"><?php
                                        _e('Type', 'oes'); ?></th>
                                    <th><?php _e('ID', 'oes'); ?></th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="oes-lod-results-table-tbody"><!-- filled by js --></tbody>
                            </table>
                            <div class="oes-lod-results-spinner"><?php
                                echo oes_get_html_img(
                                    plugins_url(OES_BASENAME . '/assets/images/spinner.gif'),
                                    'waiting...',
                                    false,
                                    'oes-spinner'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="oes-admin-popup-frame-backdrop"></div>
        </div>
        </div><?php
    endif;
}


/**
 * Execute LOD admin query (search).
 * @return void
 */
function lod_search_query()
{
    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_lod_nonce'))
        die('Invalid nonce.' . var_export($_POST, true));

    $response = [];
    $apiKey = $_POST['param']['oes-lod-authority-file'];
    if (!empty($_POST['param']) && isset($_POST['param']['search_term']) && !empty($_POST['param']['search_term'])) {

        /* post request */
        if ($apiKey) {
            $class = '\\OES\\API\\' . $apiKey . '_API';
            $restAPI = class_exists($class) ? new $class() : new Rest_API();
            $response['response'] = $restAPI->get_data($_POST['param']);
        } else $response['response'] = json_encode(['error' => 'API Class not found.']);
    } else $response['response'] = json_encode(['error' => 'No search term.']);


    /* prepare copy to post options */
    $oes = OES();
    $post_type = $_POST['param']['post_type'] ?? null;
    $lodOptions = $oes->post_types[$post_type]['lod'] ?? false;
    $copyOptions = '';
    if ($lodOptions && $apiKey) {

        /* loop through fields and check if copy option is set for this field */
        $fieldOptions = $oes->post_types[$post_type]['field_options'] ?? false;
        if ($fieldOptions)
            foreach ($fieldOptions as $fieldKey => $fieldParams)
                foreach ($fieldParams as $paramKey => $param)
                    if ($paramKey === $apiKey . '_properties')
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

    if (!empty($copyOptions)) $response['copy_options'] = $copyOptions;

    /* add preview icon path */
    $iconPath = '/includes/api/' . $apiKey . '/icon_' . $apiKey . '.png';
    $response['icon_path'] = file_exists(OES_CORE_PLUGIN . $iconPath) ?
        plugins_url(OES_BASENAME . $iconPath) :
        plugins_url(OES_BASENAME . '/includes/api/assets/icon_lod_preview.png');

    /* prepare return value */
    header("Content-Type: application/json");
    echo json_encode($response);

    /* exit ajax function */
    exit();
}


/**
 * Add lod properties as post meta values.
 * @return void
 */
function lod_add_post_meta()
{

    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_lod_nonce'))
        die('Invalid nonce.' . var_export($_POST, true));

    /* prepare response */
    $response = [];
    $apiKey = $_POST['param']['oes-lod-authority-file'];
    if (!empty($_POST['param']) && $apiKey) {

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
                if (isset($fieldParams[$apiKey . '_properties']))
                    foreach ($fieldParams[$apiKey . '_properties'] as $apiOptionKey) {

                        /* check if new value for this field available, update and store if timestamp field found */
                        if ($apiOptionKey === 'base-timestamp') $timestampField = $fieldKey;
                        elseif (isset($_POST['param'][$apiOptionKey . '_value'])) {

                            /* prepare new value */
                            $newValue = false;
                            $fieldObjectOriginal = oes_get_field_object($fieldKey);

                            /* only relevant for acf pro fields */
                            //@oesDevelopment Multiple subfields - take only first one.
                            if ($fieldObjectOriginal['type'] === 'repeater')
                                $fieldObject = $fieldObjectOriginal['sub_fields'][0] ?? false;
                            else $fieldObject = $fieldObjectOriginal;

                            $rawValue = $_POST['param'][$apiOptionKey . '_value'];
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
                                    //@oesDevelopment What if multiple values are to be added at once
                                    $choices = $fieldObject['choices'];
                                    if (isset($choices[$rawValue])) $newValue = $rawValue;
                                    elseif (in_array(strtolower($rawValue), $choices))
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
                                    //@oesDevelopment Check for different time formats.
                                    $newValue = date($rawValue) ?? false;
                                    break;

                                case 'link' :
                                    //@oesDevelopment Check if title different from url.
                                    $newValue = [
                                        'title' => $rawValue,
                                        'url' => $rawValue,
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
                                if ($fieldObjectOriginal['type'] === 'repeater') {

                                    /* split value into new values */
                                    $repeaterValue = [];
                                    if (is_array($newValue))
                                        foreach ($newValue as $singleValue)
                                            $repeaterValue[] = [
                                                $fieldObject['name'] => $singleValue
                                            ];
                                    elseif (is_string($newValue)) {
                                        $splitValueString = preg_split("/\r\n|\n|\r/", $rawValue);
                                        foreach ($splitValueString as $singleValue)
                                            $repeaterValue[] = [
                                                $fieldObject['name'] => $singleValue
                                            ];
                                    }
                                    $updated = update_field($fieldKey, $repeaterValue, $_POST['post_id']);
                                } else $updated = update_field($fieldKey, $newValue, $_POST['post_id']);
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


/**
 * Execute LOD query for frontend box.
 */
function lod_box()
{

    /* validate nonce */
    if (!wp_verify_nonce($_POST['nonce'], 'oes_lod_nonce'))
        die('Invalid nonce.' . var_export($_POST, true));

    $response = [];
    $apiKey = $_POST['param']['api'] ?? false;
    if (!empty($_POST['param']['lodid'])) {

        /* post request */
        if ($apiKey) {
            $class = '\\OES\\API\\' . $apiKey . '_API';
            $restAPI = class_exists($class) ? new $class() : new Rest_API();
            $restAPI->get_data(['lodid' => $_POST['param']['lodid']]);
            $response['html'] = $restAPI->get_data_for_display();
            $response['id'] = $_POST['param']['lodid'];
        } else $response['response'] = json_encode(['error' => 'API Class not found.']);
    } else $response['response'] = json_encode(['No search term.']);

    /* prepare return value */
    header("Content-Type: application/json");
    echo json_encode($response);

    /* exit ajax function */
    exit();
}