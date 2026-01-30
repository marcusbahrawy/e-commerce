<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class CategoryRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function findBySlug(string $slug, ?int $parentId = null): ?array
    {
        $sql = 'SELECT id, parent_id, slug, name, description_html, sort_order, is_active FROM categories WHERE slug = ? AND is_active = 1';
        $params = [$slug];
        if ($parentId !== null) {
            $sql .= ' AND parent_id = ?';
            $params[] = $parentId;
        } else {
            $sql .= ' AND parent_id IS NULL';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, parent_id, slug, name, description_html, sort_order, is_active FROM categories WHERE id = ? AND is_active = 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByIdForAdmin(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, parent_id, slug, name, description_html, sort_order, is_active FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function listAllForAdmin(): array
    {
        $stmt = $this->pdo->query('SELECT id, parent_id, slug, name, sort_order, is_active FROM categories ORDER BY parent_id IS NULL DESC, sort_order, name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories (parent_id, slug, name, description_html, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['parent_id'] ?? null,
            $data['slug'],
            $data['name'],
            $data['description_html'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET parent_id = ?, slug = ?, name = ?, description_html = ?, sort_order = ?, is_active = ? WHERE id = ?');
        $stmt->execute([
            $data['parent_id'] ?? null,
            $data['slug'],
            $data['name'],
            $data['description_html'] ?? null,
            $data['sort_order'] ?? 0,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }

    public function hasProducts(int $categoryId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM product_categories WHERE category_id = ? LIMIT 1');
        $stmt->execute([$categoryId]);
        return $stmt->fetchColumn() !== false;
    }

    /** @return array<int, array> */
    public function getRootCategories(): array
    {
        $stmt = $this->pdo->query('SELECT id, parent_id, slug, name, description_html, sort_order FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order, name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array> */
    public function getChildren(int $parentId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, parent_id, slug, name, description_html, sort_order FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order, name');
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array> */
    public function getBreadcrumbs(int $categoryId): array
    {
        $crumbs = [];
        $current = $this->findById($categoryId);
        while ($current) {
            array_unshift($crumbs, $current);
            $current = $current['parent_id'] ? $this->findById((int) $current['parent_id']) : null;
        }
        $path = [];
        foreach ($crumbs as $i => $c) {
            $path[] = $c['slug'];
            $crumbs[$i]['path'] = implode('/', $path);
        }
        return $crumbs;
    }

    public function slugExists(string $slug, ?int $parentId = null, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE slug = ? AND ';
        $sql .= $parentId === null ? 'parent_id IS NULL' : 'parent_id = ?';
        $params = $parentId === null ? [$slug] : [$slug, $parentId];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }
}
