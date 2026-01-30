<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\ShippingMethodRepository;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Support\CustomerAuth;

class CheckoutController
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private PaymentService $paymentService,
        private ShippingMethodRepository $shippingRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $sessionId = $this->sessionId();
        $data = $this->cartService->getCartWithItems($sessionId);
        if (empty($data['items'])) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $shippingMethods = $this->shippingRepo->listActive();
        $subtotalOre = (int) ($data['subtotal_ore'] ?? 0);
        $selectedId = null;
        $shippingOre = 0;
        if ($shippingMethods !== []) {
            $first = $shippingMethods[0];
            $selectedId = (int) $first['id'];
            $shippingOre = $this->shippingOreForMethod($first, $subtotalOre);
        }
        $data['shipping_methods'] = $shippingMethods;
        $data['selected_shipping_id'] = $selectedId;
        $data['shipping_ore'] = $shippingOre;
        $data['total_ore'] = $subtotalOre + $shippingOre;
        $content = $this->render('pages/checkout/index', $data);
        $html = $this->layout($content, [
            'title' => 'Kasse — Motorleaks',
            'meta_description' => 'Fullfør bestillingen.',
            'content' => $content,
        ]);
        return Response::html($html);
    }

    public function submit(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/kasse'), 302);
        }
        $sessionId = $this->sessionId();
        $data = $this->cartService->getCartWithItems($sessionId);
        if (empty($data['items'])) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $email = trim($request->input('email', '') ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = $this->addShippingToCheckoutData($data, (int) ($request->input('shipping_method_id', '0') ?: 0));
            $content = $this->render('pages/checkout/index', array_merge($data, ['error' => 'Ugyldig e-postadresse.']));
            $html = $this->layout($content, ['title' => 'Kasse — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $subtotalOre = (int) ($data['subtotal_ore'] ?? 0);
        $shippingMethodId = (int) ($request->input('shipping_method_id', '0') ?? 0);
        $shippingOre = 0;
        $shippingSnapshot = null;
        if ($shippingMethodId > 0) {
            $method = $this->shippingRepo->findById($shippingMethodId);
            if ($method !== null && !empty($method['is_active'])) {
                $shippingOre = $this->shippingOreForMethod($method, $subtotalOre);
                $shippingSnapshot = json_encode([
                    'code' => $method['code'],
                    'name' => $method['name'],
                    'price_ore' => (int) $method['price_ore'],
                ], JSON_THROW_ON_ERROR);
            }
        }
        $name = trim($request->input('name', '') ?? '');
        $address = [
            'name' => $name,
            'address_line1' => trim($request->input('address1', '') ?? ''),
            'postal_code' => trim($request->input('postal_code', '') ?? ''),
            'city' => trim($request->input('city', '') ?? ''),
            'country' => trim($request->input('country', '') ?? 'NO'),
        ];
        $customerId = CustomerAuth::userId();
        $order = $this->orderService->createOrderFromCart($sessionId, $email, $address, $address, $customerId, $shippingOre, $shippingSnapshot);
        if ($order === null) {
            return Response::redirect(url('/handlekurv'), 302);
        }
        $successUrl = url('/kasse/takk?order=' . ($order['public_id'] ?? ''));
        $cancelUrl = url('/kasse');
        $checkoutUrl = $this->paymentService->createCheckoutSession(
            (int) $order['id'],
            $order['public_id'] ?? '',
            (int) $order['total_ore'],
            $email,
            $successUrl,
            $cancelUrl
        );
        if ($checkoutUrl !== null) {
            return Response::redirect($checkoutUrl, 302);
        }
        return Response::redirect($successUrl, 302);
    }

    public function thankYou(Request $request, array $params): Response
    {
        $orderId = $request->query('order', '');
        $content = $this->render('pages/checkout/thank-you', ['order_id' => $orderId]);
        $html = $this->layout($content, [
            'title' => 'Takk for bestillingen — Motorleaks',
            'meta_description' => 'Bestillingen er mottatt.',
            'content' => $content,
        ]);
        return Response::html($html);
    }

    private function addShippingToCheckoutData(array $data, int $preferShippingId = 0): array
    {
        $shippingMethods = $this->shippingRepo->listActive();
        $subtotalOre = (int) ($data['subtotal_ore'] ?? 0);
        $selectedId = null;
        $shippingOre = 0;
        if ($shippingMethods !== []) {
            $selected = null;
            if ($preferShippingId > 0) {
                foreach ($shippingMethods as $m) {
                    if ((int) $m['id'] === $preferShippingId) {
                        $selected = $m;
                        break;
                    }
                }
            }
            if ($selected === null) {
                $selected = $shippingMethods[0];
            }
            $selectedId = (int) $selected['id'];
            $shippingOre = $this->shippingOreForMethod($selected, $subtotalOre);
        }
        $data['shipping_methods'] = $shippingMethods;
        $data['selected_shipping_id'] = $selectedId;
        $data['shipping_ore'] = $shippingOre;
        $data['total_ore'] = $subtotalOre + $shippingOre;
        return $data;
    }

    private function shippingOreForMethod(array $method, int $subtotalOre): int
    {
        $freeOver = isset($method['free_over_ore']) ? (int) $method['free_over_ore'] : null;
        if ($freeOver !== null && $freeOver > 0 && $subtotalOre >= $freeOver) {
            return 0;
        }
        return (int) ($method['price_ore'] ?? 0);
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
