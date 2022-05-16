<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

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
        $format = get_option('date_format') . ' ' . get_option('time_format');
        $difference = get_option('gmt_offset') * HOUR_IN_SECONDS;
        $start = get_post_time($format, true, $this->postId);
        $expiration = get_post_meta($this->postId, 'at-expiration', true);
        $end = $snapshot = '&mdash;';

        if ($expiration) {
            $end = wp_date($format, strtotime($expiration) - $difference);
        }

        $imploded = implode(' ', $this->getSnapshot());

        if ('' !== trim($imploded)) {
            $snapshot = wp_date($format, strtotime($imploded) - ($difference * 2));
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

    public function getCastedVotes(): array
    {
        global $wpdb;

        $key = $wpdb->esc_like('cp_governance_') . '%';
        $sql = "SELECT `user_id`,`meta_value` FROM $wpdb->usermeta WHERE `meta_key` LIKE %s ORDER BY `umeta_id` DESC";
        $result = [];

        foreach ($wpdb->get_results($wpdb->prepare($sql, $key), ARRAY_A) as $saved) {
            $result[$saved['user_id']] = maybe_unserialize($saved['meta_value']);
        }

        return $result;
    }
}
