<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class DashboardController
{
    public function __construct(
        private OrderRepository $orderRepo,
        private ?ProductRepository $productRepo = null
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $stats = $this->getStats();
        $html = $this->render('admin/dashboard', ['title' => 'Dashboard', 'stats' => $stats]);
        return Response::html($html);
    }

    private function getStats(): array
    {
        $stats = [];
        try {
            $orders = $this->orderRepo->listRecent(500);
            $stats['orders_pending'] = count(array_filter($orders, fn ($o) => ($o['status'] ?? '') === 'pending'));
            $today = date('Y-m-d');
            $stats['orders_today'] = count(array_filter($orders, fn ($o) => substr($o['created_at'] ?? '', 0, 10) === $today));
        } catch (\Throwable $e) {
            // ignore
        }
        if ($this->productRepo !== null) {
            try {
                $stats['products_count'] = $this->productRepo->countForAdmin();
            } catch (\Throwable $e) {
                // ignore
            }
        }
        return $stats;
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
