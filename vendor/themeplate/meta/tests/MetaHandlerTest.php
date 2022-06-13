<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Core\Field\InputField;
use ThemePlate\Meta\MetaHandler;
use WP_UnitTestCase;

class MetaHandlerTest extends WP_UnitTestCase {
	private InputField $field;
	private string $data_key = 'test';
	private string $default  = 'important!';

	public function setUp(): void {
		$config = array( 'default' => $this->default );

		if ( 'test_handling_repeatable' === $this->getName() ) {
			$config['repeatable'] = true;
		}

		$this->field = new InputField( $this->data_key, $config );
	}

	public function test_handling_values_and_default(): void {
		$data_type = 'post';
		$handler   = new MetaHandler( $data_type );
		$post_id   = $this->factory()->post->create();

		// Non-existent meta; returns default
		$this->assertSame( $this->default, $handler->get_value( $this->field, '', $post_id ) );

		add_metadata( $data_type, $post_id, $this->data_key, 'wanted', true );

		// Existing meta retrieves current value
		$this->assertSame( 'wanted', $handler->get_value( $this->field, '', $post_id ) );
	}

	public function test_handling_with_data_prefix(): void {
		$data_type = 'term';
		$prefix    = 'mine-';
		$handler   = new MetaHandler( $data_type );
		$term_id   = $this->factory()->term->create();

		add_metadata( $data_type, $term_id, $this->data_key, 'no-prefix-value', true );
		$this->assertSame( 'no-prefix-value', $handler->get_value( $this->field, '', $term_id ) );

		add_metadata( $data_type, $term_id, $prefix . $this->data_key, 'with-prefix-value', true );
		$this->assertSame( 'with-prefix-value', $handler->get_value( $this->field, $prefix, $term_id ) );
	}

	public function test_handling_repeatable(): void {
		$data_type = 'user';
		$handler   = new MetaHandler( $data_type );
		$user_id   = $this->factory()->user->create();
		$values    = array( 'first-value', 'second-value' );

		foreach ( $values as $value ) {
			add_metadata( $data_type, $user_id, $this->data_key, $value, false );
		}

		$this->assertSame( $values, $handler->get_value( $this->field, '', $user_id ) );
		$this->assertSame( $this->default, $handler->get_value( $this->field, 'unknown', $user_id ) );
	}
}
