<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class ShippingMethodRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    /** @return array<int, array> */
    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM shipping_methods ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array> */
    public function listActive(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shipping_methods WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shipping_methods WHERE code = ? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM shipping_methods WHERE code = ? AND id != ? LIMIT 1');
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM shipping_methods WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
        }
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shipping_methods (code, name, price_ore, free_over_ore, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['code'],
            $data['name'],
            (int) ($data['price_ore'] ?? 0),
            isset($data['free_over_ore']) && $data['free_over_ore'] !== '' ? (int) $data['free_over_ore'] : null,
            !empty($data['is_active']) ? 1 : 0,
            (int) ($data['sort_order'] ?? 0),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shipping_methods SET code = ?, name = ?, price_ore = ?, free_over_ore = ?, is_active = ?, sort_order = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['code'],
            $data['name'],
            (int) ($data['price_ore'] ?? 0),
            isset($data['free_over_ore']) && $data['free_over_ore'] !== '' ? (int) $data['free_over_ore'] : null,
            !empty($data['is_active']) ? 1 : 0,
            (int) ($data['sort_order'] ?? 0),
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM shipping_methods WHERE id = ?');
        $stmt->execute([$id]);
    }
}
