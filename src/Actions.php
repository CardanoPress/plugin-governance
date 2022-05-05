<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Actions
{
    public function __construct()
    {
        add_action('wp_ajax_cp-governance_proposal_vote', [$this, 'saveProposalVote']);
    }

    public function saveProposalVote(): void
    {
        check_ajax_referer('cardanopress-actions');

        $proposalId = (int) $_POST['proposalId'];
        $userProfile = new Profile(wp_get_current_user());

        if ($userProfile->hasVoted($proposalId)) {
            wp_send_json_error(__('Sorry, you already voted', 'cardanopress-governance'));
        }

        $postId = Proposal::getPostId($proposalId);
        $proposal = new Proposal($postId);
        $votingPower = $proposal->getVotingPower($userProfile);

        if (0 === $votingPower) {
            wp_send_json_error(__('Sorry, you do not have a voting power', 'cardanopress-governance'));
        }

        $success = $proposal->updateData($_POST['option'], $votingPower);

        if (! $success) {
            wp_send_json_error(__('Something is wrong. Please try again', 'cardanopress-governance'));
        }

        $userProfile->saveVote($proposalId, $_POST['option']);

        wp_send_json_success([
            'message' => __('Successfully voted ' . $votingPower, 'cardanopress-governance'),
            'data' => $proposal->getData(),
        ]);
    }
}
