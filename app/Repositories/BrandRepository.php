<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class BrandRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, slug FROM brands ORDER BY name');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, slug FROM brands WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM brands WHERE slug = ? AND id != ? LIMIT 1');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM brands WHERE slug = ? LIMIT 1');
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO brands (name, slug) VALUES (?, ?)');
        $stmt->execute([
            $data['name'],
            $data['slug'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE brands SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM brands WHERE id = ?')->execute([$id]);
    }

    public function productCount(int $brandId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM products WHERE brand_id = ?');
        $stmt->execute([$brandId]);
        return (int) $stmt->fetchColumn();
    }
}
