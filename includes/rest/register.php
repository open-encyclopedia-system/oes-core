<?php

//@oesDevelopment: consider import function that maps json-ld/xml-tei to OES data model
//@oesDevelopment: create tool that can export a specific post to format and download it.

namespace OES\Rest;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Register Export Rest Routes
 * @return void
 */
function apis(): void
{
    register_rest_route('oes/v1', '/vocab', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => '\OES\Rest\serve_vocab',
    ]);

    register_rest_route('oes/v1', '/export/raw/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Rest\Export('raw');
            $exporter->prepare_post_data($request['id']);
            $exporter->export_json();

            exit;
        }
    ]);

    register_rest_route('oes/v1', '/export/json/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Rest\Export('jsonld');
            $exporter->prepare_post_data($request['id']);
            $exporter->export_json();

            exit;
        }
    ]);

    register_rest_route('oes/v1', '/export/txt/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Rest\Export('txt');
            $exporter->prepare_post_data($request['id']);
            $exporter->export_txt();

            exit;
        }
    ]);

    register_rest_route('oes/v1', '/export/tei/(?P<id>\d+)', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {

            $exporter = new \OES\Rest\Export('tei');
            $exporter->export_tei($request['id']);
            exit;
        }
    ]);

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

function serve_vocab(\WP_REST_Request $request)
{

    $path = OES_CORE_PLUGIN . '/includes/rest/vocab.jsonld';
    $vocab = json_decode(file_get_contents($path), true);

    $vocab['@context']['oes'] = rest_url('oes/v1/vocab#');
    $vocab['@id'] = rest_url('oes/v1/vocab');

    $response = new \WP_REST_Response($vocab);
    $response->header('Content-Type', 'application/ld+json');
    return $response;
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

/**
 * @oesDevelopment Add as a block.
 *
 * Renders the [oes_export_button] shortcode.
 *
 * Displays a button linking to the machine-readable export of the current post (resolved via parameter or the global
 * $post object) in a given export format.
 * Produces no output if $post is not a valid singular post context.
 *
 * Label resolution order: enclosed shortcode content,
 * then a language-specific `label_{$oes_language}` attribute,
 * then the generic `label` attribute,
 * then the default translated "Export" string.
 *
 * @param array{
 *     format?: string,
 *     label?: string,
 *     ...<string, string>
 * } $args Shortcode attributes. Recognized keys: 'post_id', 'format' (one of 'json',
 *         'tei', 'txt', 'rdf', 'raw'; invalid values fall back to 'json'),
 *         'label', and 'label_{language}' (e.g. 'label_language0', 'label_language1').
 * @param string|null $content Enclosed shortcode content, used as the
 *         button label when present.
 *
 * @return string The rendered `<span class="oes-export-button">` HTML,
 *         or an empty string if there is no valid current post.
 */
function export_button_html(array $args = [], string $content = null): string
{
    $postID = null;

    if(isset($args['post_id'])){
        $postID = $args['post_id'];
    }
    else {
        global $post;
        if ($post instanceof \WP_Post) {
            $postID = $post->ID;
        }
    }

    if(!$postID) {
        return '';
    }

    $format = $args['format'] ?? 'json';

    if(!in_array($format, ['json', 'tei', 'txt', 'rdf', 'raw'])) {
        $format = 'json';
    }

    $url = esc_url(
        site_url('/wp-json/oes/v1/export/' . $format . '/' . $postID)
    );

    if(empty($content)) {
        global $oes_language;
        $content =  $args['label_' . $oes_language] ?? ($args['label'] ?? __('Export', 'oes'));
    }

    return sprintf('<span class="oes-export-button"><a href="%s" class="button" target="_blank">%s</a></span>',
        $url,
        esc_html($content)
    );
}
