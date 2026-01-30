<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class CartRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function getOrCreateBySessionId(string $sessionId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, session_id, user_id, currency FROM carts WHERE session_id = ? LIMIT 1');
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
        $this->pdo->prepare('INSERT INTO carts (session_id, currency) VALUES (?, ?)')->execute([$sessionId, 'NOK']);
        $id = (int) $this->pdo->lastInsertId();
        return ['id' => $id, 'session_id' => $sessionId, 'user_id' => null, 'currency' => 'NOK'];
    }

    public function getItems(int $cartId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ci.id, ci.cart_id, ci.product_id, ci.variant_id, ci.qty, ci.price_ore_snapshot, ci.title_snapshot, p.slug AS product_slug
             FROM cart_items ci
             INNER JOIN products p ON p.id = ci.product_id AND p.deleted_at IS NULL
             WHERE ci.cart_id = ? ORDER BY ci.id'
        );
        $stmt->execute([$cartId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItem(int $cartId, int $productId, ?int $variantId, int $qty, int $priceOre, string $titleSnapshot): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO cart_items (cart_id, product_id, variant_id, qty, price_ore_snapshot, title_snapshot) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$cartId, $productId, $variantId, $qty, $priceOre, $titleSnapshot]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateItemQty(int $cartItemId, int $qty): bool
    {
        if ($qty < 1) {
            return $this->removeItem($cartItemId);
        }
        $stmt = $this->pdo->prepare('UPDATE cart_items SET qty = ?, updated_at = CURRENT_TIMESTAMP(6) WHERE id = ?');
        $stmt->execute([$qty, $cartItemId]);
        return $stmt->rowCount() > 0;
    }

    public function removeItem(int $cartItemId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM cart_items WHERE id = ?');
        $stmt->execute([$cartItemId]);
        return $stmt->rowCount() > 0;
    }

    public function findItemById(int $cartItemId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, cart_id, product_id, qty, price_ore_snapshot, title_snapshot FROM cart_items WHERE id = ?');
        $stmt->execute([$cartItemId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function countItems(int $cartId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(qty), 0) FROM cart_items WHERE cart_id = ?');
        $stmt->execute([$cartId]);
        return (int) $stmt->fetchColumn();
    }

    public function getCartById(int $cartId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, session_id, user_id, currency FROM carts WHERE id = ?');
        $stmt->execute([$cartId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function clearCart(int $cartId): void
    {
        $this->pdo->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
    }
}
