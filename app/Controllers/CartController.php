<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\CartService;
use App\Support\Money;

class CartController
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $sessionId = $this->sessionId();
        $data = $this->cartService->getCartWithItems($sessionId);
        $content = $this->render('pages/cart/index', $data);
        $html = $this->layout($content, [
            'title' => 'Handlekurv — Motorleaks',
            'meta_description' => 'Din handlekurv.',
            'content' => $content,
        ]);
        return Response::html($html);
    }

    public function add(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $productId = (int) $request->input('product_id', '0');
        $qty = max(1, (int) $request->input('qty', '1'));
        $slug = $request->input('product_slug', '');
        $sessionId = $this->sessionId();
        if ($slug !== '') {
            $result = $this->cartService->addToCartBySlug($sessionId, $slug, $qty);
        } elseif ($productId > 0) {
            $result = $this->cartService->addToCart($sessionId, $productId, $qty, null);
        } else {
            return Response::redirect(url('/handlekurv'), 302);
        }
        if (!($result['ok'] ?? false)) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $redirect = $request->input('redirect', url('/handlekurv'));
        return Response::redirect($redirect, 302);
    }

    public function remove(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $lineId = (int) $request->input('line_id', '0');
        $sessionId = $this->sessionId();
        $this->cartService->removeLine($sessionId, $lineId);
        return Response::redirect(url('/handlekurv'), 302);
    }

    private function sessionId(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return session_id();
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
