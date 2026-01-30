<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\PageRepository;

class CmsController
{
    public function __construct(private PageRepository $pageRepo)
    {
    }

    public function page(Request $request, array $params): Response
    {
        $slug = $params['slug'] ?? '';
        if ($slug === '') {
            return Response::html('<h1>404 Not Found</h1>', 404);
        }
        $page = $this->pageRepo->findBySlug($slug);
        if ($page === null) {
            return Response::html('<h1>404 Side ikke funnet</h1>', 404);
        }
        $content = $this->render('pages/cms/page', ['page' => $page]);
        $html = $this->layout($content, [
            'title' => ($page['meta_title'] ?? $page['title']) . ' — Motorleaks',
            'meta_description' => $page['meta_description'] ?? mb_substr(strip_tags($page['content_html'] ?? ''), 0, 160),
            'content' => $content,
        ]);
        return Response::html($html);
    }

    private function layout(string $content, array $data = []): string
    {
        $data['content'] = $content;
        ob_start();
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Templates/layout.php';
        return (string) ob_get_clean();
    }

    private function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__) . '/Templates/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
