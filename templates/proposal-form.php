<?php

/**
 * The template for displaying a proposal content.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal-content.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

?>

<form>
    <?php foreach ($proposal->getOptions() as $option) : ?>
        <div x-id="['vote-option']">
            <input
                :id="$id('vote-option')"
                :disabled="isDisabled()"
                x-model="selected"
                type="radio"
                value="<?php echo $option['value']; ?>"
            >
            <label :for="$id('vote-option')"><?php echo $option['label']; ?></label>
        </div>
    <?php endforeach; ?>

    <template x-if='!isConnected'>
        <?php cardanoPress()->template('part/modal-trigger', ['text' => 'Connect Wallet']); ?>
    </template>

    <template x-if='isConnected'>
        <button @click="handleVote" :disabled="isDisabled(true)">Submit</button>
    </template>
</form>
