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
        add_action('wp_enqueue_scripts', [$this, 'localizeMessages'], 20);
    }

    public function getAjaxMessage(string $type): string
    {
        $messages = [
            'somethingWrong' => __('Something is wrong. Please try again', 'cardanopress-governance'),
            'invalidIdentifier' => __('Invalid proposal identifier', 'cardanopress-governance'),
            'invalidOption' => __('Invalid vote option', 'cardanopress-governance'),
            'invalidHash' => __('Invalid transaction hash', 'cardanopress-governance'),
            'alreadyVoted' => __('Sorry, you already voted', 'cardanopress-governance'),
            'noVotingPower' => __('Sorry, you do not have a voting power', 'cardanopress-governance'),
            'successfulVote' => __('Successfully voted %s', 'cardanopress-governance'),
        ];
        $messages = apply_filters('cp-governance-ajax_messages', $messages);

        return $messages[$type] ?? '';
    }

    public function localizeMessages()
    {
        $messages = [
            'voting' => __('Processing...', 'cardanopress-governance'),
            'invalid' => __('Invalid proposal ID', 'cardanopress-governance'),
        ];
        $messages = apply_filters('cp-governance-script_messages', $messages);

        wp_localize_script(Manifest::HANDLE_PREFIX . 'script', 'cardanoPressGovernanceMessages', $messages);
    }

    public function saveProposalVote(): void
    {
        check_ajax_referer('cardanopress-actions');

        if (empty($_POST['proposalId']) || empty($_POST['option']) || empty($_POST['transaction'])) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $proposalId = (int) sanitize_key($_POST['proposalId']);

        if (1 > $proposalId || $proposalId > 9999) {
            wp_send_json_error($this->getAjaxMessage('invalidIdentifier'));
        }

        $option = sanitize_key($_POST['option']);

        if (! is_numeric($option) || 1 > $option || $option > 99) {
            wp_send_json_error($this->getAjaxMessage('invalidOption'));
        }

        $transaction = sanitize_key($_POST['transaction']);

        if (! ctype_xdigit($transaction) || 64 !== strlen($transaction)) {
            wp_send_json_error($this->getAjaxMessage('invalidHash'));
        }

        $userProfile = Application::getInstance()->userProfile();

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

        $success = $proposal->updateData($option, $votingPower);

        if (! $success) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $userProfile->saveVote($proposalId, $option, $transaction, $votingPower);
        $userProfile->saveTransaction($userProfile->connectedNetwork(), 'payment', $transaction);

        wp_send_json_success([
            'message' => sprintf($this->getAjaxMessage('successfulVote'), $votingPower),
            'data' => $proposal->getData(),
        ]);
    }
}
