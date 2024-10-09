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
        <?php echo do_shortcode(wp_kses_post(cpGovernance()->option('vpm_unconnected'))); ?>
    </div>
</template>

<template x-if='isConnected'>
    <div>
        <?php echo do_shortcode(wp_kses_post(cpGovernance()->option('vpm_connected'))); ?>
    </div>
</template>
