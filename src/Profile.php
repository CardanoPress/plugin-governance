<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use PBWebDev\CardanoPress\Profile as CoreProfile;

class Profile extends CoreProfile
{
    private string $prefix = 'cp_governance_';

    public function getMetaPrefix(): string
    {
        return $this->prefix;
    }

    public function getAllOwnedMeta(): array
    {
        $meta = array_filter(get_user_meta($this->user->ID), function ($key) {
            return 0 === strpos($key, $this->prefix);
        }, ARRAY_FILTER_USE_KEY);

        return array_map(static function ($a) {
            return $a[0];
        }, $meta);
    }

    public function saveVote(int $proposalId, string $option, string $transaction): bool
    {
        return (bool) update_user_meta($this->user->ID, $this->prefix . $proposalId, compact('option', 'transaction'));
    }

    public function getVote(int $proposalId): array
    {
        $saved = get_user_meta($this->user->ID, $this->prefix . $proposalId, true);

        return $saved ?: [
            'option' => '',
            'transaction' => '',
        ];
    }

    public function hasVoted(int $proposalId): string
    {
        $saved = $this->getVote($proposalId);

        return $saved['option'] ?? '';
    }
}
