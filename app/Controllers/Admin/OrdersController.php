<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;

class OrdersController
{
    public function __construct(private OrderRepository $orderRepo)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $orders = $this->orderRepo->listRecent(50);
        $html = $this->render('admin/orders/index', ['title' => 'Ordrer', 'orders' => $orders]);
        return Response::html($html);
    }

    public function show(Request $request, array $params): Response
    {
        $publicId = $params['id'] ?? '';
        $order = $publicId !== '' ? $this->orderRepo->findByPublicId($publicId) : null;
        if ($order === null) {
            return Response::html('<h1>404 — Ordre ikke funnet</h1>', 404);
        }
        $items = $this->orderRepo->getItems((int) $order['id']);
        $shippingAddress = null;
        $billingAddress = null;
        if (!empty($order['shipping_address_json'])) {
            $shippingAddress = json_decode($order['shipping_address_json'], true) ?: [];
        }
        if (!empty($order['billing_address_json'])) {
            $billingAddress = json_decode($order['billing_address_json'], true) ?: [];
        }
        $html = $this->render('admin/orders/show', [
            'title' => 'Ordre ' . ($order['public_id'] ?? ''),
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress,
        ]);
        return Response::html($html);
    }

    public function updateStatus(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/admin/ordrer'), 302);
        }
        $publicId = $params['id'] ?? '';
        $order = $publicId !== '' ? $this->orderRepo->findByPublicId($publicId) : null;
        if ($order === null) {
            return Response::html('<h1>404 — Ordre ikke funnet</h1>', 404);
        }
        $status = trim($request->input('status', '') ?? '');
        $paymentStatus = trim($request->input('payment_status', '') ?? '');
        $fulfillmentStatus = trim($request->input('fulfillment_status', '') ?? '');
        if ($status !== '' || $paymentStatus !== '' || $fulfillmentStatus !== '') {
            $this->orderRepo->updateOrderStatuses(
                (int) $order['id'],
                $status !== '' ? $status : null,
                $paymentStatus !== '' ? $paymentStatus : null,
                $fulfillmentStatus !== '' ? $fulfillmentStatus : null
            );
        }
        return Response::redirect(url('/admin/ordrer/' . $publicId), 302);
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
