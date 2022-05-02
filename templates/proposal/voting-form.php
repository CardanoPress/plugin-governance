<?php

/**
 * The template for displaying a proposal form.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal-form.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Proposal;

if (! isset($proposal) || ! $proposal instanceof Proposal) {
    return;
}

$options = $proposal->getOptions();
$voted = $votedOption ?? '';
$currentStatus ??= 'publish';

?>

<fieldset<?php echo 'publish' === $currentStatus ? '' : ' disabled="true"'; ?>>
    <?php foreach ($options as $option) : ?>
        <div x-id="['vote-option']" class="form-check">
            <input
                class="form-check-input"
                type="radio"
                :id="$id('vote-option')"
                :disabled="isDisabled()"
                x-model="selected"
                value="<?php echo $option['value']; ?>"
            >
            <label class='form-check-label' :for="$id('vote-option')"><?php echo $option['label']; ?></label>
        </div>
    <?php endforeach; ?>

    <?php if ('archive' !== $currentStatus || $voted) : ?>
        <div class="pt-3">
            <?php if ($voted) : ?>
                <p><b>You voted: <?php echo $proposal->getOptionLabel($voted); ?></b></p>
            <?php else : ?>
                <template x-if='!isConnected'>
                    <?php cardanoPress()->template('part/modal-trigger', ['text' => 'Connect Wallet']); ?>
                </template>

                <template x-if='isConnected'>
                    <button class="btn btn-primary" @click="handleVote" :disabled="isDisabled(true)">Submit</button>
                </template>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</fieldset>
