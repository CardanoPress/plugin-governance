<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use Exception;
use ThemePlate\Core\Data;
use ThemePlate\CPT\PostType;
use ThemePlate\Meta\Post;
use ThemePlate\Page;
use ThemePlate\Settings;
use WP_Post;
use WP_Query;

class Admin
{
    protected Data $data;
    protected Fields $fields;

    public const OPTION_KEY = 'cp-governance';

    public function __construct()
    {
        $this->data = new Data();
        $this->fields = new Fields();

        add_action('init', [$this, 'proposalCPT']);
        add_action('init', [$this, 'proposalSettingsPage']);
        add_action('init', [$this, 'proposalConfigFields']);
        add_action('init', [$this, 'proposalArchiveFields']);
        add_action('init', [$this, 'proposalSettings']);
        add_action('init', [$this, 'proposalStatus']);
        add_action('wp_insert_post', [$this, 'prepareProposalData'], 10, 2);
        add_filter('pre_get_posts', [$this, 'customizeProposalStatus']);
        add_filter('use_block_editor_for_post_type', [$this, 'noBlocksProposal'], 10, 2);
    }

    public function proposalCPT(): void
    {
        try {
            new PostType([
                'name' => 'proposal',
                'plural' => __('Proposals', 'cardanopress-governance'),
                'singular' => __('Proposal', 'cardanopress-governance'),
                'args' => [
                    'menu_position' => 5,
                    'menu_icon' => 'dashicons-feedback',
                    'supports' => ['title', 'editor', 'excerpt'],
                    'has_archive' => true,
                    'rewrite' => ['slug' => 'proposals'],
                    'rest_base' => 'proposals',
                ],
            ]);
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function proposalSettingsPage(): void
    {
        try {
            new Page([
                'id' => self::OPTION_KEY,
                'parent' => 'edit.php?post_type=proposal',
                'menu' => 'Settings',
                'title' => 'CardanoPress - Governance',
            ]);
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function proposalArchiveFields(): void
    {
        try {
            $settings = new Settings([
                'id' => 'proposal',
                'title' => __('Proposal Archives', 'cardanopress'),
                'page' => self::OPTION_KEY,
                'fields' => [
                    'title' => [
                        'title' => __('Title', 'cardanopress-governance'),
                        'type' => 'text',
                        'default' => 'Project Governance'
                    ],
                    'content' => [
                        'title' => __('Content', 'cardanopress-governance'),
                        'type' => 'editor',
                        'default' => 'Vote on upcoming decision of the projects DAO.

Submit a proposal for discussion or vote in current proposals in our ecosystem.'
                    ],
                ],
            ]);

            $this->data->store($settings->get_config());
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function proposalConfigFields(): void
    {
        try {
            $settings = new Settings([
                'id' => 'global',
                'title' => __('Global Config', 'cardanopress'),
                'page' => self::OPTION_KEY,
                'fields' => [
                    'discussion' => $this->fields->getDiscussion(),
                    'policy' => $this->fields->getPolicy(),
                    'calculation' => $this->fields->getCalculation(),
                ],
            ]);

            $this->data->store($settings->get_config());
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function proposalSettings(): void
    {
        try {
            $post = new Post([
                'id' => 'proposal',
                'title' => __('Proposal Settings', 'cardanopress-governance'),
                'screen' => ['proposal'],
                'fields' => [
                    'id' => [
                        'title' => __('Identifier', 'cardanopress-governance'),
                        'type' => 'number',
                        'options' => ['min' => 1],
                        'required' => true,
                    ],
                    'discussion' => $this->fields->getDiscussion(),
                    'policy' => $this->fields->getPolicy(),
                    'calculation' => $this->fields->getCalculation(),
                    'options' => [
                        'title' => __('Options', 'cardanopress-governance'),
                        'type' => 'group',
                        'repeatable' => true,
                        'fields' => [
                            'value' => [
                                'title' => __('Value', 'cardanopress-governance'),
                                'type' => 'text',
                            ],
                            'label' => [
                                'title' => __('Label', 'cardanopress-governance'),
                                'type' => 'text',
                            ],
                        ],
                    ],
                ],
            ]);

            $this->data->store($post->get_config());
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function getOption(string $key)
    {
        $options = get_option(static::OPTION_KEY, []);
        $value = $options[$key] ?? '';

        if ($value) {
            return $value;
        }

        return $this->data->get_default(static::OPTION_KEY, $key);
    }

    public function proposalStatus(): void
    {
        try {
            $post = new Post([
                'id' => '_proposal',
                'title' => __('Proposal Status', 'cardanopress-governance'),
                'screen' => ['proposal'],
                'context' => 'side',
                'priority' => 'high',
                'fields' => [
                    'data' => $this->fields->getStatus(),
                ],
            ]);

            $this->data->store($post->get_config());
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function prepareProposalData(int $postId, WP_Post $post): void
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

    public function customizeProposalStatus(WP_Query $query): void
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

    public function noBlocksProposal(bool $status, string $postType): bool
    {
        if ('proposal' === $postType) {
            $status = false;
        }

        return $status;
    }
}
