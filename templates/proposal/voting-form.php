<?php

/**
 * The template for displaying the proposal voting form.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-form.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

if (empty($proposal)) {
    $proposal = cpGovernance()->getProposalInstance(get_the_ID());
}

$options = $proposal->getOptions();
$voted = $votedOption ?? '';
$currentStatus ??= 'publish';

?>

<fieldset<?php echo $proposal->isReady() ? '' : ' disabled="true"'; ?>>
    <?php foreach ($options as $option) : ?>
        <div
            x-id="['vote-option']"
            class="form-check py-1"
            :class="isWinner('<?php echo $option['value']; ?>') ? 'bg-success text-white' : ''"
        >
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
        <div class="mt-3">
            <?php if ($voted) : ?>
                <p><b>You voted: <?php echo $proposal->getOptionLabel($voted); ?></b></p>
            <?php else : ?>
                <?php echo do_shortcode('[cardanopress_template name="part/modal-trigger" if="!isConnected"]'); ?>

                <template x-if='isConnected'>
                    <button class="btn btn-primary" @click="handleVote" :disabled="isDisabled(true)">Submit</button>
                </template>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</fieldset>
