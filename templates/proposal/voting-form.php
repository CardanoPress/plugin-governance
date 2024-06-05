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

$voted = $votedOption ?? '';
$currentStatus ??= 'publish';

?>

<fieldset<?php echo esc_html($proposal->isReady() ? '' : ' disabled="true"'); ?>>
    <?php foreach ($proposal->getOptions() as $option) : ?>
        <div
            x-id="['vote-option']"
            class="form-check py-1"
            :class="isWinner('<?php echo esc_js($option['value']); ?>') ? 'bg-success text-white' : ''"
        >
            <input
                class="form-check-input"
                type="radio"
                :id="$id('vote-option')"
                :disabled="isDisabled()"
                x-model="selected"
                value="<?php echo esc_attr($option['value']); ?>"
            >
            <label class='form-check-label' :for="$id('vote-option')"><?php echo esc_html($option['label']); ?></label>
        </div>
    <?php endforeach; ?>

    <?php if ('archive' !== $currentStatus || $voted) : ?>
        <div class="mt-3">
            <?php if ($voted) : ?>
                <p><b>You voted: <?php echo esc_html($proposal->getOptionLabel($voted)); ?></b></p>
            <?php else : ?>
                <?php echo do_shortcode('[cardanopress_template name="part/modal-trigger" if="!isConnected"]'); ?>

                <template x-if='isConnected'>
                    <button class="btn btn-primary" x-on:click="handleVote" :disabled="isDisabled(true)">Submit</button>
                </template>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</fieldset>
