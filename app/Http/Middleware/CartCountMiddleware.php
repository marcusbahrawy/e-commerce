<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Services\CartService;

class CartCountMiddleware
{
    public function __construct(private CartService $cartService)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        try {
            $data = $this->cartService->getCartWithItems(session_id());
            $GLOBALS['cart_count'] = $data['item_count'] ?? 0;
        } catch (\Throwable) {
            $GLOBALS['cart_count'] = 0;
        }
        return $next($request);
    }
}
