<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class OrderRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function create(array $orderData, array $items): array
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO orders (public_id, user_id, email, status, payment_status, fulfillment_status, currency, subtotal_ore, shipping_ore, tax_ore, discount_ore, total_ore, shipping_address_json, billing_address_json, shipping_method_snapshot)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $orderData['public_id'],
                $orderData['user_id'] ?? null,
                $orderData['email'],
                $orderData['status'] ?? 'pending',
                $orderData['payment_status'] ?? 'unpaid',
                $orderData['fulfillment_status'] ?? 'unfulfilled',
                $orderData['currency'] ?? 'NOK',
                $orderData['subtotal_ore'],
                $orderData['shipping_ore'] ?? 0,
                $orderData['tax_ore'] ?? 0,
                $orderData['discount_ore'] ?? 0,
                $orderData['total_ore'],
                $orderData['shipping_address_json'] ?? null,
                $orderData['billing_address_json'] ?? null,
                $orderData['shipping_method_snapshot'] ?? null,
            ]);
            $orderId = (int) $this->pdo->lastInsertId();
            $itemStmt = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, variant_id, sku_snapshot, title_snapshot, unit_price_ore, qty, line_total_ore) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'] ?? null,
                    $item['sku_snapshot'] ?? null,
                    $item['title_snapshot'],
                    $item['unit_price_ore'],
                    $item['qty'],
                    $item['line_total_ore'],
                ]);
            }
            $this->pdo->commit();
            return array_merge($orderData, ['id' => $orderId]);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findByPublicId(string $publicId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE public_id = ?');
        $stmt->execute([$publicId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function listByUserId(int $userId, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT id, public_id, email, status, payment_status, total_ore, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOrderByUser(int $orderId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$orderId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findOrderByPublicIdAndUser(string $publicId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE public_id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$publicId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getItems(int $orderId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id');
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array> */
    public function listRecent(int $limit = 50): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->query('SELECT id, public_id, email, status, payment_status, total_ore, created_at FROM orders ORDER BY created_at DESC LIMIT ' . $limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPayment(int $orderId, string $provider, string $providerReferenceId, int $amountOre, string $status = 'created'): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO order_payments (order_id, provider, provider_payment_intent_id, status, amount_ore) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$orderId, $provider, $providerReferenceId, $status, $amountOre]);
    }

    public function findOrderIdByStripeSessionId(string $sessionId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT order_id FROM order_payments WHERE provider = ? AND provider_payment_intent_id = ? LIMIT 1');
        $stmt->execute(['stripe', $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['order_id'] : null;
    }

    public function updateOrderStatus(int $orderId, string $status, string $paymentStatus): void
    {
        $stmt = $this->pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?');
        $stmt->execute([$status, $paymentStatus, $orderId]);
    }

    public function updateOrderStatuses(int $orderId, ?string $status = null, ?string $paymentStatus = null, ?string $fulfillmentStatus = null): void
    {
        $set = [];
        $params = [];
        if ($status !== null) {
            $set[] = 'status = ?';
            $params[] = $status;
        }
        if ($paymentStatus !== null) {
            $set[] = 'payment_status = ?';
            $params[] = $paymentStatus;
        }
        if ($fulfillmentStatus !== null) {
            $set[] = 'fulfillment_status = ?';
            $params[] = $fulfillmentStatus;
        }
        if ($set === []) {
            return;
        }
        $params[] = $orderId;
        $sql = 'UPDATE orders SET ' . implode(', ', $set) . ' WHERE id = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function updatePaymentStatus(int $orderId, string $paymentStatus): void
    {
        $stmt = $this->pdo->prepare('UPDATE order_payments SET status = ? WHERE order_id = ?');
        $stmt->execute([$paymentStatus, $orderId]);
    }
}
