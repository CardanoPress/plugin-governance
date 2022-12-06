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
        add_action('wp_enqueue_scripts', [$this, 'autoEnqueues'], 25);
    }

    public function autoEnqueues(): void
    {
        wp_register_style(
            self::HANDLE_PREFIX . 'bootstrap',
            plugin_dir_url($this->path) . 'vendor/bootstrap.min.css',
            [],
            '5.1.3'
        );
        wp_register_script(
            self::HANDLE_PREFIX . 'bootstrap',
            plugin_dir_url($this->path) . 'vendor/bootstrap.bundle.min.js',
            ['jquery'],
            '5.1.3',
            true,
        );

        if (is_singular('proposal') || is_post_type_archive('proposal')) {
            if (! wp_style_is('cardanopress_bootstrap-style')) {
                wp_enqueue_style(self::HANDLE_PREFIX . 'bootstrap');
            }

            if (! wp_script_is('cardanopress_bootstrap-script')) {
                wp_enqueue_script(self::HANDLE_PREFIX . 'bootstrap');
            }

            wp_enqueue_script(self::HANDLE_PREFIX . 'script');
        }
    }
}
