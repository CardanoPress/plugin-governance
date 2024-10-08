<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Helpers\WalletHelper;
use DateTimeZone;

class Proposal
{
    public int $postId;

    protected ?bool $useGlobalConfig = null;
    protected ?string $currentStatus = null;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
        $this->currentStatus = get_post_status($this->postId) ?: null;
    }

    public function toArray(): array
    {
        return [
            'post_id' => $this->postId,
            'identifier' => $this->getID(),
            'discussion_link' => $this->getDiscussionLink(),
            'policy' => $this->getPolicy(),
            'calculation' => $this->getCalculation(),
            'fee' => $this->getFee(),
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

    public function isReady(): bool
    {
        if (! Application::getInstance()->isReady()) {
            return false;
        }

        if ('publish' !== $this->currentStatus) {
            return false;
        }

        return true;
    }

    public function isComplete(): bool
    {
        if ('archive' !== $this->currentStatus) {
            return false;
        }

        $data = $this->getData();

        if (empty($data) || !array_filter(array_values($data))) {
            return false;
        }

        return true;
    }

    public function getStatusText(): string
    {
        $statusText = __('Open for Voting', 'cardanopress-governance');

        if ('future' === $this->currentStatus) {
            $statusText = __('Upcoming', 'cardanopress-governance');
        } elseif ('archive' === $this->currentStatus) {
            $statusText = __('Complete', 'cardanopress-governance');
        }

        return $statusText;
    }

    public function getDateLabel(): string
    {
        $dateLabel = __('Closing Date', 'cardanopress-governance');

        if ('future' === $this->currentStatus) {
            $dateLabel = __('Starting Date', 'cardanopress-governance');
        }

        return $dateLabel;
    }

    public function getDateText(): string
    {
        $dateTexts = $this->getDates();
        $dateText = $dateTexts['end'];

        if ('future' === $this->currentStatus) {
            $dateText = $dateTexts['start'];
        }

        return $dateText;
    }

    public function getVoteText(): string
    {
        $voteText = __('Vote', 'cardanopress');

        if ('archive' === $this->currentStatus) {
            $voteText = __('Voting Result', 'cardanopress-governance');
        }

        return $voteText;
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
            $status = Application::getInstance()->option('global_' . $key);
        } else {
            $status = get_post_meta($this->postId, 'proposal_' . $key, true);
        }

        return $status;
    }

    public function getDiscussionLink(): array
    {
        $status = $this->getConfigValue('discussion');

        if (! is_array($status)) {
            $status = $status ? ['url' => $status] : [];
        }

        return array_merge([
            'url' => '',
            'text' => '',
            'target' => '',
        ], $status);
    }

    public function getPolicy(): string
    {
        $status = $this->getConfigValue('policy');

        return $status ?: '';
    }

    public function getCalculation(): array
    {
        $status = $this->getConfigValue('calculation');

        return (array)$status ?: [];
    }

    public function getFee(): array
    {
        $status = $this->getConfigValue('fee');

        return (array)$status ?: [];
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

    public function getDate(string $type): string
    {
        $dates = $this->getDates();

        return $dates[$type] ?? '';
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
        $network = $userProfile->connectedNetwork() ?: 'mainnet';

        $data['option'] = $this->getOptionLabel($data['option']);
        $data['transaction'] = [
            'hash' => $data['transaction'],
            'link' => WalletHelper::getCardanoscanLink($network, 'transaction/' . $data['transaction']),
        ];
        $data['time'] = $this->formatDate($data['time']);

        return $data;
    }

    protected function formatDate(int $timestamp): string
    {
        $format = get_option('date_format') . ' ' . get_option('time_format');
        $format = apply_filters('cp-governance-date_format', $format);
        $timezone = apply_filters('cp-governance-date_timezone', 'UTC');
        $timezone = new DateTimeZone($timezone);
        $value = wp_date($format, $timestamp, $timezone);
        $string = apply_filters('cp-governance-date_string', '%format% %timezone%');

        return str_replace(['%format%', '%timezone%'], [$value, $timezone->getName()], $string);
    }
}
