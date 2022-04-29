<?php

/**
 * The template for displaying the single proposal.
 *
 * This can be overridden by copying it to yourtheme/single-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

get_header();

?>

<div class="container">
    <h1>Proposals</h1>

    <ul>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>

            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
        <?php endwhile; ?>
    </ul>
</div>

<?php

get_footer();
