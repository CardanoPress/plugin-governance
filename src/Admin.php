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
use WP_Post;
use WP_Query;

class Admin
{
    protected Data $data;

    public function __construct()
    {
        $this->data = new Data();

        add_action('init', [$this, 'proposalCPT']);
        add_action('init', [$this, 'proposalSettings']);
        add_action('init', [$this, 'proposalStatus']);
        add_action('wp_insert_post', [$this, 'prepareProposalData'], 10, 2);
        add_filter('pre_get_posts', [$this, 'customizeProposalStatus']);
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
                    'supports' => ['title', 'editor'],
                    'has_archive' => true,
                    'rewrite' => ['slug' => 'proposals'],
                ],
            ]);
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    public function proposalSettings(): void
    {
        $policyIds = [];

        if (Application::isCoreActive()) {
            foreach (cardanoPress()->option('policy_ids') as $policy) {
                $policyIds[$policy['value']] = $policy['label'];
            }
        }

        try {
            $post = new Post([
                'id' => 'proposal',
                'title' => __('Proposal Settings', 'cardanopress-governance'),
                'screen' => ['proposal'],
                'fields' => [
                    'policy' => [
                        'title' => __('Policy ID', 'cardanopress-governance'),
                        'type' => 'select',
                        'options' => $policyIds,
                    ],
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
                    'data' => [
                        'type' => 'html',
                        'default' => $this->getProposalData(),
                    ],
                ],
            ]);

            $this->data->store($post->get_config());
        } catch (Exception $exception) {
            Application::log($exception->getMessage());
        }
    }

    protected function getProposalData()
    {
        if (! $this->inCorrectPage()) {
            return '';
        }

        $proposal = new Proposal($_REQUEST['post']);

        ob_start();

        ?>
        <table>
            <?php foreach ($proposal->getData() as $key => $value) : ?>
                <tr>
                    <th><?php echo $key; ?></th>
                    <td><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php

        return ob_get_clean();
    }

    protected function inCorrectPage(): bool
    {
        if (empty($_REQUEST['post']) || wp_doing_ajax() || ! is_admin()) {
            return false;
        }

        global $pagenow;

        return 'post.php' === $pagenow && 'proposal' === get_post_type($_REQUEST['post']);
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
}
