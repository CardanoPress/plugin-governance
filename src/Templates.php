<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractTemplates;

class Templates extends AbstractTemplates
{
    public const OVERRIDES_PREFIX = 'cardanopress/governance/';

    protected function initialize(): void
    {
    }

    protected function getLoaderFile(): string
    {
        $template = '';

        if (is_singular('proposal')) {
            $template = 'single-proposal.php';
        } elseif (is_post_type_archive('proposal')) {
            $template = 'archive-proposal.php';
        }

        return $template;
    }
}
