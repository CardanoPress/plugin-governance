<?php

/**
 * @package ThemePlate
 * @since   0.1.0
 */

namespace PBWebDev\CardanoPress\Governance;

use PBWebDev\CardanoPress\Foundation\AbstractManifest;

class Manifest extends AbstractManifest
{
    public function __construct(string $load_path, string $version = '0.1.0')
    {
        parent::__construct($load_path, $version);

        add_action('wp_enqueue_scripts', [$this, 'autoEnqueues']);
    }

    protected function getAssetsBase(): string
    {
        return plugin_dir_url(CP_GOVERNANCE_FILE) . 'assets/dist/';
    }

    protected function getAssetPrefix(): string
    {
        return 'cp-governance-';
    }

    public function autoEnqueues(): void
    {
        wp_script_add_data($this->getAssetPrefix() . 'script', 'defer', true);
        wp_register_style(
            $this->getAssetPrefix() . 'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
            [],
            '5.1.3'
        );
        wp_style_add_data(
            $this->getAssetPrefix() . 'bootstrap',
            'integrity',
            'sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3'
        );
        wp_style_add_data($this->getAssetPrefix() . 'bootstrap', 'crossorigin', 'anonymous');
        wp_register_script(
            $this->getAssetPrefix() . 'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            ['jquery'],
            '5.1.3',
            true,
        );
        wp_script_add_data(
            $this->getAssetPrefix() . 'bootstrap',
            'integrity',
            'sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p'
        );
        wp_script_add_data($this->getAssetPrefix() . 'bootstrap', 'crossorigin', 'anonymous');
        wp_script_add_data($this->getAssetPrefix() . 'recaptcha', 'defer', true);

        if (is_singular('proposal') || is_post_type_archive('proposal')) {
            wp_enqueue_style($this->getAssetPrefix() . 'bootstrap');
            wp_enqueue_script($this->getAssetPrefix() . 'bootstrap');
            wp_enqueue_script($this->getAssetPrefix() . 'script');
        }
    }
}
