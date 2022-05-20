<?php

/**
 * Plugin Name: CardanoPress - Governance
 * Plugin URI:  https://github.com/pbwebdev/cardanopress-governance
 * Author:      Gene Alyson Fortunado Torcende
 * Author URI:  https://pbwebdev.com
 * Description: A CardanoPress extension for governance
 * Version:     0.7.0
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
use PBWebDev\CardanoPress\Governance\Snapshot;

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
EUM_Handler::run(CP_GOVERNANCE_FILE, 'https://raw.githubusercontent.com/pbwebdev/cardanopress-governance/main/update-data.json');

// Instantiate
Application::instance();
Snapshot::instance();
register_activation_hook(CP_GOVERNANCE_FILE, [Installer::instance(), 'activate']);
