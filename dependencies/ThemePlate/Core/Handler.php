<?php

/**
 * @package ThemePlate
 */

namespace CardanoPress\Governance\Dependencies\ThemePlate\Core;

abstract class Handler {

	/**
	 * @return mixed
	 */
	abstract public function get_value( Field $field, string $data_prefix, string $current_id );

}
