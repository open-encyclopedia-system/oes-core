<?php

namespace OES\Admin\Health;

if (!defined('ABSPATH')) exit;

function debug_information(array $info): array
{
    $siteHealth = new \OES\Admin\Health\Site_Health();
    return $siteHealth->render_tab_content($info);
}
