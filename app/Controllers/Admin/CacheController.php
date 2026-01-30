<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Http\Middleware\PageCacheMiddleware;

class CacheController
{
    public function __construct(private string $storageDir)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $html = $this->render('admin/cache/index', ['title' => 'Cache']);
        return Response::html($html);
    }

    public function purge(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/cache', 302);
        }
        PageCacheMiddleware::purge($this->storageDir);
        return Response::redirect('/admin/cache?ok=1', 302);
    }

    private function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/Templates';
        $layoutPath = $base . '/admin/layout.php';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '<p>Side ikke funnet.</p>';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        $data['content'] = $content;
        $data['title'] = $data['title'] ?? 'Admin';
        extract($data, EXTR_SKIP);
        ob_start();
        require $layoutPath;
        return (string) ob_get_clean();
    }
}
