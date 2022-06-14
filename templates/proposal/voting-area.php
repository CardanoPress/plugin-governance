<?php

/**
 * The template for displaying the proposal voting area.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-area.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

$userProfile = cpGovernance()->userProfile();
$votedOption = $userProfile->hasVoted($proposal->getID());
$proposalData = $proposal->getData();
$currentStatus ??= 'publish';
$voteText = 'Vote';

if ('archive' === $currentStatus) {
    $voteText = 'Voting Result';
}

?>

<div
    class="row"
    x-data="cardanoPressGovernance"
    id="proposal-<?php echo $proposal->postId; ?>"
    data-proposal="<?php echo $proposal->getID(); ?>"
    data-options="<?php echo esc_attr(json_encode($proposalData)); ?>"
    data-voted="<?php echo $votedOption; ?>"
    data-complete="<?php echo $proposal->isComplete(); ?>"
>
    <div class="col col-md-7">
        <h2><?php echo $voteText; ?></h2>
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
