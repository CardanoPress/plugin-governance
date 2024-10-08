<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Interfaces\HookInterface;

class ProposalFields implements HookInterface
{
    protected Application $application;
    protected array $policyIds = [];

    public function __construct()
    {
        $this->application = Application::getInstance();
    }

    public function setupHooks(): void
    {
        add_action('init', function () {
            if ($this->application->isReady()) {
                foreach (cardanoPress()->option('policy_ids') as $policy) {
                    $this->policyIds[$policy['value']] = $policy['label'];
                }
            }
        }, 11);

        foreach (['id', 'options'] as $metaKey) {
            add_filter('sanitize_post_meta_proposal_' . $metaKey . '_for_proposal', [$this, 'sanitizeNumber'], 10, 2);
        }
    }

    /**
     * @param mixed $metaValue
     * @param string $metaKey
     * @return mixed
     */
    public function sanitizeNumber($metaValue, string $metaKey)
    {
        if ('proposal_id' === $metaKey) {
            return (int) $metaValue;
        }

        $metaValue['value'] = (int) $metaValue['value'];

        return $metaValue;
    }

    public function getStatus(): array
    {
        return [
            'type' => 'html',
            'options' => array($this, 'parseData'),
        ];
    }

    public function parseData($data)
    {
        if (! $this->inEditPage()) {
            return '';
        }

        ob_start();

        ?>
        <table>
            <?php foreach ($data as $key => $value) : ?>
                <tr>
                    <th><?php echo esc_html($key); ?></th>
                    <td><?php echo esc_html($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php

        return ob_get_clean();
    }

    protected function inEditPage(): bool
    {
        if (empty($_REQUEST['post']) || wp_doing_ajax() || ! is_admin()) {
            return false;
        }

        global $pagenow;

        return 'post.php' === $pagenow && 'proposal' === get_post_type($_REQUEST['post']);
    }

    protected function inAddNewPage(): bool
    {
        if (empty($_REQUEST['post_type']) || wp_doing_ajax() || ! is_admin()) {
            return false;
        }

        global $pagenow;

        return 'post-new.php' === $pagenow && 'proposal' === $_REQUEST['post_type'];
    }

    protected function showOnData(): array
    {
        return [
            'key' => '#themeplate_proposal_config',
            'value' => false,
        ];
    }

    public function getConfig(): array
    {
        $installer = new Installer(cpGovernance());

        return [
            'title' => __('Use Global Config', 'cardanopress-governance'),
            'description' => $installer->getSettingsLink(__('Set here', 'cardanopress-governance'), '_blank'),
            'type' => 'checkbox',
            'default' => $this->inAddNewPage(),
        ];
    }

    public function getDiscussion(): array
    {
        $data = [
            'title' => __('Discussion Link', 'cardanopress-governance'),
            'type' => 'link',
        ];

        if ($this->inAddNewPage()) {
            $data['default'] = $this->application->option('global_discussion');

            if (empty($data['default'])) {
                unset($data['default']);
            }
        }

        if ($this->inAddNewPage() || $this->inEditPage()) {
            $data['show_on'] = $this->showOnData();
        }

        return $data;
    }

    public function getPolicy(): array
    {
        $data = [
            'title' => __('Policy ID', 'cardanopress-governance'),
            'type' => 'select',
            'options' => $this->policyIds,
            'required' => true,
        ];

        if ($this->inAddNewPage()) {
            $data['default'] = $this->application->option('global_policy');
        }

        if ($this->inAddNewPage() || $this->inEditPage()) {
            $data['show_on'] = $this->showOnData();
        }

        return $data;
    }

    public function getCalculation(): array
    {
        $data = [
            'title' => __('Power Calculation', 'cardanopress-governance'),
            'type' => 'checkbox',
            'options' => [
                'ada' => __('Amount of ADA', 'cardanopress-governance'),
                'token' => __('# of token by Policy ID', 'cardanopress-governance'),
            ],
            'default' => ['token'],
        ];

        if ($this->inAddNewPage()) {
            $global = $this->application->option('global_calculation');

            if ($global) {
                $data['default'] = array_values((array)$global);
            }
        }

        if ($this->inAddNewPage() || $this->inEditPage()) {
            $data['show_on'] = $this->showOnData();
        }

        return $data;
    }

    public function getFee(): array
    {
        $data = [
            'title' => __('Voting Fee', 'cardanopress-governance'),
            'description' => __('*Optional*<br><br>Leave wallet address field empty to disable.', 'cardanopress-governance'),
            'type' => 'group',
            'fields' => [
                'amount' => [
                    'title' => __('Amount in ADA', 'cardanopress-governance'),
                    'type' => 'number',
                    'default' => 0,
                    'options' => [
                        'min' => 0.1,
                        'step' => 0.1,
                    ],
                ],
                'address' => [
                    'title' => __('Wallet Address', 'cardanopress-governance'),
                    'type' => 'group',
                    'default' => [
                        'mainnet' => '',
                        'testnet' => '',
                    ],
                    'fields' => [
                        'mainnet' => [
                            'title' => __('Mainnet', 'cardanopress-governance'),
                            'type' => 'text',
                        ],
                        'testnet' => [
                            'title' => __('Testnet', 'cardanopress-governance'),
                            'description' => __('For networks preview and preprod', 'cardanopress-governance'),
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ];

        if ($this->inAddNewPage()) {
            $global = $this->application->option('global_calculation');

            if ($global) {
                $data['default'] = array_values((array)$global);
            }
        }

        if ($this->inAddNewPage() || $this->inEditPage()) {
            $data['show_on'] = $this->showOnData();
        }

        return $data;
    }

    public function getSchedule(): array
    {
        return [
            'type' => 'html',
            'default' => $this->getProposalSnapshot(),
        ];
    }

    protected function getProposalSnapshot(): string
    {
        if (! $this->inEditPage()) {
            return '';
        }

        $text = __('Unscheduled', 'cardanopress-governance');

        if (Snapshot::isScheduled($_REQUEST['post'])) {
            $text = __('Scheduled', 'cardanopress-governance');
        } elseif (Snapshot::wasScheduled($_REQUEST['post'])) {
            $text = __('Completed', 'cardanopress-governance');
        }

        return sprintf(__('Snapshot: <b>%s</b>', 'cardanopress-governance'), $text);
    }
}
