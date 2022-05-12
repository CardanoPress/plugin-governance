<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Snapshot
{
    private static Snapshot $instance;
    private string $lockKey = '';

    public const LOCK = 'cpg_snapshot_lock_';
    public const HOOK = 'cp_governance_snapshot_';
    public const GROUP = 'cardanopress-governance';

    public static function instance(): Snapshot
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
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
            return;
        }

        $this->lock();

        foreach (get_users() as $user) {
            $userProfile = new Profile($user);

            if (! $userProfile->isConnected()) {
                continue;
            }

            $userId = $userProfile->getData('ID');

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
            return;
        }

        $this->lock();

        error_log(__METHOD__ . ' ' . $proposalPostId . ' ' . $userId);

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
}
