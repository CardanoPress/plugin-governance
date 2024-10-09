<?php

/**
 * Plugin Name: CardanoPress - Governance
 * Plugin URI:  https://github.com/CardanoPress/plugin-governance
 * Author:      CardanoPress
 * Author URI:  https://cardanopress.io
 * Description: A CardanoPress extension for governance
 * Version:     1.7.0
 * License:     GPL-2.0-only
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: cardanopress-governance
 *
 * Requires at least: 5.9
 * Requires PHP:      7.4
 *
 * Requires Plugins: cardanopress
 *
 * @package ThemePlate
 * @since   0.1.0
 */

// Accessed directly
if (! defined('ABSPATH')) {
    exit;
}

use PBWebDev\CardanoPress\Governance\Application;
use PBWebDev\CardanoPress\Governance\Installer;

/* ==================================================
Global constants
================================================== */

if (! defined('CP_GOVERNANCE_FILE')) {
    define('CP_GOVERNANCE_FILE', __FILE__);
}

// Load the main plugin class
require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'dependencies/vendor/autoload_packages.php';
require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'dependencies/vendor/woocommerce/action-scheduler/action-scheduler.php';

// Instantiate
function cpGovernance(): Application
{
    static $application;

    if (null === $application) {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $application = new Application(CP_GOVERNANCE_FILE);
    }

    return $application;
}

cpGovernance()->setupHooks();
(new Installer(cpGovernance()))->setupHooks();
