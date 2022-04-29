<?php

/**
 * The template for displaying a proposal content.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal-content.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

use PBWebDev\CardanoPress\Governance\Application;
use PBWebDev\CardanoPress\Governance\Proposal;
use PBWebDev\CardanoPress\Governance\Profile;

if (empty($proposalId)) {
    return;
}

$userProfile = new Profile(wp_get_current_user());
$proposal = new Proposal($proposalId);

?>

<div
    x-data="cardanoPressGovernance"
    id="proposal-<?php echo $proposalId; ?>>"
    data-options="<?php echo esc_attr(json_encode($proposal->getData())); ?>"
    data-voted="<?php echo $userProfile->hasVoted($proposalId); ?>"
>
    <div>
        <h2>Vote:</h2>

        <?php Application::instance()->template('proposal-form', ['proposal' => $proposal]); ?>
    </div>

    <div>
        <h2>Results:</h2>

        <?php Application::instance()->template('proposal-status', ['proposal' => $proposal]); ?>
    </div>
</div>
