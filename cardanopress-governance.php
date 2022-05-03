<?php

/**
 * Plugin Name: CardanoPress - Governance
 * Plugin URI:  https://github.com/pbwebdev/cardanopress-governance
 * Author:      Gene Alyson Fortunado Torcende
 * Author URI:  https://pbwebdev.com
 * Description: A CardanoPress extension for governance
 * Version:     0.1.0
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

/* ==================================================
Global constants
================================================== */

if (! defined('CP_GOVERNANCE_FILE')) {
    define('CP_GOVERNANCE_FILE', __FILE__);
}

// Load the main plugin class
require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'vendor/autoload.php';

// Instantiate the updater
EUM_Handler::run(CP_GOVERNANCE_FILE, 'https://raw.githubusercontent.com/pbwebdev/cardanopress-governance/main/update-data.json');

// Instantiate
Application::instance();
