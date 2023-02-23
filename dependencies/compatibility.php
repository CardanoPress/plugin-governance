<?php

/**
 * @package ThemePlate
 * @since   2.0.0
 */

spl_autoload_register(function (string $class) {
    if (0 === strpos($class, 'CardanoPress\Governance\Dependencies\ThemePlate\Core')) {
        $alias = str_replace('CardanoPress\Governance\Dependencies', 'CardanoPress\Dependencies', $class);

        if (! class_exists($class) && class_exists($alias)) {
            class_alias($alias, $class);
        }
    }
});
