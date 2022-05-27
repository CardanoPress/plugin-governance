<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractProfile;

class Profile extends AbstractProfile
{
    private string $prefix = 'cp_governance_';

    protected function initialize(): void
    {
    }

    public function getMetaPrefix(): string
    {
        return $this->prefix;
    }

    public function getAllOwnedMeta(): array
    {
        $meta = array_filter($this->getMeta(''), function ($key) {
            return 0 === strpos($key, $this->prefix);
        }, ARRAY_FILTER_USE_KEY);

        return array_map(static function ($a) {
            return $a[0];
        }, $meta);
    }

    public function saveVote(int $proposalId, string $option, string $transaction, int $power): bool
    {
        $time = time();

        return (bool) $this->updateMeta(
            $this->prefix . $proposalId,
            compact('option', 'transaction', 'power', 'time')
        );
    }

    public function getVote(int $proposalId): array
    {
        $saved = $this->getMeta($this->prefix . $proposalId, true);

        return $saved ?: [
            'option' => '',
            'transaction' => '',
            'power' => '',
            'time' => '',
        ];
    }

    public function hasVoted(int $proposalId): string
    {
        $saved = $this->getVote($proposalId);

        return $saved['option'] ?? '';
    }
}
