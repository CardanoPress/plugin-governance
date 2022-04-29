<?php

/**
 * The template for displaying a proposal content.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal-content.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

?>

<div>
    <?php foreach ($proposal->getOptions() as $option) : ?>
        <div>
            <p>
                <?php echo $option['label']; ?>
                <span x-show="hasVoted('<?php echo $option['value']; ?>')">&curren;</span>
            </p>
            <p>
                <span x-text="getData('<?php echo $option['value']; ?>')">0</span> Votes
                (<span x-text="getData('<?php echo $option['value']; ?>', true)">0</span>%)
            </p>
        </div>
    <?php endforeach; ?>
</div>
