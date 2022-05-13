<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

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

        $this->getFromSnapshot = 'future' !== get_post_status($proposal->postId);
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

        $policyIds = array_column($storedAssets, 'policy_id');

        if (! in_array($this->proposal->getPolicy(), $policyIds, true)) {
            return 0;
        }

        $assetsCount = array_count_values($policyIds);

        return $assetsCount[$this->proposal->getPolicy()];
    }

    public function getAdaPower(): int
    {
        if ($this->getFromSnapshot) {
            return $this->getSnapshotPower('ada');
        }

        if (! $this->profile->isConnected()) {
            return 0;
        }

        $blockfrost = new Blockfrost($this->profile->connectedNetwork());
        $response = $blockfrost->getAddressDetails($this->profile->connectedWallet());

        if (empty($response) || empty($response['amount'])) {
            return 0;
        }

        return $this->getLovelace($response['amount']);
    }

    protected function getSnapshotPower(string $type): int
    {
        $status = get_post_meta(
            $this->proposal->postId,
            '_proposal_snapshot_' . $this->profile->getData('ID'),
            true
        );

        if (empty($status)) {
            return 0;
        }

        if ('ada' === $type) {
            return $this->getLovelace($status);
        }

        $result = 0;

        foreach ($status as $asset) {
            if (0 === strpos($asset['unit'], $this->proposal->getPolicy())) {
                $result += $asset['quantity'];
            }
        }

        return $result;
    }

    protected function getLovelace(array $assets): int
    {
        $index = array_search('lovelace', array_column($assets, 'unit'), true);

        if (false === $index) {
            return 0;
        }

        return $assets[$index]['quantity'] / 1000000;
    }
}
