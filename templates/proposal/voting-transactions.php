<?php

/**
 * The template for displaying the proposal voting transactions.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-transactions.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Profile;
use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

?>

<h2>Current On Chain Votes</h2>
<hr/>

<table class="table table-borderless">
    <tbody>
        <?php foreach ($proposal->getCastedVotes() as $userId => $casted) : ?>
            <?php $userProfile = new Profile(get_user_by('id', $userId)); ?>
            <tr>
                <td><?php echo $casted['transaction']; ?></td>
                <td><?php echo $casted['option']; ?></td>
                <td><?php echo $proposal->getVotingPower($userProfile); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
