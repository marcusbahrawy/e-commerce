<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class PageRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, title, meta_title, meta_description, content_html, is_active FROM pages WHERE slug = ? AND is_active = 1 AND deleted_at IS NULL'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function listAllForAdmin(): array
    {
        $stmt = $this->pdo->query('SELECT id, slug, title, is_active, created_at, updated_at FROM pages WHERE deleted_at IS NULL ORDER BY slug');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForAdmin(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pages WHERE id = ? AND deleted_at IS NULL');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO pages (slug, title, meta_title, meta_description, content_html, is_active) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['slug'],
            $data['title'],
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
            $data['content_html'] ?? null,
            $data['is_active'] ?? 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE pages SET slug = ?, title = ?, meta_title = ?, meta_description = ?, content_html = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['slug'],
            $data['title'],
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null,
            $data['content_html'] ?? null,
            $data['is_active'] ?? 1,
            $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $this->pdo->prepare('UPDATE pages SET deleted_at = CURRENT_TIMESTAMP(6) WHERE id = ?')->execute([$id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM pages WHERE slug = ? AND deleted_at IS NULL';
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }
}
