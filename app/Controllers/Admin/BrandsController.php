<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\BrandRepository;
use App\Support\Auth;
use App\Support\Slug;

class BrandsController
{
    public function __construct(
        private BrandRepository $brandRepo,
        private AuditLogRepository $auditLogRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $brands = $this->brandRepo->listAll();
        $counts = [];
        foreach ($brands as $b) {
            $counts[(int) $b['id']] = $this->brandRepo->productCount((int) $b['id']);
        }
        $html = $this->render('admin/brands/index', ['title' => 'Merker', 'brands' => $brands, 'productCounts' => $counts]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/brands/form', ['title' => 'Nytt merke', 'brand' => null]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/merker', 302);
        }
        $name = trim($request->input('name', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($name);
        if ($name === '') {
            $html = $this->render('admin/brands/form', ['title' => 'Nytt merke', 'brand' => null, 'error' => 'Navn er påkrevd.']);
            return Response::html($html);
        }
        if ($this->brandRepo->slugExists($slug)) {
            $slug = $slug . '-' . time();
        }
        $id = $this->brandRepo->create(['name' => $name, 'slug' => $slug]);
        $this->auditLogRepo->log(Auth::userId(), 'brand.create', 'brand', (string) $id, $name, $request->ip());
        return Response::redirect('/admin/merker', 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $brand = $this->brandRepo->findById($id);
        if ($brand === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $html = $this->render('admin/brands/form', ['title' => 'Rediger merke', 'brand' => $brand]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/merker', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $brand = $this->brandRepo->findById($id);
        if ($brand === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $name = trim($request->input('name', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($name);
        if ($name === '') {
            $html = $this->render('admin/brands/form', ['title' => 'Rediger merke', 'brand' => array_merge($brand, ['name' => $request->input('name'), 'slug' => $slug]), 'error' => 'Navn er påkrevd.']);
            return Response::html($html);
        }
        if ($this->brandRepo->slugExists($slug, $id)) {
            $slug = $brand['slug'];
        }
        $this->brandRepo->update($id, ['name' => $name, 'slug' => $slug]);
        $this->auditLogRepo->log(Auth::userId(), 'brand.update', 'brand', (string) $id, $name, $request->ip());
        return Response::redirect('/admin/merker', 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/merker', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $brand = $this->brandRepo->findById($id);
        if ($brand !== null && $this->brandRepo->productCount($id) === 0) {
            $this->auditLogRepo->log(Auth::userId(), 'brand.delete', 'brand', (string) $id, $brand['name'] ?? null, $request->ip());
            $this->brandRepo->delete($id);
        }
        return Response::redirect('/admin/merker', 302);
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
