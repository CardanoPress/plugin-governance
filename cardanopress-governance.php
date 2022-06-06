<?php

/**
 * Plugin Name: CardanoPress - Governance
 * Plugin URI:  https://github.com/CardanoPress/plugin-governance
 * Author:      CardanoPress
 * Author URI:  https://cardanopress.io
 * Description: A CardanoPress extension for governance
 * Version:     0.10.0
 * License:     GPL-2.0-only
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'vendor/autoload.php';
require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'vendor/woocommerce/action-scheduler/action-scheduler.php';

// Instantiate the updater
EUM_Handler::run(CP_GOVERNANCE_FILE, 'https://raw.githubusercontent.com/CardanoPress/plugin-governance/main/update-data.json');

// Instantiate
function cpGovernance(): Application
{
    static $application;

    if (null === $application) {
        $application = new Application(CP_GOVERNANCE_FILE);
    }

    return $application;
}

cpGovernance()->setupHooks();
(new Installer(cpGovernance()))->setupHooks();
