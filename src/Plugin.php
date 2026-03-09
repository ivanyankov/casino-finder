<?php

declare(strict_types=1);

namespace CasinoFinder;

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
    }
}
