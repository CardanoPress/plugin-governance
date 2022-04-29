<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use PBWebDev\CardanoPress\Foundation\AbstractTemplates;

class Templates extends AbstractTemplates
{

    protected function getPathPrefix(): string
    {
        return 'cardanopress/governance/';
    }

    protected function getTitlePrefix(): string
    {
        return 'CP - Governance:';
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
