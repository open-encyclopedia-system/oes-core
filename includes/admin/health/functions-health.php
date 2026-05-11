<?php

namespace OES\Admin\Health;

if (!defined('ABSPATH')) exit;

function debug_information(array $info): array
{
    $siteHealth = new \OES\Admin\Health\Site_Health();
    return $siteHealth->render_tab_content($info);
}

function register_rest_routes(): void {
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
