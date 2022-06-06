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

        add_action('admin_notices', [$this, 'noticeNeedingCorePlugin']);
        add_action('admin_notices', [$this, 'noticeNeedingAugmentTypes']);
        add_action('admin_notices', [$this, 'noticeNeedingGlobalPolicy']);
        add_action(self::DATA_PREFIX . 'upgrading', [$this, 'doUpgrade'], 10, 2);
        add_filter('plugin_action_links_' . $this->pluginBaseName, [$this, 'mergeSettingsLink']);
    }

    public function noticeNeedingGlobalPolicy(): void
    {
        if ($this->application->option('global_policy')) {
            return;
        }

        $message = sprintf(
            '<strong>%1$s</strong> requires a fallback / global config for proposals. %2$s',
            $this->application->getData('Name'),
            $this->getSettingsLink(__('Please set here', 'cardanopress-governance'), '_blank')
        );

        ob_start();

        ?>
        <div class="notice notice-info">
            <p><?php echo $message; ?></p>
        </div>
        <?php

        echo ob_get_clean();
    }

    public function noticeNeedingAugmentTypes(): void
    {
        if (class_exists('AugmentTypes', false)) {
            return;
        }

        $plugin  = sprintf(
            '<a href="%1$s" target="_blank">Augment Types</a>',
            'https://github.com/kermage/augment-types'
        );
        $message = sprintf(
            '<strong>%1$s</strong> requires the %2$s plugin for marking past proposals.',
            $this->application->getData('Name'),
            $plugin
        );

        ob_start();

        ?>
        <div class="notice notice-info">
            <p><?php echo $message; ?></p>
        </div>
        <?php

        echo ob_get_clean();
    }

    public function doUpgrade(string $currentVersion, string $appVersion): void
    {
        if ('' === $currentVersion) {
            $this->updateOldVotes();
        }
    }

    public function updateOldVotes(): void
    {
        $this->log('Governance: Checking for old user votes');

        foreach (get_users() as $user) {
            $userProfile = new Profile($user);
            $userId = $userProfile->getData('ID');

            if (! $userProfile->isConnected()) {
                $this->log('Unconnected user ' . $userId);
                continue;
            }

            $meta = $userProfile->getAllOwnedMeta();

            if (empty($meta)) {
                $this->log('Absentee user ' . $userId);
                continue;
            }

            $this->log('Elector user ' . $userId);

            foreach ($meta as $key => $value) {
                if (is_array(maybe_unserialize($value))) {
                    continue;
                }

                $proposalId = str_replace($userProfile->getMetaPrefix(), '', $key);

                $userProfile->saveVote($proposalId, $value, '', 0);
                $this->log('Updating vote ' . $key);
            }
        }
    }
}
