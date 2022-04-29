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
    private Templates $templates;

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
        $load_path = plugin_dir_path(CP_GOVERNANCE_FILE);
        $this->templates = new Templates($load_path . 'templates');

        new Manifest($load_path . 'assets', self::VERSION);
    }

    public static function isCoreActive(): bool
    {
        $function = function_exists('cardanoPress');
        $namespace = 'PBWebDev\\CardanoPress\\';
        $admin = class_exists($namespace . 'Admin');

        return $function && $admin;
    }

    public static function log(string $message): void
    {
        if (self::isCoreActive()) {
            cardanoPress()->logger('admin')->error($message);
        } else {
            error_log($message);
        }
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

    public function template(string $name, array $variables = []): void
    {
        $name .= '.php';
        $file = locate_template($this->templates->getPath() . $name);

        if (! $file) {
            $file = $this->templates->getPath(true) . $name;
        }

        if (file_exists($file)) {
            extract($variables, EXTR_OVERWRITE);
            include $file;
        }
    }
}
