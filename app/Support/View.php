<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    private static string $basePath;

    public static function setBasePath(string $path): void
    {
        self::$basePath = $path;
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layout'): string
    {
        $base = self::$basePath ?: dirname(__DIR__) . '/Templates';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        if ($layout === null) {
            return $content;
        }
        $layoutPath = $base . '/' . $layout . '.php';
        if (!is_file($layoutPath)) {
            return $content;
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $layoutPath;
        return (string) ob_get_clean();
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::render($view, $data, null);
    }
}
