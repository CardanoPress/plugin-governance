<?php

/**
 * The template for displaying the proposal voting status.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-status.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

if (empty($proposal)) {
    $proposal = cpGovernance()->getProposalInstance(get_the_ID());
}

?>

<div>
    <?php foreach ($proposal->getOptions() as $option) : ?>
        <div>
            <p>
                <strong><?php echo esc_html($option['label']); ?></strong>
                <span x-show="hasVoted('<?php echo esc_js($option['value']); ?>')">&curren;</span>
            </p>
            <p>
                <span x-text="getData('<?php echo esc_js($option['value']); ?>')">0</span> Votes
                (<span x-text="getData('<?php echo esc_js($option['value']); ?>', true)">0</span>%)
            </p>
        </div>
    <?php endforeach; ?>

    <p class="mt-4 mb-0 italic">*
        <strong><?php echo esc_html($proposal->getTotalVotes()); ?></strong> unique votes with
        <strong><?php echo esc_html($proposal->getTotalPower()); ?></strong> total voting power.
    </p>
</div>
