<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Installer
{
    private static Installer $instance;
    private Application $application;
    private Admin $admin;

    public static function instance(): Installer
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->application = Application::instance();
        $this->admin = new Admin();
    }

    public function log(string $message): void
    {
        $this->application::log($message, 'installer');
    }

    public function activate(): void
    {
        if ('yes' === get_transient('cp_governance_activating')) {
            $this->log('Governance: Is already activating');

            return;
        }

        $this->log('Governance: Activating version ' . $this->application::VERSION);
        $this->admin->init();
        flush_rewrite_rules();

        set_transient('cp_governance_activating', 'yes', MINUTE_IN_SECONDS * 2);

        update_option('cp_governance_version', $this->application::VERSION);
        delete_transient('cp_governance_activating');
    }
}
