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
        add_action('wp_ajax_cp-governance_proposal_vote_verify', [$this, 'verifyProposalVote']);
        add_action('wp_ajax_cp-governance_proposal_vote_complete', [$this, 'completeProposalVote']);
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

    protected function validatedVote(): Vote
    {
        check_ajax_referer('cardanopress-actions');

        if (empty($_POST['proposalId']) || empty($_POST['optionValue'])) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $proposalId = (int) sanitize_key($_POST['proposalId']);

        if (! is_numeric($proposalId) || 1 > $proposalId || $proposalId > 9999) {
            wp_send_json_error($this->getAjaxMessage('invalidIdentifier'));
        }

        $optionValue = sanitize_key($_POST['optionValue']);

        if (! is_numeric($optionValue) || 1 > $optionValue || $optionValue > 99) {
            wp_send_json_error($this->getAjaxMessage('invalidOption'));
        }

        $postId = Proposal::getPostId($proposalId);
        $proposal = new Proposal($postId);

        if (! $proposal->isReady() || $proposal->isComplete()) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        return new Vote($proposal, $optionValue);
    }

    public function verifyProposalVote(): void
    {
        $vote = $this->validatedVote();
        $proposal = $vote->getProposal();
        $userProfile = Application::getInstance()->userProfile();

        if ($userProfile->hasVoted($proposal->getID())) {
            wp_send_json_error(self::getAjaxMessage('alreadyVoted'));
        }

        $votingFee = $proposal->getFee();
        $votingPower = $proposal->getVotingPower($userProfile);

        if (0 === $votingPower) {
            wp_send_json_error($this->getAjaxMessage('noVotingPower'));
        }

        $votingFee['address'] = $votingFee['address'][$userProfile->connectedNetwork()];

        wp_send_json_success(compact('votingFee', 'votingPower'));
    }

    public function completeProposalVote(): void
    {
        $vote = $this->validatedVote();

        if (empty($_POST['transaction'])) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $transaction = sanitize_key($_POST['transaction']);

        if (! ctype_xdigit($transaction) || 64 !== strlen($transaction)) {
            wp_send_json_error($this->getAjaxMessage('invalidHash'));
        }

        $proposal = $vote->getProposal();
        $optionValue = $vote->getOptionValue();
        $userProfile = Application::getInstance()->userProfile();
        $votingPower = $proposal->getVotingPower($userProfile);
        $success = $proposal->updateData($optionValue, $votingPower);

        if (! $success) {
            wp_send_json_error($this->getAjaxMessage('somethingWrong'));
        }

        $userProfile->saveVote($proposal->getID(), $optionValue, $transaction, $votingPower);
        $userProfile->saveTransaction($userProfile->connectedNetwork(), 'payment', $transaction);

        wp_send_json_success([
            'message' => sprintf($this->getAjaxMessage('successfulVote'), $votingPower),
            'data' => $proposal->getData(),
        ]);
    }
}
