<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

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
            'policy' => $this->getPolicy(),
            'options' => $this->getOptions(),
            'data' => $this->getData(),
            'dates' => $this->getDates(),
        ];
    }

    public function getPolicy(): string
    {
        $status = get_post_meta($this->postId, 'proposal_policy', true);

        return $status ?: '';
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
}
