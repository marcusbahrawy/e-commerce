<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\RedirectRepository;
use App\Support\Auth;

class RedirectsController
{
    public function __construct(
        private RedirectRepository $redirectRepo,
        private AuditLogRepository $auditLogRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $redirects = $this->redirectRepo->listAll();
        $html = $this->render('admin/redirects/index', ['title' => '301-omdirigeringer', 'redirects' => $redirects]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/redirects/form', ['title' => 'Ny omdirigering', 'redirect' => null]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/omdirigeringer', 302);
        }
        $oldPath = trim($request->input('old_path', '') ?? '');
        $newPath = trim($request->input('new_path', '') ?? '');
        $statusCode = (int) ($request->input('status_code', '301') ?? 301);
        if ($oldPath === '' || $newPath === '') {
            $html = $this->render('admin/redirects/form', ['title' => 'Ny omdirigering', 'redirect' => null, 'error' => 'Gammel og ny sti er påkrevd.']);
            return Response::html($html);
        }
        if ($statusCode < 301 || $statusCode > 308) {
            $statusCode = 301;
        }
        $oldPath = '/' . ltrim($oldPath, '/');
        if (!str_starts_with($newPath, 'http')) {
            $newPath = '/' . ltrim($newPath, '/');
        }
        $existing = $this->redirectRepo->findByOldPath($oldPath);
        if ($existing !== null) {
            $html = $this->render('admin/redirects/form', ['title' => 'Ny omdirigering', 'redirect' => null, 'error' => 'Denne gamle stien har allerede en omdirigering.']);
            return Response::html($html);
        }
        $id = $this->redirectRepo->create(['old_path' => $oldPath, 'new_path' => $newPath, 'status_code' => $statusCode]);
        $this->auditLogRepo->log(Auth::userId(), 'redirect.create', 'redirect', (string) $id, $oldPath . ' → ' . $newPath, $request->ip());
        return Response::redirect('/admin/omdirigeringer', 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/omdirigeringer', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $found = $this->redirectRepo->findById($id);
        if ($found !== null) {
            $this->auditLogRepo->log(Auth::userId(), 'redirect.delete', 'redirect', (string) $id, $found['old_path'] ?? null, $request->ip());
            $this->redirectRepo->delete($id);
        }
        return Response::redirect('/admin/omdirigeringer', 302);
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
