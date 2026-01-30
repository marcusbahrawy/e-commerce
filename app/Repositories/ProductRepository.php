<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class ProductRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.sku, p.slug, p.title, p.subtitle, p.brand_id, p.description_short, p.description_html,
                    p.price_from_ore, p.price_to_ore, p.primary_category_id, p.primary_image_id,
                    b.name AS brand_name, b.slug AS brand_slug
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.slug = ? AND p.is_active = 1 AND p.deleted_at IS NULL'
        );
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.sku, p.slug, p.title, p.subtitle, p.brand_id, p.description_short, p.description_html,
                    p.price_from_ore, p.price_to_ore, p.primary_category_id, p.primary_image_id,
                    b.name AS brand_name, b.slug AS brand_slug
             FROM products p
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.id = ? AND p.is_active = 1 AND p.deleted_at IS NULL'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return array<int, array> */
    public function getImages(int $productId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, path_original, path_webp, alt_text, sort_order FROM product_images WHERE product_id = ? ORDER BY sort_order, id');
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array{sort?: string, page?: int, per_page?: int} $options
     * @return array{items: array, total: int}
     */
    public function listByCategory(int $categoryId, array $options = []): array
    {
        $sort = $options['sort'] ?? 'relevance';
        $page = max(1, (int) ($options['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($options['per_page'] ?? 24)));
        $offset = ($page - 1) * $perPage;

        $orderBy = 'p.id';
        if ($sort === 'price_asc') {
            $orderBy = 'p.price_from_ore ASC, p.id';
        } elseif ($sort === 'price_desc') {
            $orderBy = 'p.price_from_ore DESC, p.id';
        } elseif ($sort === 'newest') {
            $orderBy = 'p.created_at DESC, p.id';
        }

        $countSql = 'SELECT COUNT(DISTINCT p.id) FROM products p INNER JOIN product_categories pc ON pc.product_id = p.id WHERE pc.category_id = ? AND p.is_active = 1 AND p.deleted_at IS NULL';
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute([$categoryId]);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.id, p.slug, p.title, p.price_from_ore, p.price_to_ore, p.primary_image_id
                FROM products p
                INNER JOIN product_categories pc ON pc.product_id = p.id
                WHERE pc.category_id = ? AND p.is_active = 1 AND p.deleted_at IS NULL
                ORDER BY $orderBy
                LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['items' => $items, 'total' => $total];
    }

    /** @return array<int, array> */
    public function getFeatured(int $limit = 12): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, title, price_from_ore, price_to_ore, primary_image_id FROM products WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL ORDER BY id LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Get primary image path for a product (from product_images by primary_image_id or first image). */
    public function getPrimaryImagePath(int $productId, ?int $primaryImageId = null): ?string
    {
        if ($primaryImageId) {
            $stmt = $this->pdo->prepare('SELECT path_webp, path_original FROM product_images WHERE id = ? AND product_id = ?');
            $stmt->execute([$primaryImageId, $productId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT path_webp, path_original FROM product_images WHERE product_id = ? ORDER BY sort_order, id LIMIT 1');
            $stmt->execute([$productId]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $row['path_webp'] ?: $row['path_original'];
    }

    public function countForAdmin(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM products');
        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, array> */
    public function listAllForAdmin(int $limit = 100, int $offset = 0): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.sku, p.slug, p.title, p.price_from_ore, p.is_active, p.is_featured, p.deleted_at, b.name AS brand_name
             FROM products p LEFT JOIN brands b ON b.id = p.brand_id ORDER BY p.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForAdmin(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON b.id = p.brand_id WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (sku, slug, title, subtitle, brand_id, description_short, description_html, price_from_ore, price_to_ore, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['sku'] ?? null,
            $data['slug'],
            $data['title'],
            $data['subtitle'] ?? null,
            $data['brand_id'] ?? null,
            $data['description_short'] ?? null,
            $data['description_html'] ?? null,
            $data['price_from_ore'] ?? 0,
            $data['price_to_ore'] ?? null,
            $data['is_active'] ?? 1,
            $data['is_featured'] ?? 0,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products SET sku = ?, slug = ?, title = ?, subtitle = ?, brand_id = ?, description_short = ?, description_html = ?, price_from_ore = ?, price_to_ore = ?, is_active = ?, is_featured = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['sku'] ?? null,
            $data['slug'],
            $data['title'],
            $data['subtitle'] ?? null,
            $data['brand_id'] ?? null,
            $data['description_short'] ?? null,
            $data['description_html'] ?? null,
            $data['price_from_ore'] ?? 0,
            $data['price_to_ore'] ?? null,
            $data['is_active'] ?? 1,
            $data['is_featured'] ?? 0,
            $id,
        ]);
    }

    public function softDelete(int $id): void
    {
        $this->pdo->prepare('UPDATE products SET deleted_at = CURRENT_TIMESTAMP(6) WHERE id = ?')->execute([$id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug = ? AND deleted_at IS NULL';
        $params = [$slug];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public function setPrimaryCategory(int $productId, ?int $categoryId): void
    {
        $this->pdo->prepare('UPDATE products SET primary_category_id = ? WHERE id = ?')->execute([$categoryId, $productId]);
    }

    /** @return array<int, array{slug: string}> */
    public function listSlugsForSitemap(): array
    {
        $stmt = $this->pdo->query('SELECT slug FROM products WHERE is_active = 1 AND deleted_at IS NULL');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search products by title, slug, sku, description_short.
     * @return array{items: array, total: int}
     */
    public function search(string $query, int $limit = 24, int $offset = 0): array
    {
        $query = trim($query);
        if ($query === '') {
            return ['items' => [], 'total' => 0];
        }
        $term = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query) . '%';
        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM products WHERE is_active = 1 AND deleted_at IS NULL
             AND (title LIKE ? ESCAPE "\\\\" OR slug LIKE ? ESCAPE "\\\\" OR sku LIKE ? ESCAPE "\\\\" OR description_short LIKE ? ESCAPE "\\\\")'
        );
        $countStmt->execute([$term, $term, $term, $term]);
        $total = (int) $countStmt->fetchColumn();
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        $stmt = $this->pdo->prepare(
            'SELECT id, slug, title, price_from_ore, price_to_ore, primary_image_id
             FROM products WHERE is_active = 1 AND deleted_at IS NULL
             AND (title LIKE ? ESCAPE "\\\\" OR slug LIKE ? ESCAPE "\\\\" OR sku LIKE ? ESCAPE "\\\\" OR description_short LIKE ? ESCAPE "\\\\")
             ORDER BY title LIMIT ' . $limit . ' OFFSET ' . $offset
        );
        $stmt->execute([$term, $term, $term, $term]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ['items' => $items, 'total' => $total];
    }

    public function setProductCategories(int $productId, array $categoryIds, ?int $primaryId = null): void
    {
        $this->pdo->prepare('DELETE FROM product_categories WHERE product_id = ?')->execute([$productId]);
        foreach ($categoryIds as $cid) {
            $primary = ($primaryId !== null && (int) $cid === $primaryId) ? 1 : 0;
            $this->pdo->prepare('INSERT INTO product_categories (product_id, category_id, is_primary) VALUES (?, ?, ?)')->execute([$productId, (int) $cid, $primary]);
        }
    }

    public function addImage(int $productId, string $pathOriginal, ?string $pathWebp = null, ?string $altText = null, int $sortOrder = 0): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO product_images (product_id, path_original, path_webp, alt_text, sort_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$productId, $pathOriginal, $pathWebp, $altText, $sortOrder]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteImage(int $imageId, int $productId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM product_images WHERE id = ? AND product_id = ?');
        $stmt->execute([$imageId, $productId]);
        if ($stmt->rowCount() === 0) {
            return false;
        }
        $this->pdo->prepare('UPDATE products SET primary_image_id = NULL WHERE id = ? AND primary_image_id = ?')->execute([$productId, $imageId]);
        return true;
    }

    public function setPrimaryImage(int $productId, ?int $imageId): void
    {
        if ($imageId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM product_images WHERE id = ? AND product_id = ?');
            $stmt->execute([$imageId, $productId]);
            if ($stmt->fetchColumn() === false) {
                return;
            }
        }
        $this->pdo->prepare('UPDATE products SET primary_image_id = ? WHERE id = ?')->execute([$imageId, $productId]);
    }

    public function getImageById(int $imageId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM product_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
