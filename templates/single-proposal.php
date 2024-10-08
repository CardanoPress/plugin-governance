<?php

/**
 * The template for displaying the single proposal.
 *
 * This can be overridden by copying it to yourtheme/single-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

$proposalId = get_the_ID();
$proposal = cpGovernance()->getProposalInstance($proposalId);
$discussionLink = $proposal->getDiscussionLink();
$currentStatus = get_post_status();

cardanoPress()->compatibleHeader();

?>

<div id="proposal-<?php echo esc_attr($proposalId); ?>" class="py-5">
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col col-md-10">
                <nav class="breadcrumb" style="--bs-breadcrumb-divider: ' ';" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo esc_url(home_url()); ?>">Home</a></li>
                        <li class="breadcrumb-item">
                            <a href='<?php echo esc_url(get_post_type_archive_link('proposal')); ?>'>Governance</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><?php the_title(); ?></li>
                    </ol>
                </nav>

                <h1 class="pb-3"><?php the_title(); ?></h1>
                <p><b>Status: <?php echo esc_html($proposal->getStatusText()); ?></b></p>
                <p><b><?php echo esc_html($proposal->getDateLabel() . ': ' . $proposal->getDateText()); ?></b></p>
                <p><b>Snapshot: <?php echo esc_html($proposal->getDate('snapshot')); ?></b></p>

                <?php the_content(); ?>

                <?php if ($discussionLink['url']) : ?>
                    <div class="mt-4">
                        <a
                            class="btn btn-primary"
                            href="<?php echo esc_url($discussionLink['url']); ?>"
                            target="<?php echo esc_attr($discussionLink['target']); ?>"
                        >
                            <?php echo esc_html($discussionLink['text'] ?: $discussionLink['url']); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ('future' !== $currentStatus) : ?>
                    <div class="mt-5">
                        <?php cpGovernance()->template(
                            'proposal/voting-area',
                            compact('proposal', 'currentStatus')
                        ); ?>
                    </div>

                    <div class="mt-5">
                        <?php cpGovernance()->template(
                            'proposal/voting-transactions',
                            compact('proposal')
                        ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php

cardanoPress()->compatibleFooter();
