<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;

final class CartService
{
    public function __construct(
        private CartRepository $cartRepo,
        private ProductRepository $productRepo
    ) {
    }

    public function getOrCreateCart(string $sessionId): array
    {
        return $this->cartRepo->getOrCreateBySessionId($sessionId);
    }

    public function getCartWithItems(string $sessionId): array
    {
        $cart = $this->cartRepo->getOrCreateBySessionId($sessionId);
        $items = $this->cartRepo->getItems((int) $cart['id']);
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int) $item['price_ore_snapshot'] * (int) $item['qty'];
        }
        return [
            'cart' => $cart,
            'items' => $items,
            'subtotal_ore' => $subtotal,
            'item_count' => $this->cartRepo->countItems((int) $cart['id']),
        ];
    }

    public function addToCart(string $sessionId, int $productId, int $qty = 1, ?int $variantId = null): array
    {
        $product = $this->productRepo->findBySlug($this->productRepo->findById($productId)['slug'] ?? '');
        if (!$product) {
            $product = $this->productRepo->findById($productId);
        }
        if (!$product) {
            return ['ok' => false, 'error' => 'Produkt ikke funnet'];
        }
        $priceOre = (int) ($product['price_from_ore'] ?? 0);
        $title = $product['title'] ?? 'Produkt';
        $cart = $this->cartRepo->getOrCreateBySessionId($sessionId);
        $this->cartRepo->addItem((int) $cart['id'], $productId, $variantId, max(1, $qty), $priceOre, $title);
        return ['ok' => true, 'cart' => $this->getCartWithItems($sessionId)];
    }

    public function addToCartBySlug(string $sessionId, string $productSlug, int $qty = 1): array
    {
        $product = $this->productRepo->findBySlug($productSlug);
        if (!$product) {
            return ['ok' => false, 'error' => 'Produkt ikke funnet'];
        }
        return $this->addToCart($sessionId, (int) $product['id'], $qty, null);
    }

    public function updateLineQty(string $sessionId, int $lineId, int $qty): array
    {
        $item = $this->cartRepo->findItemById($lineId);
        if (!$item) {
            return ['ok' => false, 'error' => 'Linje ikke funnet'];
        }
        $cart = $this->cartRepo->getOrCreateBySessionId($sessionId);
        if ((int) $item['cart_id'] !== (int) $cart['id']) {
            return ['ok' => false, 'error' => 'Ugyldig handlekurv'];
        }
        $this->cartRepo->updateItemQty($lineId, $qty);
        return ['ok' => true, 'cart' => $this->getCartWithItems($sessionId)];
    }

    public function removeLine(string $sessionId, int $lineId): array
    {
        $item = $this->cartRepo->findItemById($lineId);
        if (!$item) {
            return ['ok' => false, 'error' => 'Linje ikke funnet'];
        }
        $cart = $this->cartRepo->getOrCreateBySessionId($sessionId);
        if ((int) $item['cart_id'] !== (int) $cart['id']) {
            return ['ok' => false, 'error' => 'Ugyldig handlekurv'];
        }
        $this->cartRepo->removeItem($lineId);
        return ['ok' => true, 'cart' => $this->getCartWithItems($sessionId)];
    }
}
