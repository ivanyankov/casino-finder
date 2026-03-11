<?php

declare(strict_types=1);

namespace CasinoFinder\Support;

final class TemplateRenderer
{
    /**
     * Render a template from the plugin resources/templates directory.
     *
     * @param string $template Relative template filename, e.g. 'wizard.php'.
     * @param array<string,mixed> $context Variables to extract into the template scope.
     *
     * @return string
     */
    public static function render(string $template, array $context = []): string
    {
        $path = CASINO_FINDER_PLUGIN_DIR . 'resources/templates/' . ltrim($template, '/');

        if (! is_readable($path)) {
            return '';
        }

        if ($context !== []) {
            extract($context, EXTR_SKIP);
        }

        ob_start();

        include $path;

        return (string) ob_get_clean();
    }
}

