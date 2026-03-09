<?php

declare(strict_types=1);

namespace CasinoFinder;

/**
 * Handles plugin activation tasks.
 *
 * @since 1.0.0
 */
final class Activator
{
    /**
     * Run on plugin activation.
     *
     * Stores the current plugin version and flushes rewrite rules
     * so any custom post types or taxonomies are registered.
     *
     * @return void
     */
    public static function activate(): void
    {
        update_option('casino_finder_version', CASINO_FINDER_VERSION);
        flush_rewrite_rules();
    }
}
