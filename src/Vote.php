<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Vote
{
    protected Proposal $proposal;
    protected string $optionValue;

    public function __construct(Proposal $proposal, string $optionValue)
    {
        $this->proposal = $proposal;
        $this->optionValue = $optionValue;
    }

    public function getProposal(): Proposal
    {
        return $this->proposal;
    }

    public function getOptionValue(): string
    {
        return $this->optionValue;
    }
}
