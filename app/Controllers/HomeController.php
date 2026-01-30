<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CatalogService;

class HomeController
{
    public function __construct(private ?CatalogService $catalog = null)
    {
    }

    public function __invoke(Request $request, array $params): Response
    {
        $categories = [];
        $featured = [];
        if ($this->catalog !== null) {
            try {
                $categories = $this->catalog->getRootCategories();
                $featured = $this->catalog->getFeaturedProducts(8);
            } catch (\Throwable) {
                // DB not configured or empty
            }
        }
        $content = $this->render('pages/home', [
            'categories' => $categories,
            'featured' => $featured,
            'catalog' => $this->catalog,
        ]);
        $html = $this->layout($content, [
            'title' => 'Motorleaks — Delebestilling på nett',
            'meta_description' => 'Delebestilling på nett. Scooter, moped, lett MC, ATV, hage, dekk og utstyr.',
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
        ob_start();
        extract($data, EXTR_SKIP);
        $path = dirname(__DIR__) . '/Templates/' . str_replace('.', '/', $view) . '.php';
        if (is_file($path)) {
            require $path;
        }
        return (string) ob_get_clean();
    }
}
