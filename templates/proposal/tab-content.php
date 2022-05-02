<?php

/**
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/tab-panel.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

$type ??= 'current';

$linkText = 'Vote';

if ('upcoming' === $type) {
    $linkText = 'View Proposal';
} elseif ('past' === $type) {
    $linkText = 'View Results';
}

?>

<div class="row align-items-center">
    <div class="col pt-2 pb-2">
        <h2><?php the_title(); ?></h2>
        <p><?php the_excerpt(); ?></p>
    </div>

    <div class="col-auto pt-2 pb-2">
        <a href="<?php the_permalink(); ?>" class="btn btn-primary me-2"><?php echo $linkText; ?></a>
    </div>
</div>

<hr>
