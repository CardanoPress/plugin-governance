<?php

/**
 * The template for displaying the proposal voting status.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-status.php.
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
                <strong><?php echo $option['label']; ?></strong>
                <span x-show="hasVoted('<?php echo $option['value']; ?>')">&curren;</span>
            </p>
            <p>
                <span x-text="getData('<?php echo $option['value']; ?>')">0</span> Votes
                (<span x-text="getData('<?php echo $option['value']; ?>', true)">0</span>%)
            </p>
        </div>
    <?php endforeach; ?>

    <p class="mt-4 mb-0 italic">*
        <strong><?php echo $proposal->getTotalVotes(); ?></strong> unique votes with
        <strong><?php echo $proposal->getTotalPower(); ?></strong> total voting power.
    </p>
</div>
