<?php

declare(strict_types=1);

namespace CasinoFinder;

/**
 * Handles plugin deactivation tasks.
 *
 * @since 1.0.0
 */
final class Deactivator
{
    /**
     * Run on plugin deactivation.
     *
     * Flushes rewrite rules to remove any custom routes
     * registered by the plugin.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
