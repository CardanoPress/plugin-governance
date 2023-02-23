<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractAdmin;
use CardanoPress\Governance\Dependencies\ThemePlate\Meta\PostMeta;

class Admin extends AbstractAdmin
{
    public const OPTION_KEY = 'cp-governance';

    protected ProposalFields $proposalFields;
    protected ProposalCPT $proposalCPT;

    protected function initialize(): void
    {
        $this->proposalFields = new ProposalFields();
        $this->proposalCPT    = new ProposalCPT($this->getLogger());
    }

    public function setupHooks(): void
    {
        $this->proposalFields->setupHooks();
        $this->proposalCPT->setupHooks();

        $this->settingsPage('CardanoPress - Governance', [
            'parent' => 'edit.php?post_type=proposal',
            'menu_title' => 'Settings',
        ]);

        add_action('init', function () {
            $this->proposalArchiveFields();
            $this->proposalConfigFields();
            $this->proposalSettingsMetaBox();
            $this->proposalStatusMetaBox();
        }, 11);
    }

    private function proposalArchiveFields(): void
    {
        $this->optionFields(__('Proposal Archives', 'cardanopress-governance'), [
            'data_prefix' => 'proposal_',
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
    }

    private function proposalConfigFields(): void
    {
        $this->optionFields(__('Global Config', 'cardanopress-governance'), [
            'data_prefix' => 'global_',
            'fields' => [
                'discussion' => $this->proposalFields->getDiscussion(),
                'policy' => $this->proposalFields->getPolicy(),
                'calculation' => $this->proposalFields->getCalculation(),
            ],
        ]);
    }

    private function proposalSettingsMetaBox(): void
    {
        $postMeta = new PostMeta(__('Proposal Settings', 'cardanopress-governance'), [
            'data_prefix' => 'proposal_',
        ]);

        $postMeta->fields([
            'id' => [
                'title' => __('Identifier', 'cardanopress-governance'),
                'type' => 'number',
                'options' => [
                    'min' => 1,
                    'max' => 9999,
                ],
                'required' => true,
            ],
            'snapshot' => [
                'title' => __('Snapshot', 'cardanopress-governance'),
                'type' => 'group',
                'fields' => [
                    'date' => [
                        'title' => __('Date', 'cardanopress-governance'),
                        'type' => 'date',
                        'required' => true,
                    ],
                    'time' => [
                        'title' => __('Time', 'cardanopress-governance'),
                        'type' => 'time',
                        'required' => true,
                    ],
                ],
            ],
            'config' => $this->proposalFields->getConfig(),
            'discussion' => $this->proposalFields->getDiscussion(),
            'policy' => $this->proposalFields->getPolicy(),
            'calculation' => $this->proposalFields->getCalculation(),
            'options' => [
                'title' => __('Options', 'cardanopress-governance'),
                'type' => 'group',
                'repeatable' => true,
                'required' => true,
                'minimum' => 1,
                'maximum' => 99,
                'fields' => [
                    'value' => [
                        'title' => __('Value', 'cardanopress-governance'),
                        'type' => 'number',
                        'options' => [
                            'min' => 1,
                            'max' => 99,
                        ],
                    ],
                    'label' => [
                        'title' => __('Label', 'cardanopress-governance'),
                        'type' => 'text',
                    ],
                ],
            ],
        ]);

        $postMeta->location('proposal')->create();
        $this->storeConfig($postMeta->get_config());
    }

    private function proposalStatusMetaBox(): void
    {
        $postMeta = new PostMeta(__('Proposal Status', 'cardanopress-governance'), [
            'data_prefix' => '_proposal_',
            'context' => 'side',
            'priority' => 'high',
        ]);

        $postMeta->fields([
            'snapshot' => $this->proposalFields->getSchedule(),
            'data' => $this->proposalFields->getStatus(),
        ]);

        $postMeta->location('proposal')->create();
        $this->storeConfig($postMeta->get_config());
    }
}
