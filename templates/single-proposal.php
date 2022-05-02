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
use PBWebDev\CardanoPress\Governance\Proposal;

$proposalId = get_the_ID();
$proposal = new Proposal($proposalId);
$proposalDates = $proposal->getDates();

$currentStatus = get_post_status();
$statusText = 'Open for Voting';
$dateLabel = 'Closing Date';
$dateText = $proposalDates['end'];

if ('future' === $currentStatus) {
    $statusText = 'Upcoming';
    $dateLabel = 'Starting Date';
    $dateText = $proposalDates['start'];
} elseif ('archive' === $currentStatus) {
    $statusText = 'Complete';
}

get_header();

?>

    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col col-md-10 pt-5">
                <nav class="breadcrumb" style="--bs-breadcrumb-divider: ' ';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo home_url(); ?>">Home</a></li>
                        <li class="breadcrumb-item">
                            <a href='<?php echo get_post_type_archive_link('proposal'); ?>'>Governance</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                    </ol>
                </nav>

                <h1 class="pb-3"><?php the_title(); ?></h1>
                <p><b>Status: <?php echo $statusText; ?></b>
                <p><b><?php echo $dateLabel; ?>: <?php echo $dateText; ?> UTC</b></p>

                <?php the_content(); ?>

                <p class="pt-3"><a href="#" class="btn btn-primary">Discuss Proposal</a></p>
            </div>
        </div>

        <?php if ('future' !== $currentStatus) : ?>
            <div class="row justify-content-md-center">
                <div class="col col-md-10 pt-5">
                    <?php Application::instance()->template(
                        'proposal/voting-area',
                        compact('proposal', 'currentStatus')
                    ); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php

get_footer();
