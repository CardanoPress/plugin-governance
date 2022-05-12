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
    public const HOOK = 'cp_governance_snapshot';
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
        add_action(self::HOOK . '_wallets', [$this, 'scanWallets']);
        add_action(self::HOOK . '_wallet', [$this, 'scanWallet'], 10, 2);
    }

    public function schedule(int $timestamp, int $proposalPostId): int
    {
        return as_schedule_single_action(
            $timestamp,
            self::HOOK . '_wallets',
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
                self::HOOK . '_wallet',
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
