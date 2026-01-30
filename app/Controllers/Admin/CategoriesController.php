<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\CategoryRepository;
use App\Http\Middleware\PageCacheMiddleware;
use App\Support\Auth;
use App\Support\Slug;

class CategoriesController
{
    public function __construct(
        private CategoryRepository $categoryRepo,
        private AuditLogRepository $auditLogRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $categories = $this->categoryRepo->listAllForAdmin();
        $html = $this->render('admin/categories/index', ['title' => 'Kategorier', 'categories' => $categories]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $parents = $this->categoryRepo->listAllForAdmin();
        $html = $this->render('admin/categories/form', ['title' => 'Ny kategori', 'category' => null, 'parents' => $parents]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/kategorier', 302);
        }
        $name = trim($request->input('name', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($name);
        $parentId = $request->input('parent_id', '');
        $parentId = $parentId !== '' ? (int) $parentId : null;
        if ($name === '') {
            $parents = $this->categoryRepo->listAllForAdmin();
            $html = $this->render('admin/categories/form', ['title' => 'Ny kategori', 'category' => null, 'parents' => $parents, 'error' => 'Navn er påkrevd.']);
            return Response::html($html);
        }
        if ($this->categoryRepo->slugExists($slug, $parentId)) {
            $slug = $slug . '-' . time();
        }
        $id = $this->categoryRepo->create([
            'parent_id' => $parentId,
            'slug' => $slug,
            'name' => $name,
            'description_html' => trim($request->input('description_html', '') ?? '') ?: null,
            'sort_order' => (int) ($request->input('sort_order', '0') ?? 0),
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
        ]);
        $this->auditLogRepo->log(Auth::userId(), 'category.create', 'category', (string) $id, $name, $request->ip());
        PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        return Response::redirect('/admin/kategorier', 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $category = $this->categoryRepo->findByIdForAdmin($id);
        if ($category === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $parents = $this->categoryRepo->listAllForAdmin();
        $html = $this->render('admin/categories/form', ['title' => 'Rediger kategori', 'category' => $category, 'parents' => $parents]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/kategorier', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $category = $this->categoryRepo->findByIdForAdmin($id);
        if ($category === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $name = trim($request->input('name', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($name);
        $parentId = $request->input('parent_id', '');
        $parentId = $parentId !== '' ? (int) $parentId : null;
        if ($parentId === $id) {
            $parentId = $category['parent_id'];
        }
        if ($name === '') {
            $parents = $this->categoryRepo->listAllForAdmin();
            $html = $this->render('admin/categories/form', ['title' => 'Rediger kategori', 'category' => array_merge($category, ['name' => $request->input('name'), 'slug' => $slug]), 'parents' => $parents, 'error' => 'Navn er påkrevd.']);
            return Response::html($html);
        }
        if ($this->categoryRepo->slugExists($slug, $parentId, $id)) {
            $slug = $category['slug'];
        }
        $this->categoryRepo->update($id, [
            'parent_id' => $parentId,
            'slug' => $slug,
            'name' => $name,
            'description_html' => trim($request->input('description_html', '') ?? '') ?: null,
            'sort_order' => (int) ($request->input('sort_order', '0') ?? 0),
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
        ]);
        $this->auditLogRepo->log(Auth::userId(), 'category.update', 'category', (string) $id, $name, $request->ip());
        PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        return Response::redirect('/admin/kategorier', 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/kategorier', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $category = $this->categoryRepo->findByIdForAdmin($id);
        if ($category !== null && !$this->categoryRepo->hasProducts($id)) {
            $this->auditLogRepo->log(Auth::userId(), 'category.delete', 'category', (string) $id, $category['name'] ?? null, $request->ip());
            $this->categoryRepo->delete($id);
            PageCacheMiddleware::purge(dirname(__DIR__, 3) . '/storage');
        }
        return Response::redirect('/admin/kategorier', 302);
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
