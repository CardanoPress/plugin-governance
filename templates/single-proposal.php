<?php

/**
 * The template for displaying the single proposal.
 *
 * This can be overridden by copying it to yourtheme/single-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Application;
use ThemePlate\Enqueue;

Enqueue::asset('script', 'cp-governance-script');

get_header();

?>

<div class="container">
    <h2><?php the_title(); ?></h2>

    <?php Application::instance()->template('proposal-content', ['proposalId' => get_the_ID()]); ?>
</div>

<?php

get_footer();
