<?php

namespace Tests\Integration;

use PBWebDev\CardanoPress\Governance\Proposal;
use Tests\LoadDependencies;
use WP_UnitTestCase;

class ProposalTest extends WP_UnitTestCase
{
    use LoadDependencies;

    private int $postId;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadDependencies();

        $postId = self::factory()->post->create([
            'post_type' => 'proposal',
        ]);
        self::assertIsInt($postId);

        $this->postId = $postId;

        update_post_meta($this->postId, 'proposal_config', '');
    }

    public function test_getCalculation_with_empty_string_returns_empty_array(): void
    {
        update_post_meta($this->postId, 'proposal_calculation', '');

        $proposal = new Proposal($this->postId);

        $this->assertSame([], $proposal->getCalculation());
    }

    public function test_getCalculation_with_valid_array_returns_cast_array(): void
    {
        update_post_meta($this->postId, 'proposal_calculation', ['token' => '1']);

        $proposal = new Proposal($this->postId);

        $this->assertSame(['token' => '1'], $proposal->getCalculation());
    }

    public function test_getFee_with_empty_string_returns_empty_array(): void
    {
        update_post_meta($this->postId, 'proposal_fee', '');

        $proposal = new Proposal($this->postId);

        $this->assertSame([], $proposal->getFee());
    }

    public function test_getFee_with_valid_array_returns_cast_array(): void
    {
        update_post_meta($this->postId, 'proposal_fee', ['address' => ['mainnet' => 'addr1']]);

        $proposal = new Proposal($this->postId);

        $this->assertSame(['address' => ['mainnet' => 'addr1']], $proposal->getFee());
    }

    public function test_getOptionLabel_finds_int_stored_value_from_string_input(): void
    {
        add_post_meta($this->postId, 'proposal_options', ['label' => 'Yes', 'value' => 1]);
        add_post_meta($this->postId, 'proposal_options', ['label' => 'No', 'value' => 2]);

        $proposal = new Proposal($this->postId);

        $this->assertSame('Yes', $proposal->getOptionLabel('1'));
        $this->assertSame('No', $proposal->getOptionLabel('2'));
    }

    public function test_getOptionLabel_returns_empty_string_for_missing_value(): void
    {
        add_post_meta($this->postId, 'proposal_options', ['label' => 'Yes', 'value' => 1]);

        $proposal = new Proposal($this->postId);

        $this->assertSame('', $proposal->getOptionLabel('99'));
    }

    public function test_getDates_defaults_contain_no_html_entities(): void
    {
        $proposal = new Proposal($this->postId);
        $dates = $proposal->getDates();

        foreach ($dates as $value) {
            $this->assertStringNotContainsString('&', $value);
        }
    }
}
