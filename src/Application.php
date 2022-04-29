<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Application
{
    private static Application $instance;
    public const VERSION = '0.1.0';

    public static function instance(): Application
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('cardanopress_loaded', [$this, 'init']);
        add_action('admin_notices', [$this, 'notice']);
        add_action('init', [$this, 'setup']);
    }

    public function init(): void
    {
        new Manifest(plugin_dir_path(CP_GOVERNANCE_FILE) . 'assets', self::VERSION);
    }

    public static function isCoreActive(): bool
    {
        $function = function_exists('cardanoPress');
        $namespace = 'PBWebDev\\CardanoPress\\';
        $admin = class_exists($namespace . 'Admin');

        return $function && $admin;
    }

    public function notice(): void
    {
        if (self::isCoreActive()) {
            return;
        }

        ob_start();

        ?>
        <div class="notice notice-info">
            <p>
                <strong>CardanoPress - Governance</strong> requires the core plugin for its full functionality.
            </p>
        </div>
        <?php

        echo ob_get_clean();
    }

    public function setup(): void
    {
        new Admin();
    }
}
