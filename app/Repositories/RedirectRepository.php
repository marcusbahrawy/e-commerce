<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class RedirectRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, old_path, new_path, status_code, hits FROM redirects WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByOldPath(string $oldPath): ?array
    {
        $oldPath = $this->normalizePath($oldPath);
        $stmt = $this->pdo->prepare('SELECT id, old_path, new_path, status_code FROM redirects WHERE old_path = ? LIMIT 1');
        $stmt->execute([$oldPath]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function incrementHits(int $id): void
    {
        $this->pdo->prepare('UPDATE redirects SET hits = hits + 1 WHERE id = ?')->execute([$id]);
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO redirects (old_path, new_path, status_code) VALUES (?, ?, ?)');
        $stmt->execute([
            $this->normalizePath($data['old_path']),
            $data['new_path'],
            (int) ($data['status_code'] ?? 301),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array{id:int, old_path:string, new_path:string, status_code:int, hits:int}> */
    public function listAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, old_path, new_path, status_code, hits FROM redirects ORDER BY old_path');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM redirects WHERE id = ?')->execute([$id]);
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
