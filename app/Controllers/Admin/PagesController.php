<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\PageRepository;
use App\Support\Slug;

class PagesController
{
    public function __construct(private PageRepository $pageRepo)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $pages = $this->pageRepo->listAllForAdmin();
        $html = $this->render('admin/pages/index', ['title' => 'CMS-sider', 'pages' => $pages]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/pages/form', ['title' => 'Ny side', 'page' => null]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/sider', 302);
        }
        $title = trim($request->input('title', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($title);
        if ($title === '') {
            $html = $this->render('admin/pages/form', ['title' => 'Ny side', 'page' => null, 'error' => 'Tittel er påkrevd.']);
            return Response::html($html);
        }
        if ($this->pageRepo->slugExists($slug)) {
            $slug = $slug . '-' . time();
        }
        $this->pageRepo->create([
            'slug' => $slug,
            'title' => $title,
            'meta_title' => trim($request->input('meta_title', '') ?? '') ?: null,
            'meta_description' => trim($request->input('meta_description', '') ?? '') ?: null,
            'content_html' => trim($request->input('content_html', '') ?? '') ?: null,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
        ]);
        return Response::redirect('/admin/sider', 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $page = $this->pageRepo->findByIdForAdmin($id);
        if ($page === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $html = $this->render('admin/pages/form', ['title' => 'Rediger side', 'page' => $page]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/sider', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $page = $this->pageRepo->findByIdForAdmin($id);
        if ($page === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $title = trim($request->input('title', '') ?? '');
        $slug = trim($request->input('slug', '') ?? '') ?: Slug::from($title);
        if ($title === '') {
            $html = $this->render('admin/pages/form', ['title' => 'Rediger side', 'page' => array_merge($page, ['title' => $request->input('title'), 'slug' => $slug]), 'error' => 'Tittel er påkrevd.']);
            return Response::html($html);
        }
        if ($this->pageRepo->slugExists($slug, $id)) {
            $slug = $page['slug'];
        }
        $this->pageRepo->update($id, [
            'slug' => $slug,
            'title' => $title,
            'meta_title' => trim($request->input('meta_title', '') ?? '') ?: null,
            'meta_description' => trim($request->input('meta_description', '') ?? '') ?: null,
            'content_html' => trim($request->input('content_html', '') ?? '') ?: null,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
        ]);
        return Response::redirect('/admin/sider', 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/sider', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $page = $this->pageRepo->findByIdForAdmin($id);
        if ($page !== null) {
            $this->pageRepo->softDelete($id);
        }
        return Response::redirect('/admin/sider', 302);
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
