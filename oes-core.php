<?php

/**
 * Open Encyclopedia System Core
 *
 * @wordpress-plugin
 * Plugin Name:       OES Core
 * Plugin URI:        https://www.open-encyclopedia-system.org/
 * Description:       A framework for scholarly reference works.
 * Version:           3.1.0
 * Author:            Maren Welterlich-Strobl, Freie Universität Berlin, FUB-IT, Digitale Forschungsinfrastrukturen
 * Author URI:        https://www.fu-berlin.de/
 * Requires at least: 6.5
 * Tested up to:      7.1
 * Requires PHP:      8.1
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       oes
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/* ---------------------------------------------------------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------------------------------------------------------ */
defined('OES_CORE_VERSION') || define('OES_CORE_VERSION', '3.1.0');
defined('OES_CORE_PLUGIN') || define('OES_CORE_PLUGIN', __DIR__);
defined('OES_BASENAME') || define('OES_BASENAME', basename(__DIR__));
defined('OES_CORE_PLUGIN_FILE') || define('OES_CORE_PLUGIN_FILE', __FILE__);
defined('OES_CORE_PLUGIN_BASENAME') || define('OES_CORE_PLUGIN_BASENAME', plugin_basename(__FILE__));
defined('OES_LIVE_MODE') || define('OES_LIVE_MODE', true);
defined('OES_DB_VERSION') || define('OES_DB_VERSION', '2.1');

/* ---------------------------------------------------------------------------------------------------------------------
 * Loader
 * ------------------------------------------------------------------------------------------------------------------ */
require_once __DIR__ . '/includes/core/autoload.php';

/* ---------------------------------------------------------------------------------------------------------------------
 * Accessor. A plain getter: boots the core once, never loads the data model.
 * ------------------------------------------------------------------------------------------------------------------ */
if (!function_exists('OES')) {

    /**
     * @param string $applicationPath Optional path to the OES application plugin (legacy way to register it).
     * @param array $args Boot arguments (only used by the very first call).
     */
    function OES(string $applicationPath = '', array $args = []): OES_Core
    {
        global $oes;

        if (!isset($oes)) {
            $oes = new OES_Core($args);
            if ($oes->initialized) {
                $oes->maybe_upgrade();
                $oes->initialize_core();
            }
        }

        if ($applicationPath !== '' && !$oes->application_initialized) {
            $oes->initialize_application($applicationPath);
        }

        return $oes;
    }
}

/** --------------------------------------------------------------------------------------------------------------------
 * Add OES hook after all plugins are loaded. (This grants full control over the execution order outside the WordPress
 * hook.)
 * -------------------------------------------------------------------------------------------------------------------*/
add_action('plugins_loaded', function () {
    OES();
    do_action('oes/plugins_loaded');
});

/** --------------------------------------------------------------------------------------------------------------------
 * Load the compiled data model once.
 * -------------------------------------------------------------------------------------------------------------------*/
add_action('init', function () {
    OES()->load_data_model();
}, 5);

/** --------------------------------------------------------------------------------------------------------------------
 * Install the OES data base tables.
 * -------------------------------------------------------------------------------------------------------------------*/
require_once __DIR__ . '/includes/admin/initialize-db.php';

register_activation_hook(__FILE__, function () {
    OES()->maybe_upgrade();
});
