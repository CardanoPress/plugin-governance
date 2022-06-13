<?php

/**
 * Setup post meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Meta;

use ThemePlate\Core\Helper\BoxHelper;
use ThemePlate\Core\Helper\FormHelper;
use ThemePlate\Core\Helper\MetaHelper;
use WP_User;

class UserMeta extends BaseMeta {

	protected function initialize( array &$config ): void {

		$config['object_type'] = 'user';

	}


	public function create(): void {

		$priority = BoxHelper::get_priority( $this->config );

		add_action( 'show_user_profile', array( $this, 'add_box' ), $priority );
		add_action( 'edit_user_profile', array( $this, 'add_box' ), $priority );
		add_action( 'user_new_form', array( $this, 'add_box' ), $priority );
		add_action( 'personal_options_update', array( $this, 'save_data' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_data' ) );
		add_action( 'edit_user_created_user', array( $this, 'save_data' ) );
		add_action( 'admin_footer', array( $this, 'maybe_wanted_page' ) );

	}


	public function add_box( $user ) {

		$this->current_id = $user instanceof WP_User ? $user->ID : 0;

		$this->layout_postbox( $this->current_id );

	}


	public function save_data( int $object_id ): void {

		$this->current_id = $object_id;

		if ( 'edit_user_created_user' === current_action() ) {
			$object_id = 0;
		}

		if ( ! $this->can_save( $object_id ) ) {
			return;
		}

		$object_id = $this->current_id;

		if ( ! current_user_can( 'edit_user', $object_id ) ) {
			return;
		}

		$this->save( $object_id );

	}


	public function maybe_wanted_page( string $hook_suffix ): void {

		$screen = get_current_screen();

		if ( null === $screen || ! in_array( $screen->base, array( 'user', 'user-edit', 'profile' ), true ) ) {
			return;
		}

		if ( ! MetaHelper::should_display( $this->config, $this->current_id ) ) {
			return;
		}

		FormHelper::enqueue_assets( $hook_suffix );

	}

}
