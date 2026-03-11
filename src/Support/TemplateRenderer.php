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
        $basePath = CASINO_FINDER_PLUGIN_DIR . 'resources/templates/';
        $template = ltrim($template, '/');

        if (pathinfo($template, PATHINFO_EXTENSION) !== 'php') {
            return '';
        }

        $base = realpath($basePath);
        $path = realpath($basePath . $template);

        if ($base === false || $path === false || strpos($path, $base) !== 0 || ! is_readable($path)) {
            return '';
        }

        extract($context, EXTR_SKIP);

        ob_start();
        include $path;

        return (string) ob_get_clean();
    }
}

