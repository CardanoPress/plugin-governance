<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use Exception;
use Monolog\Logger;
use ThemePlate\Core\Data;
use ThemePlate\Meta\Post;
use ThemePlate\Page;
use ThemePlate\Settings;

class Admin
{
    protected Data $data;
    protected Logger $logger;
    protected ProposalFields $proposalFields;
    protected ProposalCPT $proposalCPT;

    public const OPTION_KEY = 'cp-governance';

    public function __construct()
    {
        $this->data = new Data();
        $this->logger = Application::logger('admin');
    }

    protected function log(string $message, string $level = 'error'): void
    {
        $this->logger->log($level, $message);
    }

    public function init(): void
    {
        $this->proposalFields = new ProposalFields();
        $this->proposalCPT = new ProposalCPT($this->logger);

        $this->proposalFields->populate();
        $this->proposalCPT->register();
        $this->settingsPage();
        $this->proposalArchiveFields();
        $this->proposalConfigFields();
        $this->proposalSettingsMetaBox();
        $this->proposalStatusMetaBox();
    }

    public function settingsPage(): void
    {
        try {
            new Page([
                'id' => self::OPTION_KEY,
                'parent' => 'edit.php?post_type=proposal',
                'menu' => 'Settings',
                'title' => 'CardanoPress - Governance',
            ]);
        } catch (Exception $exception) {
            $this->log($exception->getMessage());
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
            $this->log($exception->getMessage());
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
                    'discussion' => $this->proposalFields->getDiscussion(),
                    'policy' => $this->proposalFields->getPolicy(),
                    'calculation' => $this->proposalFields->getCalculation(),
                ],
            ]);

            $this->data->store($settings->get_config());
        } catch (Exception $exception) {
            $this->log($exception->getMessage());
        }
    }

    public function proposalSettingsMetaBox(): void
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
            $this->log($exception->getMessage());
        }
    }

    public function proposalStatusMetaBox(): void
    {
        try {
            $post = new Post([
                'id' => '_proposal',
                'title' => __('Proposal Status', 'cardanopress-governance'),
                'screen' => ['proposal'],
                'context' => 'side',
                'priority' => 'high',
                'fields' => [
                    'data' => $this->proposalFields->getStatus(),
                ],
            ]);

            $this->data->store($post->get_config());
        } catch (Exception $exception) {
            $this->log($exception->getMessage());
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
}
