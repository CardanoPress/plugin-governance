<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Core\Field\SelectField;
use ThemePlate\Meta\UserMeta;
use WP_UnitTestCase;

class UserMetaTest extends WP_UnitTestCase {
	private UserMeta $meta_box;

	public function setUp(): void {
		$this->meta_box = new UserMeta( 'Test' );
	}

	public function test_firing_create_actually_add_hooks(): void {
		$this->meta_box->create();

		$this->assertSame( 10, has_filter( 'show_user_profile', array( $this->meta_box, 'add_box' ) ) );
		$this->assertSame( 10, has_filter( 'edit_user_profile', array( $this->meta_box, 'add_box' ) ) );
		$this->assertSame( 10, has_filter( 'user_new_form', array( $this->meta_box, 'add_box' ) ) );
		$this->assertSame( 10, has_filter( 'personal_options_update', array( $this->meta_box, 'save_data' ) ) );
		$this->assertSame( 10, has_filter( 'edit_user_profile_update', array( $this->meta_box, 'save_data' ) ) );
		$this->assertSame( 10, has_filter( 'edit_user_created_user', array( $this->meta_box, 'save_data' ) ) );
		$this->assertSame( 10, has_filter( 'admin_footer', array( $this->meta_box, 'maybe_wanted_page' ) ) );
	}

	public function test_get_config(): void {
		$config = $this->meta_box->get_config();

		$this->assertSame( array(), $config->get_fields() );

		$this->meta_box->fields( array( 'test' => array( 'type' => 'select' ) ) );

		$config = $this->meta_box->get_config();
		$fields = $config->get_fields();

		$this->assertArrayHasKey( 'test', $fields );
		$this->assertInstanceOf( SelectField::class, $fields['test'] );
	}
}
