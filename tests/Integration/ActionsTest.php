<?php

/**
 * @package ThemePlate
 */

namespace Tests\Integration;

use PBWebDev\CardanoPress\Governance\Actions;
use PBWebDev\CardanoPress\Governance\Application;
use PBWebDev\CardanoPress\Governance\Profile;
use Tests\LoadDependencies;
use WP_Ajax_UnitTestCase;
use WPAjaxDieContinueException;
use WPAjaxDieStopException;

class ActionsTest extends WP_Ajax_UnitTestCase
{
    use LoadDependencies;

    protected Actions $actions;

    public const REQUIRED_KEYS = [
        'proposalId',
        'option',
        'transaction',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->loadDependencies();

        $this->actions = new Actions();

        $this->actions->setupHooks();
    }

    protected function fill_post(array $values)
    {
        $data = array_combine(self::REQUIRED_KEYS, $values);
        $_POST = array_merge($_POST, $data);
    }

    protected function do_ajax()
    {
        try {
            $_POST['_wpnonce'] = wp_create_nonce('cardanopress-actions');

            $this->_handleAjax('cp-governance_proposal_vote');
        } catch (WPAjaxDieContinueException $exception) {
        } catch (WPAjaxDieStopException $exception) {
        }
    }

    public function for_with_incomplete_data(): array
    {
        return array_map(
            function ($key) {
                return [$key];
            },
            self::REQUIRED_KEYS
        );
    }

    /** @dataProvider for_with_incomplete_data */
    public function test_with_incomplete_data(string $missed): void
    {
        $this->fill_post(array_fill(0, 3, 'test'));
        unset($_POST[$missed]);
        $this->do_ajax();

        $output = json_decode($this->_last_response, true);

        $this->assertFalse($output['success']);
        $this->assertSame($this->actions->getAjaxMessage('somethingWrong'), $output['data']);
    }

    public function for_with_invalid_identifier(): array
    {
        return [
            [0],
            [10000],
            [99999999],
        ];
    }

    /** @dataProvider for_with_invalid_identifier */
    public function test_with_invalid_identifier(int $identifier): void
    {
        $this->fill_post(array($identifier, 'test', 'test'));
        $this->do_ajax();

        $output = json_decode($this->_last_response, true);

        $this->assertFalse($output['success']);

        if ( 0 === $identifier) {
            $this->assertSame($this->actions->getAjaxMessage('somethingWrong'), $output['data']);
        } else {
            $this->assertSame($this->actions->getAjaxMessage('invalidIdentifier'), $output['data']);
        }
    }

    public function for_with_invalid_vote(): array
    {
        return [
            ['0'],
            ['100'],
            ['9999'],
            ['test'],
        ];
    }

    /** @dataProvider for_with_invalid_vote */
    public function test_with_invalid_vote(string $option): void
    {
        $this->fill_post(array(9999, $option, 'test'));
        $this->do_ajax();

        $output = json_decode($this->_last_response, true);

        $this->assertFalse($output['success']);

        if ('0' === $option) {
            $this->assertSame($this->actions->getAjaxMessage('somethingWrong'), $output['data']);
        } else {
            $this->assertSame($this->actions->getAjaxMessage('invalidOption'), $output['data']);
        }
    }

    public function for_with_invalid_transaction(): array
    {
        return [
            ['test'],
            ['40ce33d3ec8811af'],
            ['69f01e8bd839a0d8c32697cb9c60e352'],
        ];
    }

    /** @dataProvider for_with_invalid_transaction */
    public function test_with_invalid_transaction(string $hash): void
    {
        $this->fill_post(array(9999, 99, $hash));
        $this->do_ajax();

        $output = json_decode($this->_last_response, true);

        $this->assertFalse($output['success']);
        $this->assertSame($this->actions->getAjaxMessage('invalidHash'), $output['data']);
    }
}
