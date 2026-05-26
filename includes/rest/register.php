<?php

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

//TODO documentation
//TODO add schema to json
function export(): void
{

    register_rest_route('oes/v1', '/export/json/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Export\JSON_Export($request['id']);
            $json = $exporter->export_post();

            header('Content-Type: application/json; charset=utf-8');

            echo $json;
            exit;
        }
    ]);

    register_rest_route('oes/v1', '/export/oes/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Export\OES_Export($request['id']);
            $json = $exporter->export_post();

            header('Content-Type: application/json; charset=utf-8');

            echo $json;
            exit;
        }
    ]);

    register_rest_route('oes/v1', '/export/tei/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Export\TEI_Export($request['id']);
            $json = $exporter->export_post();

            header('Content-Type: application/json; charset=utf-8');

            echo $json;
            exit;
        }
    ]);
}

function fields(): void
{
    register_rest_route('oes/v1', '/fields', [
        'methods' => 'GET',
        'callback' => function () {

            if (!function_exists('acf_get_field_groups')) {
                return [];
            }

            $result = [];

            $groups = acf_get_field_groups();

            foreach ($groups as $group) {

                $fields = acf_get_fields($group['key']);

                if (!$fields) {
                    continue;
                }

                $labelPrefix = $group['title'] ?? $group['key'];

                foreach ($fields as $field) {

                    $type = $field['type'];
                    if ($type == 'tab') {
                        continue;
                    }

                    $result[] = [
                        'key' => $field['key'],
                        'name' => $field['name'],
                        'label' => $labelPrefix . ': ' . $field['label'],
                        'type' => $field['type'],
                    ];
                }
            }

            return $result;
        },
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ]);
}

function health(): void
{
    register_rest_route('oes/v1', '/debug/', [
        'methods' => 'GET',
        'permission_callback' => function () {
            return true; //TODO
        },
        'callback' => function () {

            $siteHealth = new \OES\Admin\Health\Site_Health();
            $data = $siteHealth->get_site_health_values(true);
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            header('Content-Type: application/json; charset=utf-8');

            echo $json;
            exit;
        }
    ]);
}

/**
 * Register LOD API Rest Routes
 * @return void
 */
function lod(): void
{
    register_rest_route('oes/v1', '/lod-apis', [
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