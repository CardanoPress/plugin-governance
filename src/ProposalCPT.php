<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Governance\Dependencies\ThemePlate\CPT\PostType;
use CardanoPress\Interfaces\HookInterface;
use CardanoPress\Traits\Loggable;
use Psr\Log\LoggerInterface;
use WP_Post;
use WP_Query;

class ProposalCPT implements HookInterface
{
    use Loggable;

    public const STATUSES = [
        'current' => 'publish',
        'upcoming' => 'future',
        'past' => 'archive',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public function setupHooks(): void
    {
        add_action('wp_insert_post', [$this, 'prepareData'], 10, 2);
        add_action('wp_insert_post', [$this, 'scheduleSnapshot'], 10, 2);
        add_action('pre_get_posts', [$this, 'customizeStatus']);
        add_filter('use_block_editor_for_post_type', [$this, 'noBlocks'], 10, 2);
        add_action(Installer::DATA_PREFIX . 'activating', [$this, 'pluginActivating']);
        $this->register();
    }

    public function pluginActivating(): void
    {
        $this->register();
        flush_rewrite_rules();
    }

    public function register(): void
    {
        $postType = new PostType('proposal', [
            'menu_position' => 5,
            'menu_icon' => 'dashicons-feedback',
            'supports' => ['title', 'editor', 'excerpt'],
            'has_archive' => true,
            'rewrite' => ['slug' => 'proposals'],
            'rest_base' => 'proposals',
        ]);

        $postType->register();
    }

    public function prepareData(int $postId, WP_Post $post): void
    {
        if ('proposal' !== $post->post_type) {
            return;
        }

        $options = get_post_meta($postId, 'proposal_options', false);
        $data = get_post_meta($postId, '_proposal_data', true) ?: [];
        $updated = false;

        foreach ($options as $option) {
            if (array_key_exists($option['value'], $data)) {
                continue;
            }

            $data[$option['value']] = 0;
            $updated = true;
        }

        $different = array_diff(array_keys($data), array_column($options, 'value'));

        if ($different) {
            $updated = true;

            foreach ($different as $old) {
                unset($data[$old]);
            }
        }

        if ($updated) {
            update_post_meta($postId, '_proposal_data', $data);
        }
    }

    public function scheduleSnapshot(int $postId, WP_Post $post): void
    {
        if ('proposal' !== $post->post_type || !in_array($post->post_status, self::STATUSES, true)) {
            return;
        }

        $snapshot = get_post_meta($postId, 'proposal_snapshot', true);
        $snapshot = array_filter((array)$snapshot);

        if (empty($snapshot) || Snapshot::isScheduled($postId) || Snapshot::wasScheduled($postId)) {
            return;
        }

        $difference = get_option('gmt_offset') * HOUR_IN_SECONDS;
        $datetime = strtotime(implode(' ', $snapshot));
        $timestamp = $datetime - $difference;

        if (time() > $timestamp) {
            return;
        }

        Snapshot::schedule($timestamp, $postId);
    }

    public function customizeStatus(WP_Query $query): void
    {
        if (
            $query->get_queried_object() &&
            ! $query->is_post_type_archive('proposal') &&
            ! $query->is_singular('proposal')
        ) {
            return;
        }

        global $wp_post_statuses;

        $future = &$wp_post_statuses['future'];

        $future->public = true;
        $future->protected = false;
    }

    public function noBlocks(bool $status, string $postType): bool
    {
        if ('proposal' === $postType) {
            $status = false;
        }

        return $status;
    }
}
