<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Interfaces\HookInterface;
use CardanoPress\Traits\Loggable;
use PBWebDev\CardanoPress\Blockfrost;
use Psr\Log\LoggerInterface;

class Snapshot implements HookInterface
{
    use Loggable;

    private static Snapshot $instance;
    private string $lockKey = '';

    public const LOCK = 'cpg_snapshot_lock_';
    public const HOOK = 'cp_governance_snapshot_';
    public const GROUP = 'cardanopress-governance';

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function setupHooks(): void
    {
        add_action(self::HOOK . 'wallets', [$this, 'scanWallets']);
        add_action(self::HOOK . 'wallet', [$this, 'scanWallet'], 10, 2);
    }

    public static function isScheduled(int $proposalPostId): bool
    {
        return as_has_scheduled_action(self::HOOK . 'wallets', compact('proposalPostId'), self::GROUP);
    }

    public static function wasScheduled(int $proposalPostId): int
    {
        $retries = as_get_scheduled_actions([
            'hook'     => self::HOOK . 'wallets',
            'args'     => compact('proposalPostId'),
            'group'    => self::GROUP,
            'per_page' => -1,
        ], 'ids');

        return count($retries);
    }

    public static function schedule(int $timestamp, int $proposalPostId): int
    {
        return as_schedule_single_action(
            $timestamp,
            self::HOOK . 'wallets',
            compact('proposalPostId'),
            self::GROUP
        );
    }

    public function scanWallets(int $proposalPostId): void
    {
        $this->lockKey = self::LOCK . md5(__METHOD__ . $proposalPostId);

        if ($this->isRunning()) {
            $this->log(__METHOD__ . 'Is already running ' . $this->lockKey);

            return;
        }

        $this->lock();

        foreach (get_users() as $user) {
            $userProfile = new Profile($user);
            $userId = $userProfile->getData('ID');

            if (! $userProfile->isConnected()) {
                $this->log('User: ' . $userId . ' is not connected');

                continue;
            }

            if (md5($userProfile->connectedWallet()) === $userProfile->getData('user_login')) {
                $this->log('User: ' . $userId . ' is an old instance');

                continue;
            }


            as_enqueue_async_action(
                self::HOOK . 'wallet',
                compact('proposalPostId', 'userId'),
                self::GROUP
            );
        }

        $this->unlock();
    }

    public function scanWallet(int $proposalPostId, int $userId): void
    {
        $this->lockKey = self::LOCK . md5(__METHOD__ . $proposalPostId . $userId);

        if ($this->isRunning()) {
            $this->log(__METHOD__ . 'Is already running ' . $this->lockKey);

            return;
        }

        $this->lock();

        $user = get_user_by('id', $userId);
        $userProfile = new Profile($user);

        if (Application::getInstance()->isReady() && $userProfile->isConnected()) {
            $stakeAddress = $userProfile->connectedStake();
            $blockfrost = new Blockfrost($userProfile->connectedNetwork());
            $proposal = new Proposal($proposalPostId);
            $policyId = $proposal->getPolicy();
            $assets = [];
            $page = 1;

            $details = $blockfrost->getAccountDetails($stakeAddress);
            $assets[] = [
                'unit' => 'lovelace',
                'quantity' => $details['controlled_amount'] ?? '0',
            ];

            do {
                $response = $blockfrost->associatedAssets($stakeAddress, $page);

                foreach ($response as $asset) {
                    if (0 === strpos($asset['unit'], $policyId)) {
                        $assets[] = $asset;
                    }
                }

                $page++;
            } while (100 === count($response));

            update_post_meta($proposalPostId, '_proposal_snapshot_' . $userId, array_filter($assets));
            $this->log('User: ' . $userId . ' got scanned');
        } else {
            $this->log('User: ' . $userId . ' is not connected');
        }

        $this->unlock();
    }

    protected function isRunning(): int
    {
        return get_transient($this->lockKey);
    }

    protected function lock(): void
    {
        set_transient($this->lockKey, microtime());
    }

    protected function unlock(): void
    {
        delete_transient($this->lockKey);
    }

    protected function log(string $message, string $level = 'info'): void
    {
        $this->logger->log($level, $message);
    }
}
