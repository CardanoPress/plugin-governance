<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use Monolog\Logger as MonoLogger;
use ThemePlate\Logger;

class Application
{
    private static Application $instance;
    private static Logger $logger;
    public const VERSION = '0.4.0';
    private Admin $admin;
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
        self::$logger = new Logger('cardanopress-logs');
        $this->admin = new Admin();

        add_action('init', [$this->admin, 'init']);
        add_action('cardanopress_loaded', [$this, 'init']);
    }

    public function init(): void
    {
        $load_path = plugin_dir_path(CP_GOVERNANCE_FILE);
        $this->templates = new Templates($load_path . 'templates');

        new Manifest($load_path . 'assets/dist', self::VERSION);
        new Actions();
    }

    public static function isCoreActive(): bool
    {
        $function = function_exists('cardanoPress');
        $namespace = 'PBWebDev\\CardanoPress\\';
        $admin = class_exists($namespace . 'Admin');

        return $function && $admin;
    }

    public static function logger(string $channel): MonoLogger
    {
        return self::$logger->channel($channel);
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

    public function option(string $key)
    {
        return $this->admin->getOption($key);
    }
}
