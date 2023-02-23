<?php

/**
 * @package ThemePlate
 */

namespace CardanoPress\Governance\Dependencies\ThemePlate\Meta\Traits;

trait HasLocation {

	protected array $locations = array();


	public function location( string $location ): self {

		$this->locations[] = $location;

		return $this;

	}

}
