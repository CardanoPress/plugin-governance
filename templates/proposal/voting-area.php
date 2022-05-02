<?php

/**
 * The template for displaying the single proposal.
 *
 * This can be overridden by copying it to yourtheme/single-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Application;
use PBWebDev\CardanoPress\Governance\Profile;
use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

$userProfile = new Profile(wp_get_current_user());
$currentStatus ??= 'publish';
$voteText = 'Vote';

if ('archive' === $currentStatus) {
    $voteText = 'Voting Result';
}

get_header();

?>

    <div
        class="row"
        x-data="cardanoPressGovernance"
        id="proposal-<?php echo $proposal->postId; ?>"
        data-options="<?php echo esc_attr(json_encode($proposal->getData())); ?>"
        data-voted="<?php echo $userProfile->hasVoted($proposal->postId); ?>"
    >
        <div class="col col-md-7">
            <h2><?php echo $voteText; ?></h2>
            <hr/>

            <?php Application::instance()->template(
                'proposal/voting-form',
                compact('proposal')
            ); ?>
        </div>

        <div class="col col-md-5">
            <?php if ($userProfile->hasVoted($proposal->postId)) : ?>
                <h2>Vote Stats</h2>
                <hr/>

                <?php Application::instance()->template(
                    'proposal/voting-status',
                    compact('proposal')
                ); ?>
            <?php else : ?>
                <h2>Your voting power</h2>
                <hr/>

                <template x-if='!isConnected'>
                    <h2>Connect to see voting power</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab adipisci amet animi
                        corporis, culpa doloribus ducimus eius eos, et fuga hic iure necessitatibus non
                        nulla
                        pariatur rem sapiente similique voluptatem.</p>
                </template>

                <template x-if='isConnected'>
                    <h2><?php echo $proposal->getVotingPower($userProfile); ?>&curren;</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum nostrum sunt
                        voluptas. Assumenda consectetur illo, incidunt labore quia sequi voluptas! Ad
                        distinctio dolore fugiat iste iusto non officiis. Aut, repellat.</p>
                </template>
            <?php endif; ?>
        </div>
    </div>

<?php

get_footer();
