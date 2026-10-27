<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once OES_CORE_PLUGIN . '/includes/core/trait-oes_core_compat.php';
require_once OES_CORE_PLUGIN . '/includes/core/services/functions-services.php';

spl_autoload_register(static function (string $class): void {
    static $map = [
        'OES_Core' => 'class-oes_core.php',
        'OES\\Core\\Cache' => 'class-cache.php',
        'OES\\Core\\Loader' => 'class-loader.php',
        'OES\\Core\\Reader' => 'class-reader.php',
        'OES\\Service\\Exportable' => 'services/interface-exportable.php',
        'OES\\Service\\Admin' => 'services/class-admin.php',
        'OES\\Service\\Languages' => 'services/class-languages.php',
        'OES\\Service\\Model' => 'services/class-model.php',
        'OES\\Service\\Registry' => 'services/class-registry.php',
        'OES\\Service\\Theme' => 'services/class-theme.php',
    ];
    if (isset($map[$class])) require_once OES_CORE_PLUGIN . '/includes/core/' . $map[$class];
});
