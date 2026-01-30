<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CatalogService;

class CatalogController
{
    public function __construct(private CatalogService $catalog)
    {
    }

    public function category(Request $request, array $params): Response
    {
        $slug = $params['slug'] ?? '';
        $parentSlug = $params['parent'] ?? null;
        if ($slug === '') {
            return Response::html('<h1>404 Not Found</h1>', 404);
        }
        $category = $this->catalog->getCategoryBySlug($slug, $parentSlug);
        if ($category === null) {
            return Response::html('<h1>404 Kategori ikke funnet</h1>', 404);
        }
        $categoryId = (int) $category['id'];
        $page = max(1, (int) $request->query('page', '1'));
        $sort = $request->query('sort', 'relevance') ?: 'relevance';
        $result = $this->catalog->listProductsInCategory($categoryId, ['page' => $page, 'per_page' => 24, 'sort' => $sort]);
        $breadcrumbs = $this->catalog->getBreadcrumbsForCategory($categoryId);
        $subcategories = $this->catalog->getSubcategories($categoryId);
        $perPage = 24;
        $totalPages = (int) ceil($result['total'] / $perPage);

        $content = $this->render('pages/catalog/category', [
            'category' => $category,
            'breadcrumbs' => $breadcrumbs,
            'subcategories' => $subcategories,
            'products' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'sort' => $sort,
            'catalog' => $this->catalog,
        ]);
        $html = $this->layout($content, [
            'title' => $category['name'] . ' — Motorleaks',
            'meta_description' => mb_substr(strip_tags($category['description_html'] ?? $category['name'] ?? ''), 0, 160),
            'content' => $content,
        ]);
        return Response::html($html);
    }

    public function product(Request $request, array $params): Response
    {
        $slug = $params['slug'] ?? '';
        if ($slug === '') {
            return Response::html('<h1>404 Not Found</h1>', 404);
        }
        $product = $this->catalog->getProductBySlug($slug);
        if ($product === null) {
            return Response::html('<h1>404 Produkt ikke funnet</h1>', 404);
        }
        $productId = (int) $product['id'];
        $images = $this->catalog->getProductImages($productId);
        $primaryImagePath = $this->catalog->getProductPrimaryImagePath($productId, $product['primary_image_id'] ? (int) $product['primary_image_id'] : null);

        $content = $this->render('pages/catalog/product', [
            'product' => $product,
            'images' => $images,
            'primaryImagePath' => $primaryImagePath,
            'catalog' => $this->catalog,
        ]);
        $metaTitle = $product['meta_title'] ?? $product['title'];
        $metaDesc = $product['meta_description'] ?? $product['description_short'] ?? $product['title'];
        $html = $this->layout($content, [
            'title' => $metaTitle . ' — Motorleaks',
            'meta_description' => mb_substr(strip_tags($metaDesc), 0, 160),
            'content' => $content,
        ]);
        return Response::html($html);
    }

    public function search(Request $request, array $params): Response
    {
        $q = trim($request->query('q', '') ?? '');
        $page = max(1, (int) ($request->query('page', '1') ?? 1));
        $perPage = 24;
        $result = $this->catalog->searchProducts($q, $page, $perPage);
        $totalPages = $result['total'] > 0 ? (int) ceil($result['total'] / $perPage) : 0;
        $content = $this->render('pages/catalog/search', [
            'query' => $q,
            'products' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'totalPages' => $totalPages,
            'catalog' => $this->catalog,
        ]);
        $title = $q !== '' ? 'Søk: ' . $q . ' — Motorleaks' : 'Søk — Motorleaks';
        $html = $this->layout($content, [
            'title' => $title,
            'meta_description' => $q !== '' ? 'Søkeresultater for «' . $q . '».' : 'Søk i produkter.',
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
