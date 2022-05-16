<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use Monolog\Logger;

class Installer
{
    private static Installer $instance;
    private Application $application;
    private Admin $admin;
    private Logger $logger;

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
        $this->logger = $this->application::logger('installer');
        $this->admin = new Admin();

        add_action('admin_notices', [$this, 'notice']);
        add_filter('plugin_action_links_' . plugin_basename(CP_GOVERNANCE_FILE), [$this, 'addSettingsLink']);
    }

    protected function log(string $message, string $level = 'info'): void
    {
        $this->logger->log($level, $message);
    }

    public function notice(): void
    {
        if ($this->application::isCoreActive()) {
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

    public function addSettingsLink(array $links): array
    {
        $settings = sprintf(
            '<a href="%1$s" id="settings-%2$s" aria-label="%3$s">%4$s</a>',
            admin_url('edit.php?post_type=proposal&page=' . Admin::OPTION_KEY),
            Admin::OPTION_KEY,
            __('Settings CardanoPress - Governance', 'cardanopress-governance'),
            __('Settings', 'cardanopress-governance'),
        );

        return array_merge(compact('settings'), $links);
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

        if (empty(get_option('cp_governance_version'))) {
            $this->upgrade();
        }

        update_option('cp_governance_version', $this->application::VERSION);
        delete_transient('cp_governance_activating');
    }

    public function upgrade(): void
    {
        $this->log('Governance: Upgrading database values');

        foreach (get_users() as $user) {
            $userProfile = new Profile($user);

            if (! $userProfile->isConnected()) {
                continue;
            }

            $meta = $userProfile->getAllOwnedMeta();

            if (empty($meta)) {
                continue;
            }

            foreach ($meta as $key => $value) {
                $proposalId = str_replace($userProfile->getMetaPrefix(), '', $key);

                $userProfile->saveVote($proposalId, $value, '');
            }

            $this->log(print_r($meta, true));
        }
    }
}
