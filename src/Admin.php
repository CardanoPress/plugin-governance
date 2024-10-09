<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractAdmin;
use CardanoPress\Dependencies\ThemePlate\Meta\PostMeta;

class Admin extends AbstractAdmin
{
    public const OPTION_KEY = 'cp-governance';

    protected ProposalFields $proposalFields;
    protected ProposalCPT $proposalCPT;

    protected function initialize(): void
    {
        require_once plugin_dir_path(CP_GOVERNANCE_FILE) . 'class-tgm-plugin-activation.php';

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

        add_action('tgmpa_register', [$this, 'recommendPlugins']);
        add_action('init', function () {
            $this->proposalArchiveFields();
            $this->proposalConfigFields();
            $this->proposalMessagesFields();
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
                'fee' => $this->proposalFields->getFee(),
            ],
        ]);
    }


    private function proposalMessagesFields(): void
    {
        $this->optionFields(__('Voting Power Messages', 'cardanopress-governance'), [
            'data_prefix' => 'vpm_',
            'fields' => [
                'connected' => [
                    'title' => __('Connected', 'cardanopress-governance'),
                    'type' => 'editor',
                    'default' => '<h3>[cp-governance_power]&curren;</h3>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum nostrum sunt voluptas. Assumenda consectetur illo, incidunt labore quia sequi voluptas! Ad distinctio dolore fugiat iste iusto non officiis. Aut, repellat.</p>'
                ],
                'unconnected' => [
                    'title' => __('Un-connected', 'cardanopress-governance'),
                    'type' => 'editor',
                    'default' => '<h3>Connect to see voting power</h3>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab adipisci amet animi corporis, culpa doloribus ducimus eius eos, et fuga hic iure necessitatibus non nulla pariatur rem sapiente similique voluptatem.</p>'
                ],
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
            'fee' => $this->proposalFields->getFee(),
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

    public function recommendPlugins()
    {
        $plugins = [
            [
                'name'     => 'CardanoPress',
                'slug'     => 'cardanopress',
                'required' => true,
            ],
            [
                'name' => 'Augment Types',
                'slug' => 'augment-types',
            ],
        ];

        $config = [
            'id' => 'cardanopress-tgmpa',
            'menu' => 'cardanopress-plugins',
            'parent_slug' => 'cardanopress',
            'dismissable' => true,
            'is_automatic' => true,
        ];

        tgmpa($plugins, $config);
    }
}
