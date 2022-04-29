<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

class Proposal
{
    private int $postId;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function toArray(): array
    {
        return [
            'policy' => $this->getPolicy(),
            'options' => $this->getOptions(),
            'data' => $this->getData(),
        ];
    }

    public function getPolicy(): string
    {
        $status = get_post_meta($this->postId, 'proposal_policy', true);

        return $status ?: '';
    }

    public function getOptions(): array
    {
        $status = get_post_meta($this->postId, 'proposal_options', false);

        return $status ?: [];
    }

    public function getData(): array
    {
        $status = get_post_meta($this->postId, '_proposal_data', true);

        return $status ?: [];
    }

    public function updateData(string $option, int $value, bool $increase = true): bool
    {
        $data = $this->getData();

        if (! isset($data[$option])) {
            return false;
        }

        $current = $data[$option];

        if ($increase) {
            $data[$option] = $current + $value;
        } else {
            $data[$option] = $current - $value;
        }

        return (bool) update_post_meta($this->postId, '_proposal_data', $data);
    }
}
