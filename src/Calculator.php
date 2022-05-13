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

    public function __construct(Proposal $proposal, Profile $profile)
    {
        $this->proposal = $proposal;
        $this->profile = $profile;
    }

    public function getTokenPower(): int
    {
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
        if (! $this->profile->isConnected()) {
            return 0;
        }

        $blockfrost = new Blockfrost($this->profile->connectedNetwork());
        $response = $blockfrost->getAddressDetails($this->profile->connectedWallet());

        if (empty($response) || empty($response['amount'])) {
            return 0;
        }

        $index = array_search('lovelace', array_column($response['amount'], 'unit'), true);

        if (false === $index) {
            return 0;
        }

        return $response['amount'][$index]['quantity'] / 1000000;
    }
}
