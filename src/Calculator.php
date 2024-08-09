<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Helpers\NumberHelper;
use PBWebDev\CardanoPress\Blockfrost;

class Calculator
{
    private Proposal $proposal;
    private Profile $profile;
    private bool $getFromSnapshot;

    public function __construct(Proposal $proposal, Profile $profile)
    {
        $this->proposal = $proposal;
        $this->profile = $profile;

        $this->getFromSnapshot = $this->isSnapshotAvailable($proposal);
    }

    private function isSnapshotAvailable(Proposal $proposal): bool
    {
        if ('future' === get_post_status($proposal->postId)) {
            return false;
        }

        if (! Snapshot::wasScheduled($proposal->postId)) {
            return false;
        }

        if (! $this->getSnapshotData()) {
            return false;
        }

        return true;
    }

    protected function getSnapshotData()
    {
        return get_post_meta(
            $this->proposal->postId,
            '_proposal_snapshot_' . $this->profile->getData('ID'),
            true
        );
    }

    public function getTokenPower(): int
    {
        if ($this->getFromSnapshot) {
            return $this->getSnapshotPower('token');
        }

        $storedAssets = $this->profile->storedAssets();

        if (empty($storedAssets)) {
            return 0;
        }

        $assets = array_map(function ($stored) {
            return array(
                'unit' => $stored['asset'],
                'quantity' => $stored['_quantity'],
            );
        }, $storedAssets);

        return $this->getToken($assets);
    }

    public function getAdaPower(): int
    {
        if ($this->getFromSnapshot) {
            return $this->getSnapshotPower('ada');
        }

        if (! Application::getInstance()->isReady() || ! $this->profile->isConnected()) {
            return 0;
        }

        $blockfrost = new Blockfrost($this->profile->connectedNetwork());
        $response = $blockfrost->getAccountDetails($this->profile->connectedStake());

        if (empty($response) || empty($response['controlled_amount'])) {
            return 0;
        }

        return (int) round(NumberHelper::lovelaceToAda($response['controlled_amount']));
    }

    protected function getSnapshotPower(string $type): int
    {
        $status = $this->getSnapshotData();

        if (empty($status)) {
            return 0;
        }

        if ('ada' === $type) {
            return $this->getLovelace($status);
        }

        return $this->getToken($status);
    }

    protected function getLovelace(array $assets): int
    {
        $index = array_search('lovelace', array_column($assets, 'unit'), true);

        if (false === $index) {
            return 0;
        }

        return (int) round(NumberHelper::lovelaceToAda($assets[$index]['quantity']));
    }

    protected function getToken(array $assets): int
    {
        $result = 0;
        $policy = $this->proposal->getPolicy();

        foreach ($assets as $asset) {
            if (0 === strpos($asset['unit'], $policy)) {
                $result += $asset['quantity'];
            }
        }

        return $result;
    }
}
