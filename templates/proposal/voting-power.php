<?php

/**
 * The template for displaying the proposal voting power.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-power.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

if (empty($proposal)) {
    $proposal = cpGovernance()->getProposalInstance(get_the_ID());
}

?>

<template x-if='!isConnected'>
    <div>
        <h3>Connect to see voting power</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab adipisci amet animi
            corporis, culpa doloribus ducimus eius eos, et fuga hic iure necessitatibus non
            nulla
            pariatur rem sapiente similique voluptatem.</p>
    </div>
</template>

<template x-if='isConnected'>
    <div>
        <h3><?php echo esc_html($proposal->getVotingPower(cpGovernance()->userProfile())); ?>&curren;</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Dolorum nostrum sunt
            voluptas. Assumenda consectetur illo, incidunt labore quia sequi voluptas! Ad
            distinctio dolore fugiat iste iusto non officiis. Aut, repellat.</p>
    </div>
</template>
