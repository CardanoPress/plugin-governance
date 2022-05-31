<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Interfaces\HookInterface;

class Actions implements HookInterface
{
    public function setupHooks(): void
    {
        add_action('wp_ajax_cp-governance_proposal_vote', [$this, 'saveProposalVote']);
    }

    public function getAjaxMessage(string $type): string
    {
        $messages = [
            'somethingWrong' => __('Something is wrong. Please try again', 'cardanopress-governance'),
            'alreadyVoted' => __('Sorry, you already voted', 'cardanopress-governance'),
            'noVotingPower' => __('Sorry, you do not have a voting power', 'cardanopress-governance'),
            'successfulVote' => __('Successfully voted %s', 'cardanopress-governance'),
        ];
        $messages = apply_filters('cp-governance-ajax_messages', $messages);

        return $messages[$type] ?? '';
    }

    public function saveProposalVote(): void
    {
        check_ajax_referer('cardanopress-actions');

        if (empty($_POST['proposalId']) || empty($_POST['option']) || empty($_POST['transaction'])) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $proposalId = (int) $_POST['proposalId'];
        $userProfile = new Profile(wp_get_current_user());

        if ($userProfile->hasVoted($proposalId)) {
            wp_send_json_error(self::getAjaxMessage('alreadyVoted'));
        }

        $postId = Proposal::getPostId($proposalId);
        $proposal = new Proposal($postId);

        if (! $proposal->isReady() || $proposal->isComplete()) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $votingPower = $proposal->getVotingPower($userProfile);

        if (0 === $votingPower) {
            wp_send_json_error($this->getAjaxMessage('noVotingPower'));
        }

        $success = $proposal->updateData($_POST['option'], $votingPower);

        if (! $success) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $userProfile->saveVote($proposalId, $_POST['option'], $_POST['transaction'], $votingPower);
        $userProfile->saveTransaction($userProfile->connectedNetwork(), 'payment', $_POST['transaction']);

        wp_send_json_success([
            'message' => sprintf($this->getAjaxMessage('successfulVote'), $votingPower),
            'data' => $proposal->getData(),
        ]);
    }
}
