<?php

/**
 * The template for displaying the tabbed proposals.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/tabbed-proposals.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

$types = cpGovernance()->proposalTypes();

?>

<div class="mt-5">
    <ul class="nav nav-tabs" id="tab-proposal" role="tablist">
        <?php foreach ($types as $index => $type) : ?>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link<?php echo 0 === $index ? ' active' : ''; ?>"
                    id="<?php echo esc_attr($type); ?>-tab-toggle"
                    role="tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?php echo esc_attr($type); ?>-tab-panel"
                    aria-controls="<?php echo esc_attr($type); ?>-tab-panel"
                    aria-selected="<?php echo esc_attr(0 === $index ? 'true' : 'false'); ?>"
                >
                    <?php echo esc_html(ucfirst($type)); ?> Proposal
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content" id="content-proposal">
        <?php foreach ($types as $index => $type) : ?>
            <?php $query = cpGovernance()->getProposalQuery($type); ?>
            <div
                class="tab-pane fade<?php echo 0 === $index ? ' show active' : ''; ?>"
                id="<?php echo esc_attr($type); ?>-tab-panel"
                role="tabpanel"
                aria-labelledby="<?php echo esc_attr($type); ?>-tab-toggle"
            >
                <div class="container gx-0">
                    <?php while ($query->have_posts()) : ?>
                        <?php $query->the_post(); ?>

                        <?php cpGovernance()->template('proposal/tab-content', compact('type')); ?>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
