<?php

/**
 * The template for displaying the proposal voting area.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-area.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

if (empty($proposal)) {
    $proposal = cpGovernance()->getProposalInstance(get_the_ID());
}

$userProfile = cpGovernance()->userProfile();
$votedOption = $userProfile->hasVoted($proposal->getID());
$currentStatus ??= 'publish';

?>

<div
    class="row"
    x-data="cardanoPressGovernance"
    id="proposal-<?php echo esc_attr($proposal->postId); ?>"
    data-proposal="<?php echo esc_attr($proposal->getID()); ?>"
    data-options="<?php echo esc_attr(json_encode($proposal->getData())); ?>"
    data-voted="<?php echo esc_attr($votedOption); ?>"
    data-complete="<?php echo esc_attr($proposal->isComplete()); ?>"
    data-power="<?php echo esc_attr($proposal->getVotingPower($userProfile)); ?>"
>
    <div class="col col-md-7">
        <h2><?php echo esc_html($proposal->getVoteText()); ?></h2>
        <hr/>

        <?php cpGovernance()->template(
            'proposal/voting-form',
            compact('proposal', 'votedOption', 'currentStatus')
        ); ?>
    </div>

    <div class="col col-md-5">
        <?php if ($votedOption || 'archive' === $currentStatus) : ?>
            <h2>Vote Stats</h2>
            <hr/>

            <?php cpGovernance()->template(
                'proposal/voting-status',
                compact('proposal')
            ); ?>
        <?php else : ?>
            <h2>Your voting power</h2>
            <hr/>

            <?php cpGovernance()->template(
                'proposal/voting-power',
                compact('proposal')
            ); ?>
        <?php endif; ?>
    </div>
</div>
