<?php

/**
 * Setup meta boxes
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace ThemePlate\Meta;

use ThemePlate\Core\Config;
use ThemePlate\Core\Form;
use ThemePlate\Core\Handler;
use ThemePlate\Core\Helper\BoxHelper;

abstract class BaseMeta extends Form {

	protected int $current_id = 0;


	protected function get_handler(): Handler {

		return new MetaHandler( $this->config['object_type'] );

	}


	protected function fields_group_key(): string {

		return 'themeplate';

	}


	protected function get_nonce_data( int $object_id ): array {

		$form_id = $this->config['form_id'];
		$action  = 'save_' . $this->fields_group_key() . '_' . $form_id;
		$name    = $this->fields_group_key() . '_' . $form_id . '_' . $object_id;

		return compact( 'action', 'name' );

	}


	protected function maybe_nonce_fields( string $current_id ): void {

		$data = $this->get_nonce_data( $current_id );

		wp_nonce_field( $data['action'], $data['name'] );

	}


	protected function can_save( int $object_id ): bool {

		if ( null === $this->fields || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return false;
		}

		$data = $this->get_nonce_data( $object_id );

		return ! ( ! isset( $_POST[ $data['name'] ] ) || ! wp_verify_nonce( $_POST[ $data['name'] ], $data['action'] ) );

	}


	protected function save( int $object_id ): void {

		$config = $this->config;

		foreach ( $this->fields->get_collection() as $field ) {
			$key = $field->data_key( $this->config['data_prefix'] );

			if ( ! isset( $_POST[ $this->fields_group_key() ][ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				continue;
			}

			$stored  = get_metadata( $config['object_type'], $object_id, $key, ! $field->get_config( 'repeatable' ) );
			$updated = $_POST[ $this->fields_group_key() ][ $key ]; // phpcs:ignore WordPress.Security.NonceVerification

			if ( is_array( $updated ) ) {
				$updated = BoxHelper::prepare_save( $updated );
				$updated = array_filter( $updated );
			}

			if ( $field->get_config( 'repeatable' ) ) {
				delete_metadata( $config['object_type'], $object_id, $key );

				foreach ( $updated as $i => $value ) {
					if ( 'i-x' === $i ) {
						continue;
					}

					add_metadata( $config['object_type'], $object_id, $key, $value );
				}
			} else {
				if ( ( ! $stored && ! $updated ) || $stored === $updated ) {
					continue;
				}

				if ( $updated ) {
					update_metadata( $config['object_type'], $object_id, $key, $updated, $stored );
				} else {
					delete_metadata( $config['object_type'], $object_id, $key, $stored );
				}
			}
		}

	}


	public function get_config(): Config {

		return new Config( $this->config['data_prefix'], $this->fields );

	}

}
