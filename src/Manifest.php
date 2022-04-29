<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use PBWebDev\CardanoPress\Foundation\AbstractManifest;

class Manifest extends AbstractManifest
{
    protected function getAssetsBase(): string
    {
        return plugin_dir_url(CP_GOVERNANCE_FILE) . 'assets/dist/';
    }

    protected function getAssetPrefix(): string
    {
        return 'cp-governance-';
    }
}
