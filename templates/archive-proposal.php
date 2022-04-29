<?php

/**
 * The template for displaying the archive proposal.
 *
 * This can be overridden by copying it to yourtheme/archive-proposal.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Application;

$types = ['current', 'upcoming', 'past'];

$current = new WP_Query([
    'post_type' => 'proposal',
    'post_status' => 'publish',
    'post_per_page' => -1,
]);

$upcoming = new WP_Query([
    'post_type' => 'proposal',
    'post_status' => 'future',
    'post_per_page' => -1,
]);

$past = new WP_Query([
    'post_type' => 'proposal',
    'post_status' => 'archive',
    'post_per_page' => -1,
]);

get_header();

?>

<div class="container">
    <div class="row justify-content-md-center">
        <div class="col col-md-10 pt-5">
            <nav class="breadcrumb" style="--bs-breadcrumb-divider: ' ';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo home_url(); ?>">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Governance</li>
                </ol>
            </nav>

            <h1 class="pb-3">Project Governance</h1>
            <p>Vote on upcoming decision of the projects DAO.</p>
            <p class="pb-5">Submit a proposal for discussion or vote in current proposals in our ecosystem.</p>

            <ul class="nav nav-tabs pt-5" id="tab-proposal" role="tablist">
                <?php foreach ($types as $index => $type) : ?>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link<?php echo 0 === $index ? ' active' : ''; ?>"
                            id="<?php echo $type; ?>-tab-toggle"
                            role="tab"
                            data-bs-toggle="tab"
                            data-bs-target="#<?php echo $type; ?>-tab-panel"
                            aria-controls="<?php echo $type; ?>-tab-panel"
                            aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
                        >
                            <?php echo ucfirst($type); ?> Proposal
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content pt-5" id="content-proposal">
                <?php foreach ($types as $index => $type) : ?>
                    <div
                        class="tab-pane fade<?php echo 0 === $index ? ' show active' : ''; ?>"
                        id="<?php echo $type; ?>-tab-panel"
                        role="tabpanel"
                        aria-labelledby="<?php echo $type; ?>-tab-toggle"
                    >
                        <div class="container gx-1">
                            <?php while (${$type}->have_posts()) : ?>
                                <?php ${$type}->the_post(); ?>

                                <?php Application::instance()->template('proposal/tab-content'); ?>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php

get_footer();
