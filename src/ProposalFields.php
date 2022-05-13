<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class ProposalFields
{
    protected array $policyIds = [];

    public function populate(): void
    {
        if (Application::isCoreActive()) {
            foreach (cardanoPress()->option('policy_ids') as $policy) {
                $this->policyIds[$policy['value']] = $policy['label'];
            }
        }
    }

    public function getStatus(): array
    {
        return [
            'type' => 'html',
            'default' => $this->getProposalData(),
        ];
    }

    protected function getProposalData()
    {
        if (! $this->inEditPage()) {
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
            'key' => '#proposal_config',
            'value' => false,
        ];
    }

    public function getConfig(): array
    {
        return [
            'title' => __('Use Global Config', 'cardanopress-governance'),
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
            $data['default'] = Application::instance()->option('global_discussion');
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
            $data['default'] = Application::instance()->option('global_policy');
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
            $data['default'] = array_values(Application::instance()->option('global_calculation'));
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
