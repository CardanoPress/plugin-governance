<?php

/**
 * The template for displaying the single proposal.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-power.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Profile;
use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

if (! isset($userProfile) || ! $userProfile instanceof Profile) {
    $userProfile = new Profile(wp_get_current_user());
}

?>

<template x-if='!isConnected'>
    <div>
        <h2>Connect to see voting power</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab adipisci amet animi
            corporis, culpa doloribus ducimus eius eos, et fuga hic iure necessitatibus non
            nulla
            pariatur rem sapiente similique voluptatem.</p>
    </div>
</template>

<template x-if='isConnected'>
    <div>
        <h2><?php echo $proposal->getVotingPower($userProfile); ?>&curren;</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum nostrum sunt
            voluptas. Assumenda consectetur illo, incidunt labore quia sequi voluptas! Ad
            distinctio dolore fugiat iste iusto non officiis. Aut, repellat.</p>
    </div>
</template>
