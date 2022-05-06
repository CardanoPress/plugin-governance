<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use PBWebDev\CardanoPress\Blockfrost;

class Proposal
{
    public int $postId;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function toArray(): array
    {
        return [
            'post_id' => $this->postId,
            'identifier' => $this->getID(),
            'discussion_link' => $this->getDiscussionLink(),
            'policy' => $this->getPolicy(),
            'calculation' => $this->getCalculation(),
            'options' => $this->getOptions(),
            'data' => $this->getData(),
            'dates' => $this->getDates(),
        ];
    }

    public static function getPostId(int $id): int
    {
        $result = get_posts([
            'post_type' => 'proposal',
            'numberposts' => 1,
            'fields' => 'ids',
            'meta_key' => 'proposal_id',
            'meta_value' => $id,
        ]);

        return $result[0] ?? 0;
    }

    public function getID(): int
    {
        $status = get_post_meta($this->postId, 'proposal_id', true);

        return $status ?: 0;
    }

    protected function getConfig(): bool
    {
        $status = get_post_meta($this->postId, 'proposal_config', true);

        return (bool) $status;
    }

    protected function getConfigValue(string $key)
    {
        if ($this->getConfig()) {
            $status = Application::instance()->option('global_' . $key);
        } else {
            $status = get_post_meta($this->postId, 'proposal_' . $key, true);
        }

        return $status;
    }

    public function getDiscussionLink(): array
    {
        $status = $this->getConfigValue('discussion');

        return $status ?: [
            'url' => '',
            'text' => '',
            'target' => '',
        ];
    }

    public function getPolicy(): string
    {
        $status = $this->getConfigValue('policy');

        return $status ?: '';
    }

    public function getCalculation(): array
    {
        $status = $this->getConfigValue('calculation');

        return $status ?: [];
    }

    public function getOptions(): array
    {
        $status = get_post_meta($this->postId, 'proposal_options', false);

        return $status ?: [];
    }

    public function getOptionLabel(string $value): string
    {
        $options = $this->getOptions();

        $index = array_search($value, array_column($this->getOptions(), 'value'), true);

        if (false === $index) {
            return '';
        }

        return $options[$index]['label'];
    }

    public function getData(): array
    {
        $status = get_post_meta($this->postId, '_proposal_data', true);

        return $status ?: [];
    }

    public function getDates(): array
    {
        $format = get_option('date_format') . ' ' . get_option('time_format');
        $start = get_post_time($format, true, $this->postId);
        $expiration = get_post_meta($this->postId, 'at-expiration', true);
        $end = '&mdash;';

        if ($expiration) {
            $end = wp_date($format, strtotime($expiration));
        }

        return compact('start', 'end');
    }

    public function updateData(string $option, int $value, bool $increase = true): bool
    {
        $data = $this->getData();

        if (! isset($data[$option])) {
            return false;
        }

        $current = $data[$option];

        if ($increase) {
            $data[$option] = $current + $value;
        } else {
            $data[$option] = $current - $value;
        }

        return (bool)update_post_meta($this->postId, '_proposal_data', $data);
    }

    public function getVotingPower(Profile $profile): int
    {
        $total = 0;

        foreach ($this->getCalculation() as $type) {
            $method = 'get' . ucfirst($type) . 'Power';

            if (method_exists($this, $method)) {
                $total += $this->$method($profile);
            }
        }

        return $total;
    }

    protected function getTokenPower(Profile $profile): int
    {
        $storedAssets = $profile->storedAssets();

        if (empty($storedAssets)) {
            return 0;
        }

        $policyIds = array_column($storedAssets, 'policy_id');

        if (! in_array($this->getPolicy(), $policyIds, true)) {
            return 0;
        }

        $assetsCount = array_count_values($policyIds);

        return $assetsCount[$this->getPolicy()];
    }

    protected function getAdaPower(Profile $profile): int
    {
        $blockfrost = new Blockfrost($profile->connectedNetwork());
        $response = $blockfrost->getAddressDetails($profile->connectedWallet());

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
