<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractShortcode;

class Shortcode extends AbstractShortcode
{
    protected Application $application;

    public function __construct()
    {
        $this->application = Application::getInstance();
    }

    public function setupHooks(): void
    {
        add_shortcode('cp-governance_power', [$this, 'doPower']);
    }

    public function doPower(): string
    {

        return '<span x-text="power"></span>';
    }
}
