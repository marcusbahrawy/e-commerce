<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\ShippingMethodRepository;

class ShippingMethodsController
{
    public function __construct(private ShippingMethodRepository $shippingRepo)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $methods = $this->shippingRepo->listAll();
        $html = $this->render('admin/shipping/index', ['title' => 'Fraktmetoder', 'methods' => $methods]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/shipping/form', ['title' => 'Ny fraktmetode', 'method' => null]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/frakt'), 302);
        }
        $code = trim($request->input('code', '') ?? '');
        $name = trim($request->input('name', '') ?? '');
        if ($code === '' || $name === '') {
            $html = $this->render('admin/shipping/form', [
                'title' => 'Ny fraktmetode',
                'method' => null,
                'error' => 'Kode og navn er påkrevd.',
                'input' => $request->all(),
            ]);
            return Response::html($html);
        }
        if ($this->shippingRepo->codeExists($code)) {
            $html = $this->render('admin/shipping/form', [
                'title' => 'Ny fraktmetode',
                'method' => null,
                'error' => 'En fraktmetode med denne koden finnes allerede.',
                'input' => $request->all(),
            ]);
            return Response::html($html);
        }
        $this->shippingRepo->create([
            'code' => $code,
            'name' => $name,
            'price_ore' => (int) ($request->input('price_ore', '0') ?? 0),
            'free_over_ore' => $request->input('free_over_ore', '') !== '' ? (int) $request->input('free_over_ore') : null,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
            'sort_order' => (int) ($request->input('sort_order', '0') ?? 0),
        ]);
        return Response::redirect(url('/admin/frakt'), 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $method = $this->shippingRepo->findById($id);
        if ($method === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $html = $this->render('admin/shipping/form', ['title' => 'Rediger fraktmetode', 'method' => $method]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/frakt'), 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $method = $this->shippingRepo->findById($id);
        if ($method === null) {
            return Response::html('<h1>404</h1>', 404);
        }
        $code = trim($request->input('code', '') ?? '');
        $name = trim($request->input('name', '') ?? '');
        if ($code === '' || $name === '') {
            $html = $this->render('admin/shipping/form', [
                'title' => 'Rediger fraktmetode',
                'method' => array_merge($method, $request->all()),
                'error' => 'Kode og navn er påkrevd.',
            ]);
            return Response::html($html);
        }
        if ($this->shippingRepo->codeExists($code, $id)) {
            $html = $this->render('admin/shipping/form', [
                'title' => 'Rediger fraktmetode',
                'method' => array_merge($method, $request->all()),
                'error' => 'En annen fraktmetode bruker denne koden.',
            ]);
            return Response::html($html);
        }
        $this->shippingRepo->update($id, [
            'code' => $code,
            'name' => $name,
            'price_ore' => (int) ($request->input('price_ore', '0') ?? 0),
            'free_over_ore' => $request->input('free_over_ore', '') !== '' ? (int) $request->input('free_over_ore') : null,
            'is_active' => $request->input('is_active', '1') ? 1 : 0,
            'sort_order' => (int) ($request->input('sort_order', '0') ?? 0),
        ]);
        return Response::redirect(url('/admin/frakt'), 302);
    }

    public function delete(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/frakt'), 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $method = $this->shippingRepo->findById($id);
        if ($method !== null) {
            $this->shippingRepo->delete($id);
        }
        return Response::redirect(url('/admin/frakt'), 302);
    }

    private function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/Templates';
        $layoutPath = $base . '/admin/layout.php';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '<p>Under utvikling.</p>';
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
