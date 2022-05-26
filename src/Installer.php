<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractInstaller;
use CardanoPress\Traits\HasSettingsLink;

class Installer extends AbstractInstaller
{
    use HasSettingsLink;

    public const DATA_PREFIX = 'cp_governance_';

    protected function initialize(): void
    {
        $this->setSettingsLinkUrl(admin_url('edit.php?post_type=proposal&page=' . Admin::OPTION_KEY));
    }

    public function setupHooks(): void
    {
        parent::setupHooks();

        $plugin = plugin_basename($this->application->getPluginFile());

        add_action('admin_notices', [$this, 'noticeNeedingCorePlugin']);
        add_action('admin_notices', [$this, 'noticeNeedingGlobalPolicy']);
        add_filter('plugin_action_links_' . $plugin, [$this, 'mergeSettingsLink']);
        add_action(self::DATA_PREFIX . 'upgrading', [$this, 'doUpgrade'], 10, 2);
    }

    public function noticeNeedingCorePlugin(): void
    {
        if ($this->application->isReady()) {
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

    public function noticeNeedingGlobalPolicy(): void
    {
        if ($this->application->option('global_policy')) {
            return;
        }

        ob_start();

        ?>
        <div class="notice notice-info">
            <p>
                <strong>CardanoPress - Governance</strong> requires a fallback / global config for proposals.
                <?php echo $this->getSettingsLink(__('Please set here', 'cardanopress-governance'), '_blank'); ?>
            </p>
        </div>
        <?php

        echo ob_get_clean();
    }

    public function doUpgrade(string $currentVersion, string $appVersion): void
    {
        if ('' !== $currentVersion) {
            $this->upgrade();
        }
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

                $userProfile->saveVote($proposalId, $value, '', 0);
            }

            $this->log(print_r($meta, true));
        }
    }
}
