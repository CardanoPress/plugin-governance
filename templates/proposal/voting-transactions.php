<?php

/**
 * The template for displaying the proposal voting transactions.
 *
 * This can be overridden by copying it to yourtheme/cardanopress/governance/proposal/voting-transactions.php.
 *
 * @package ThemePlate
 * @since   0.1.0
 */

if (empty($proposal)) {
    $proposal = cpGovernance()->getProposalInstance(get_the_ID());
}

$votes = $proposal->getCastedVotes();

?>

<h2>Current On Chain Votes</h2>
<hr/>

<table class="table table-borderless">
    <tbody>
        <?php if (! $votes) :?>
            <tr>Nothing to show here.</tr>
        <?php else : ?>
            <?php foreach ($votes as $casted) : ?>
                <tr>
                    <td>
                        <p><a href="<?php echo esc_url($casted['transaction']['link']); ?>" target="_blank">
                            <?php echo esc_html($casted['transaction']['hash']); ?>
                        </a></p>
                        <small><?php echo esc_html($casted['time']); ?></small>
                    </td>
                    <td><?php echo esc_html($casted['option']); ?></td>
                    <td><?php echo esc_html($casted['power']); ?>&curren;</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
