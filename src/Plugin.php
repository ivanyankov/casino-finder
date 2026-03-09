<?php

declare(strict_types=1);

namespace CasinoFinder;

use CasinoFinder\PostTypes\PostType;
use CasinoFinder\MetaBoxes\CasinoMetaBoxes;

/**
 * Main plugin bootstrap class.
 *
 * Uses the singleton pattern to ensure a single instance
 * throughout the WordPress lifecycle.
 *
 * @since 1.0.0
 */
final class Plugin
{
    /** @var self|null Singleton instance. */
    private static ?self $instance = null;

    /** @var bool Whether the plugin has already been booted. */
    private bool $booted = false;

    /**
     * Prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Get the singleton instance of the plugin.
     *
     * @return self
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Boot the plugin by registering all hooks, shortcodes and services.
     *
     * Safe to call multiple times — subsequent calls are no-ops.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $this->registerPostTypes();
        $this->registerMetaBoxes();
    }

    /**
     * Register all meta boxes for custom post types.
     *
     * @return void
     */
    private function registerMetaBoxes(): void
    {
        (new CasinoMetaBoxes())->register();
    }

    /**
     * Register all custom post types.
     *
     * @return void
     */
    private function registerPostTypes(): void
    {
        (new PostType('casino', __('Casino', 'casino-finder'), __('Casinos', 'casino-finder'), [
            'menu_icon'    => 'dashicons-money-alt',
            'supports'     => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'has_archive'  => false,
            'show_in_rest' => true,
        ]))->register();
    }
}
