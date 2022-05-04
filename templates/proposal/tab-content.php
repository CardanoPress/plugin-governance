<?php

/**
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/tab-panel.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Proposal;

$proposalId = get_the_ID();
$proposal = new Proposal($proposalId);
$proposalDates = $proposal->getDates();

$type ??= 'current';

$linkText = 'Vote';
$voteText = 'Voting ends';
$voteDate = $proposalDates['end'];

if ('upcoming' === $type) {
    $linkText = 'View Proposal';
    $voteText = 'Voting starts';
    $voteDate = $proposalDates['start'];
} elseif ('past' === $type) {
    $linkText = 'View Results';
    $voteText = 'Voting ended';
}

?>

<div class="row align-items-center mt-3 border-bottom">
    <div class="col py-2">
        <h2><?php the_title(); ?></h2>
        <p><?php the_excerpt(); ?></p>
        <p><b><?php echo $voteText; ?>: <?php echo $voteDate; ?> UTC</b></p>
    </div>

    <div class="col-auto py-2">
        <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php echo $linkText; ?></a>
    </div>
</div>
