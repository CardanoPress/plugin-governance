<?php

/**
 * Setup a field type
 *
 * @package ThemePlate
 * @since 0.1.0
 */

namespace CardanoPress\Governance\Dependencies\ThemePlate\Core\Field;

use CardanoPress\Governance\Dependencies\ThemePlate\Core\Field;

class HtmlField extends Field {

	public function render( $value ): void {

		echo $this->get_config( 'default' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}

}
