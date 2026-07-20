<?php

namespace Tests\Integration;

use PBWebDev\CardanoPress\Governance\Calculator;
use PBWebDev\CardanoPress\Governance\Profile;
use PBWebDev\CardanoPress\Governance\Proposal;
use Tests\LoadDependencies;
use WP_UnitTestCase;

class CalculatorTest extends WP_UnitTestCase
{
    use LoadDependencies;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadDependencies();
    }

    public function test_getToken_returns_zero_when_policy_is_empty(): void
    {
        $postDate = gmdate('Y-m-d H:i:s', strtotime('+1 day'));
        $postId = self::factory()->post->create([
            'post_type' => 'proposal',
            'post_status' => 'future',
            'post_date' => $postDate,
            'post_date_gmt' => $postDate,
        ]);
        self::assertIsInt($postId);

        update_post_meta($postId, 'proposal_policy', '');
        update_post_meta($postId, 'proposal_config', '');

        $proposal = new Proposal($postId);

        $userId = self::factory()->user->create();
        self::assertIsInt($userId);

        $user = get_user_by('ID', $userId);
        self::assertInstanceOf(\WP_User::class, $user);
        $profile = new Profile($user);

        $calculator = new Calculator($proposal, $profile);

        $assets = [
            ['unit' => 'lovelace', 'quantity' => 5000000],
            ['unit' => 'abc123token456', 'quantity' => 100],
        ];

        $reflection = new \ReflectionMethod($calculator, 'getToken');

        if (PHP_VERSION_ID < 80100) {
            $reflection->setAccessible(true);
        }

        $result = $reflection->invoke($calculator, $assets);

        $this->assertSame(0, $result);
    }
}
