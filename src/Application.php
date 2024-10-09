<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractApplication;
use CardanoPress\Traits\Configurable;
use CardanoPress\Traits\Enqueueable;
use CardanoPress\Traits\Instantiable;
use CardanoPress\Traits\Templatable;
use WP_Query;

class Application extends AbstractApplication
{
    use Configurable;
    use Enqueueable;
    use Instantiable;
    use Templatable;

    protected function initialize(): void
    {
        $this->setInstance($this);

        $path = plugin_dir_path($this->getPluginFile());
        $this->admin = new Admin($this->logger('admin'));
        $this->manifest = new Manifest($path . 'assets/dist', $this->getData('Version'));
        $this->templates = new Templates($path . 'templates');
    }

    public function setupHooks(): void
    {
        $this->admin->setupHooks();
        $this->manifest->setupHooks();
        $this->templates->setupHooks();

        add_action('cardanopress_loaded', [$this, 'init']);
    }

    public function init(): void
    {
        (new Snapshot($this->logger('snapshot')))->setupHooks();
        (new Actions())->setupHooks();
        (new Shortcode())->setupHooks();
    }

    public function isReady(): bool
    {
        $function = function_exists('cardanoPress');
        $namespace = 'PBWebDev\\CardanoPress\\';
        $admin = class_exists($namespace . 'Admin');
        $blockfrost = class_exists($namespace . 'Blockfrost');

        return $function && $admin && $blockfrost;
    }

    public function proposalTypes(): array
    {
        return array_keys(ProposalCPT::STATUSES);
    }

    public function getProposalQuery(string $type): WP_Query
    {
        return new WP_Query([
            'post_type' => 'proposal',
            'post_status' => ProposalCPT::STATUSES[$type] ?? 'publish',
            'posts_per_page' => -1,
        ]);
    }

    public function getProposalInstance(int $postId): Proposal
    {
        return new Proposal($postId);
    }

    public function userProfile(): Profile
    {
        static $user;

        if (null === $user) {
            $user = new Profile(wp_get_current_user());
        }

        return $user;
    }
}
