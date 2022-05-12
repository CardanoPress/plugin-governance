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
$discussionLink = $proposal->getDiscussionLink();

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

<div id="proposal-<?php echo $proposalId; ?>" class="py-5">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col col-md-10">
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
                <p><b>Snapshot: <?php echo $proposalDates['snapshot']; ?> UTC</b></p>

                <?php the_content(); ?>

                <?php if ($discussionLink['url']) : ?>
                    <div class="mt-4">
                        <a
                            class="btn btn-primary"
                            href="<?php echo $discussionLink['url']; ?>"
                            target="<?php echo $discussionLink['target']; ?>"
                        >
                            <?php echo $discussionLink['text']; ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ('future' !== $currentStatus) : ?>
                    <div class="mt-5">
                        <?php Application::instance()->template(
                            'proposal/voting-area',
                            compact('proposal', 'currentStatus')
                        ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php

get_footer();
