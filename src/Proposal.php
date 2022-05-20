<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use DateTimeZone;

class Proposal
{
    public int $postId;

    protected ?bool $useGlobalConfig = null;

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

    public function isComplete(): bool
    {
        if ('archive' !== get_post_status($this->postId)) {
            return false;
        }

        $data = $this->getData();

        if (empty($data) || !array_filter(array_values($data))) {
            return false;
        }

        return true;
    }

    public function getID(): int
    {
        $status = get_post_meta($this->postId, 'proposal_id', true);

        return $status ?: 0;
    }

    protected function getSnapshot(): array
    {
        $status = get_post_meta($this->postId, 'proposal_snapshot', true);

        return $status ?: [
            'date' => '',
            'time' => '',
        ];
    }

    protected function getConfig(): bool
    {
        $status = get_post_meta($this->postId, 'proposal_config', true);

        return (bool) $status;
    }

    protected function getConfigValue(string $key)
    {
        if (null === $this->useGlobalConfig) {
            $this->useGlobalConfig = $this->getConfig();
        }

        if ($this->useGlobalConfig) {
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
        $start = get_post_datetime($this->postId);
        $start = $this->formatDate($start->getTimestamp());
        $expiration = get_post_meta($this->postId, 'at-expiration', true);
        $end = $snapshot = '&mdash;';


        if ($expiration) {
            $end = $this->formatDate(strtotime($expiration));
        }

        $imploded = implode(' ', $this->getSnapshot());

        if ('' !== trim($imploded)) {
            $difference = get_option('gmt_offset') * HOUR_IN_SECONDS;
            $snapshot = $this->formatDate(strtotime($imploded) - $difference);
        }

        return compact('start', 'end', 'snapshot');
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
        $calculator = new Calculator($this, $profile);
        $total = 0;

        foreach ($this->getCalculation() as $type) {
            $method = 'get' . ucfirst($type) . 'Power';

            if (method_exists($calculator, $method)) {
                $total += $calculator->$method();
            }
        }

        return $total;
    }

    public function getTotalVotes(): int
    {
        global $wpdb;

        $key = 'cp_governance_' . $this->getID();
        $sql = "SELECT COUNT(*) FROM $wpdb->usermeta WHERE `meta_key` = %s";

        return (int) $wpdb->get_var($wpdb->prepare($sql, $key));
    }

    public function getTotalPower(): int
    {
        $votes = $this->getCastedVotes();
        $powers = array_column($votes, 'power');

        return array_sum($powers);
    }

    public function getCastedVotes(): array
    {
        global $wpdb;

        $key = 'cp_governance_' . $this->getID();
        $sql = "SELECT `user_id`,`meta_value` FROM $wpdb->usermeta WHERE `meta_key` = %s ORDER BY `umeta_id` DESC";
        $result = [];

        foreach ($wpdb->get_results($wpdb->prepare($sql, $key), ARRAY_A) as $saved) {
            $user = $saved['user_id'];
            $data = maybe_unserialize($saved['meta_value']);

            $result[] = $this->formatVoteData($user, $data);
        }

        return $result;
    }

    protected function formatVoteData(int $userId, array $data): array
    {
        $userProfile = new Profile(get_user_by('id', $userId));
        $link = [
            'mainnet' => 'https://cardanoscan.io/transaction/',
            'testnet' => 'https://testnet.cardanoscan.io/transaction/',
        ];

        $data['option'] = $this->getOptionLabel($data['option']);
        $data['transaction'] = [
            'hash' => $data['transaction'],
            'link' => $link[$userProfile->connectedNetwork()] . $data['transaction'],
        ];
        $data['time'] = $this->formatDate($data['time']);

        return $data;
    }

    protected function formatDate(int $timestamp): string
    {
        $format = get_option('date_format') . ' ' . get_option('time_format');
        $format = apply_filters('cp-governance-date_format', $format);
        $timezone = apply_filters('cp-governance-date_timezone', 'UTC');

        return wp_date($format, $timestamp, new DateTimeZone($timezone));
    }
}
