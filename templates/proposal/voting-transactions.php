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

$link = [
    'mainnet' => 'https://cardanoscan.io/transaction/',
    'testnet' => 'https://testnet.cardanoscan.io/transaction/',
];

?>

<h2>Current On Chain Votes</h2>
<hr/>

<table class="table table-borderless">
    <tbody>
        <?php foreach ($proposal->getCastedVotes() as $userId => $casted) : ?>
            <?php $userProfile = new Profile(get_user_by('id', $userId)); ?>
            <tr>
                <td>
                    <a
                        href="<?php echo $link[$userProfile->connectedNetwork()] . $casted['transaction']; ?>"
                        target="_blank"
                    >
                        <?php echo $casted['transaction']; ?>
                    </a>
                </td>
                <td><?php echo $proposal->getOptionLabel($casted['option']); ?></td>
                <td><?php echo $proposal->getVotingPower($userProfile); ?>&curren;</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
