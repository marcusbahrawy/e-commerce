<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;

final class OrderService
{
    public function __construct(
        private OrderRepository $orderRepo,
        private CartRepository $cartRepo
    ) {
    }

    public function createOrderFromCart(string $sessionId, string $email, array $shippingAddress = [], array $billingAddress = [], ?int $userId = null, int $shippingOre = 0, ?string $shippingMethodSnapshot = null): ?array
    {
        $cart = $this->cartRepo->getOrCreateBySessionId($sessionId);
        $items = $this->cartRepo->getItems((int) $cart['id']);
        if (empty($items)) {
            return null;
        }
        $subtotal = 0;
        $orderItems = [];
        foreach ($items as $item) {
            $qty = (int) $item['qty'];
            $unitPrice = (int) $item['price_ore_snapshot'];
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;
            $orderItems[] = [
                'product_id' => (int) $item['product_id'],
                'variant_id' => !empty($item['variant_id']) ? (int) $item['variant_id'] : null,
                'sku_snapshot' => null,
                'title_snapshot' => $item['title_snapshot'],
                'unit_price_ore' => $unitPrice,
                'qty' => $qty,
                'line_total_ore' => $lineTotal,
            ];
        }
        $totalOre = $subtotal + $shippingOre;
        $publicId = strtoupper(bin2hex(random_bytes(13)));
        $orderData = [
            'public_id' => $publicId,
            'user_id' => $userId ?? $cart['user_id'],
            'email' => $email,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency' => $cart['currency'] ?? 'NOK',
            'subtotal_ore' => $subtotal,
            'shipping_ore' => $shippingOre,
            'tax_ore' => 0,
            'discount_ore' => 0,
            'total_ore' => $totalOre,
            'shipping_address_json' => $shippingAddress ? json_encode($shippingAddress, JSON_THROW_ON_ERROR) : null,
            'billing_address_json' => $billingAddress ? json_encode($billingAddress, JSON_THROW_ON_ERROR) : null,
            'shipping_method_snapshot' => $shippingMethodSnapshot,
        ];
        $order = $this->orderRepo->create($orderData, $orderItems);
        $this->cartRepo->clearCart((int) $cart['id']);
        return array_merge($order, ['items' => $orderItems]);
    }
}
