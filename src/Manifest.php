<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use CardanoPress\Foundation\AbstractManifest;

class Manifest extends AbstractManifest
{
    public const HANDLE_PREFIX = 'cp-governance-';

    protected function initialize(): void
    {
    }

    public function setupHooks(): void
    {
        parent::setupHooks();
        add_action('wp_enqueue_scripts', [$this, 'autoEnqueues']);
    }

    public function autoEnqueues(): void
    {
        wp_register_style(
            self::HANDLE_PREFIX . 'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
            [],
            '5.1.3'
        );
        wp_style_add_data(
            self::HANDLE_PREFIX . 'bootstrap',
            'integrity',
            'sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3'
        );
        wp_style_add_data(self::HANDLE_PREFIX . 'bootstrap', 'crossorigin', 'anonymous');
        wp_register_script(
            self::HANDLE_PREFIX . 'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            '5.1.3',
            true,
        );
        wp_script_add_data(
            self::HANDLE_PREFIX . 'bootstrap',
            'integrity',
            'sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p'
        );
        wp_script_add_data(self::HANDLE_PREFIX . 'bootstrap', 'crossorigin', 'anonymous');

        if (is_singular('proposal') || is_post_type_archive('proposal')) {
            if (apply_filters(self::HANDLE_PREFIX . 'enqueue-bootstrap-style', true)) {
                wp_enqueue_style(self::HANDLE_PREFIX . 'bootstrap');
            }

            if (apply_filters(self::HANDLE_PREFIX . 'enqueue-bootstrap-script', true)) {
                wp_enqueue_script(self::HANDLE_PREFIX . 'bootstrap');
            }

            wp_enqueue_script(self::HANDLE_PREFIX . 'script');
        }
    }
}
