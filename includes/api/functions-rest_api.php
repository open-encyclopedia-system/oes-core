<?php

namespace OES\API;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Enqueue admin scripts for API.
 * @return void
 */
function admin_scripts(): void
{
    wp_register_style('oes-api', plugins_url(OES_BASENAME . '/includes/api/assets/api.css'));
    wp_enqueue_style('oes-api');

    wp_enqueue_script(
        'oes-lod-app',
        plugins_url(OES_BASENAME . '/includes/api/assets/lod-app' . oes_minify() . '.js'),
        [
            'wp-plugins',
            'wp-edit-post',
            'wp-element',
            'wp-components',
            'wp-i18n',
            'wp-api-fetch'
        ],
        '1.0',
        true
    );
    wp_set_script_translations('oes-lod-app', 'oes');
}

/**
 * Enqueue scripts and styles for frontend display of API elements.
 * @return void
 */
function scripts(): void
{
    wp_register_script('oes-api',
        plugins_url(OES_BASENAME . '/includes/api/assets/api-frontend' . oes_minify() . '.js'),
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
 * Initialize api interfaces.
 * @return void
 */
function initialize(): void
{
    include_once __DIR__ . '/class-api_interface.php';

    include_once __DIR__ . '/gnd/class-gnd_interface.php';
    include_once __DIR__ . '/geonames/class-geonames_interface.php';
    include_once __DIR__ . '/ror/class-ror_interface.php';
    include_once __DIR__ . '/orcid/class-orcid_interface.php';
    include_once __DIR__ . '/loc/class-loc_interface.php';

    include_once __DIR__ . '/class-lod_config.php';
    include_once __DIR__ . '/class-lod_schema.php';

    foreach (OES()->apis ?? [] as $apiKey => $apiData) {

        $pathPrefix = __DIR__ . '/' . strtolower($apiKey) . '/';
        $files = [
            "class-$apiKey.php",
            "class-{$apiKey}_api.php",
            "class-{$apiKey}_display_helper.php",
            "functions-$apiKey.php",
        ];

        foreach ($files as $file) {
            $path = $pathPrefix . $file;
            if (file_exists($path)) {
                include_once $path;
            }
        }

        if ($apiData->schema ?? false) {

            $schemaPath = $pathPrefix . "class-{$apiKey}_schema.php";
            if (file_exists($schemaPath)) {
                include_once $schemaPath;
            } else {
                \OES\Admin\Tools\register_tool('\OES\Admin\Tools\LOD_Schema', "schema-$apiKey", ['api_key' => $apiKey]);
            }
        }
    }

    include_once __DIR__ . '/functions-meta_box.php';
    add_action('add_meta_boxes', __NAMESPACE__ . '\\lod_add_meta_box', 10, 2);
}

/**
 * Execute LOD API Request.
 *
 * @param $request
 * @return array|\WP_Error
 */
function rest_lod_search($request)
{

    $params = $request->get_param('param');
    $apiKey = $params['authority_file'] ?? '';
    $searchTerm = $params['search_term'] ?? '';

    if (!$apiKey || empty($searchTerm)) {
        return new \WP_Error('no_search_term', 'Please provide a search term.', ['status' => 400]);
    }

    $class = "\\OES\\API\\{$apiKey}_API";
    $api = class_exists($class) ? new $class() : new Rest_API();
    $responseData = $api->get_data($params);

    $copyOptionsArray = [];
    $oes = OES();
    $postType = $params['post_type'] ?? null;
    $lodOptions = $oes->post_types[$postType]['lod'] ?? [];
    $fieldOptions = $oes->post_types[$postType]['field_options'] ?? [];

    if ($lodOptions) {
        foreach ($fieldOptions as $fieldKey => $fieldParams) {
            foreach ($fieldParams as $paramKey => $param) {

                if ($paramKey != $apiKey . '_properties') {
                    continue;
                }

                if (!is_array($param)) {
                    continue;
                }

                foreach ($param as $lodKey) {
                    if ($lodKey === 'base-timestamp') {
                        continue;
                    }

                    $fieldObject = oes_get_field_object($fieldKey);

                    $copyOptionsArray[$fieldKey . ':' . $lodKey] = $fieldObject['label'] ?? $fieldKey;
                }
            }
        }
    }

    $iconPath = '/includes/api/' . $apiKey . '/icon_' . $apiKey . '.png';
    $iconURL = file_exists(OES_CORE_PLUGIN . $iconPath)
        ? plugins_url(OES_BASENAME . $iconPath)
        : plugins_url(OES_BASENAME . '/includes/api/assets/icon_lod_preview.png');

    return [
        'response' => $responseData,
        'icon_path' => $iconURL,
        'copy_options' => $copyOptionsArray
    ];
}

/**
 * Copy selected data to post.
 * @param $request
 * @return true|\WP_Error
 */
function rest_copy_to_post($request)
{

    $currentPostID = $request->get_param('post_id');

    if (!$currentPostID) {
        return new \WP_Error('missing_post_id', __('Missing post ID.', 'oes'), ['status' => 400]);
    }

    $currentPost = get_post($currentPostID);

    if (!$currentPost) {
        return new \WP_Error('invalid_post', __('Invalid post.', 'oes'), ['status' => 400]);
    }

    if (!current_user_can('edit_post', $currentPostID)) {
        return new \WP_Error('permission_denied', __('Permission denied.', 'oes'), ['status' => 400]);
    }

    if (!in_array($currentPost->post_status, ['publish', 'draft'], true)) {
        return new \WP_Error('post_not_published', __('Post must be draft or published.', 'oes'), ['status' => 400]);
    }

    $apiKey = $request->get_param('authority_file');

    if (!$apiKey) {
        return new \WP_Error('missing_api_key', __('Missing API key.', 'oes'), ['status' => 400]);
    }

    $params = $request->get_param('param');

    if (empty($params)) {
        return new \WP_Error('missing_param', __('Missing parameters.', 'oes'), ['status' => 400]);
    }

    $timestampField = false;

    $fieldOptions = OES()->post_types[$currentPost->post_type]['field_options'] ?? [];

    if (!$fieldOptions) {
        return true;
    } else {
        foreach ($fieldOptions as $fieldKey => $fieldParams) {
            $properties = $fieldParams[$apiKey . '_properties'] ?? null;

            if (is_array($properties) && in_array('base-timestamp', $properties, true)) {
                $timestampField = $fieldKey;
                break;
            }
        }
    }

    $timestamp = [];
    foreach ($params as $key => $rawValue) {

        if (!str_contains($key, ':')) {
            continue;
        }

        [$fieldKey, $apiOptionKey] = array_pad(explode(':', $key), 2, $key);

        if ($apiOptionKey === 'base-timestamp') {
            $timestampField = $fieldKey;
            continue;
        }

        if (empty($rawValue)) {
            continue;
        }

        $fieldObjectOriginal = oes_get_field_object($fieldKey);

        if (!$fieldObjectOriginal) {
            continue;
        }

        $fieldObject = $fieldObjectOriginal;

        if ($fieldObjectOriginal['type'] === 'repeater') {
            $fieldObject = $fieldObjectOriginal['sub_fields'][0] ?? null;
        }

        if (!$fieldObject) {
            continue;
        }

        $newValue = prepare_lod_field_value($fieldObject, $rawValue);

        if ($newValue === false) {
            continue;
        }

        if ($fieldObjectOriginal['type'] === 'repeater') {

            $repeaterValue = [];

            $values = is_array($newValue) ? $newValue : [$newValue];

            foreach ($values as $value) {
                $repeaterValue[] = [
                    $fieldObject['name'] => $value
                ];
            }

            $updated = update_field($fieldKey, $repeaterValue, $currentPostID);

        } else {
            $updated = update_field($fieldKey, $newValue, $currentPostID);
        }

        if ($updated) {
            $collectValue = is_string($newValue) ? $newValue : json_encode($newValue);
            $timestamp[] = $fieldKey . ':' . $collectValue;
        }
    }

    if ($timestampField && $timestamp) {

        $oldValue = oes_get_field($timestampField, $currentPostID);

        $newValue =
            current_time('d.m.Y H:i:s') .
            " Update fields:\n" .
            implode(",\n", $timestamp) .
            ($oldValue ? "\n\n" . $oldValue : '');

        update_field($timestampField, $newValue, $currentPostID);
    }

    return true;
}

/**
 * Prepare a value for an ACF field based on field type.
 *
 * @param array $fieldObject ACF field configuration.
 * @param mixed $rawValue Raw value retrieved from LOD API.
 *
 * @return mixed|false Prepared value or false if invalid.
 */
function prepare_lod_field_value(array $fieldObject, $rawValue)
{
    $type = $fieldObject['type'] ?? '';

    switch ($type) {

        case 'text':
        case 'textarea':
        case 'wysiwyg':
        case 'email':
        case 'url':
            if (is_string($rawValue)) {
                return sanitize_text_field($rawValue);
            }

            if (is_array($rawValue)) {
                return implode(', ', array_map(function ($val) {
                    return is_string($val) ? sanitize_text_field($val) : '';
                }, $rawValue));
            }

            return '';

        case 'number':
        case 'range':
            return is_numeric($rawValue) ? (int)$rawValue : false;

        case 'true_false':
            return filter_var($rawValue, FILTER_VALIDATE_BOOLEAN);

        case 'checkbox':
        case 'radio':
        case 'select':
            //@oesDevelopment What if multiple values are to be added at once
            $choices = $fieldObject['choices'] ?? [];

            if (isset($choices[$rawValue])) {
                return $rawValue;
            }

            foreach ($choices as $key => $label) {
                if (strtolower($label) === strtolower($rawValue)) {
                    return $key;
                }
            }

            return false;

        case 'taxonomy':

            $taxonomy = $fieldObject['taxonomy'] ?? null;

            if (!$taxonomy) {
                return false;
            }

            $values = preg_split("/\r\n|\n|\r|;|,/", $rawValue);
            $values = array_filter(array_map('trim', $values));

            if (!$values) {
                return false;
            }

            $termIDs = [];

            foreach ($values as $singleValue) {

                $term = get_term_by('name', $singleValue, $taxonomy);

                if (!$term) {
                    $created = wp_insert_term($singleValue, $taxonomy);

                    if (is_wp_error($created)) {
                        continue;
                    }

                    $termIDs[] = $created['term_id'];
                } else {
                    $termIDs[] = $term->term_id;
                }
            }

            if (!$termIDs) {
                return false;
            }

            if (($fieldObject['field_type'] ?? '') === 'multi_select') {
                return $termIDs;
            }

            return $termIDs[0];

        case 'date_picker':
        case 'date_time_picker':
        case 'time_picker':
            //@oesDevelopment Check for different time formats.
            $timestamp = strtotime($rawValue);
            return $timestamp ? date('Y-m-d H:i:s', $timestamp) : false;

        case 'link':
            //@oesDevelopment Check if title different from url.
            $url = esc_url_raw($rawValue);

            if (!$url) {
                return false;
            }

            return [
                'title' => $url,
                'url' => $url,
                'target' => '_blank'
            ];

        case 'post_object' :
        case 'relationship' :
        case 'image' :
        case 'file' :
        case 'password' :
        case 'google_map' :
        default:
            return false;
    }
}

/**
 * Execute LOD query for frontend box.
 */
function lod_box(): void
{
    if (!check_ajax_referer('oes_lod_nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce.');
    }

    $params = isset($_POST['param']) ? (array)$_POST['param'] : [];

    $apiKey = sanitize_key($params['api'] ?? '');
    $id = sanitize_text_field($params['lod_id'] ?? '');
    $boxID = sanitize_text_field($params['box_id'] ?? '');

    if (!$apiKey || !$id) {
        wp_send_json_error('Missing parameters.');
    }

    if (!isset(OES()->apis[$apiKey])) {
        wp_send_json_error('Unknown API.');
    }

    $apiClass = "\\OES\\API\\{$apiKey}_API";

    if (!class_exists($apiClass)) {
        wp_send_json_error('API class not found.');
    }

    $restAPI = new $apiClass();

    $data = $restAPI->get_data([
        'lod_id' => $id
    ]);

    $firstEntry = !empty($data[0]) ? (array)$data[0] : [];

    $displayClass = "\\OES\\API\\{$apiKey}_Display_Helper";
    $displayHelper = class_exists($displayClass) ? new $displayClass() : new Display_Helper();

    $html = $displayHelper->html($firstEntry);

    wp_send_json([
        'html' => $html,
        'id' => $id,
        'box_id' => $boxID
    ]);
}

/**
 * Register LOD API Rest Routes
 * @return void
 */
function register_rest_routes(): void
{

    register_rest_route('oes/v1', '/apis', [
        'methods' => 'GET',
        'callback' => function () {

            $oes = OES();
            $apis = $oes->apis ?? [];

            $data = [];

            foreach ($apis as $key => $api) {
                $data[] = [
                    'key' => $key,
                    'label' => $api->label ?? '',
                    'database_link' => $api->database_link ?? '',
                    'search_options' => $api->search_options ?? [],
                    'post_type' => 'post',
                ];
            }

            return $data;
        },
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('oes/v1', '/lod-search', [
        'methods' => 'POST',
        'callback' => '\OES\API\rest_lod_search',
        'permission_callback' => '__return_true',
        'args' => [
            'param' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_array($param);
                },
            ],
        ],
    ]);

    register_rest_route('oes/v1', '/lod-copy', [
        'methods' => 'POST',
        'callback' => '\OES\API\rest_copy_to_post',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
        'args' => [
            'param' => [
                'required' => true,
                'validate_callback' => function ($param) {
                    return is_array($param);
                },
            ],
            'post_id' => [
                'required' => true,
                'validate_callback' => function ($post_id) {
                    return is_numeric($post_id);
                },
            ],
        ],
    ]);
}