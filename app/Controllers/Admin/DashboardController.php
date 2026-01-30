<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;

class DashboardController
{
    public function __construct(private OrderRepository $orderRepo)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $html = $this->render('admin/dashboard', ['title' => 'Dashboard']);
        return Response::html($html);
    }

    private function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/Templates';
        $layoutPath = $base . '/admin/layout.php';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        if (is_file($layoutPath)) {
            $data['content'] = $content;
            $data['title'] = $data['title'] ?? 'Admin';
            extract($data, EXTR_SKIP);
            ob_start();
            require $layoutPath;
            return (string) ob_get_clean();
        }
        return $content;
    }
}
