<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class MenuRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function getByKey(string $key): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, `key`, name FROM menus WHERE `key` = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function getItems(int $menuId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, menu_id, parent_id, type, ref_id, url, label, sort_order, is_active FROM menu_items WHERE menu_id = ? ORDER BY parent_id IS NULL DESC, sort_order, id');
        $stmt->execute([$menuId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array> */
    public function listMenus(): array
    {
        $stmt = $this->pdo->query('SELECT id, `key`, name FROM menus ORDER BY `key`');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrCreateMenu(string $key, string $name): int
    {
        $menu = $this->getByKey($key);
        if ($menu) {
            return (int) $menu['id'];
        }
        $this->pdo->prepare('INSERT INTO menus (`key`, name) VALUES (?, ?)')->execute([$key, $name]);
        return (int) $this->pdo->lastInsertId();
    }

    public function setItems(int $menuId, array $items): void
    {
        $this->pdo->prepare('DELETE FROM menu_items WHERE menu_id = ?')->execute([$menuId]);
        $stmt = $this->pdo->prepare('INSERT INTO menu_items (menu_id, parent_id, type, ref_id, url, label, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($items as $i => $item) {
            $stmt->execute([
                $menuId,
                $item['parent_id'] ?? null,
                $item['type'] ?? 'url',
                $item['ref_id'] ?? null,
                $item['url'] ?? null,
                $item['label'] ?? '',
                $item['sort_order'] ?? $i,
                $item['is_active'] ?? 1,
            ]);
        }
    }
}
