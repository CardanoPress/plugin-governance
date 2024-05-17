<?php

/**
 * The template for displaying the archive proposal.
 *
 * This can be overridden by copying it to yourtheme/archive-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

cardanoPress()->compatibleHeader();

?>

<div class="container py-5">
    <div class="row justify-content-md-center">
        <div class="col col-md-10">
            <nav class="breadcrumb" style="--bs-breadcrumb-divider: ' ';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url()); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Governance</li>
                </ol>
            </nav>

            <h1 class="pb-3"><?php echo esc_html(cpGovernance()->option('proposal_title')); ?></h1>

            <?php echo wp_kses_post(apply_filters('the_content', cpGovernance()->option('proposal_content'))); ?>
            <?php cpGovernance()->template('tabbed-proposals'); ?>
        </div>
    </div>
</div>

<?php

cardanoPress()->compatibleFooter();
