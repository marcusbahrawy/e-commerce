<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

final class CatalogService
{
    public function __construct(
        private CategoryRepository $categoryRepo,
        private ProductRepository $productRepo
    ) {
    }

    public function getCategoryBySlug(string $slug, ?string $parentSlug = null): ?array
    {
        $parentId = null;
        if ($parentSlug !== null && $parentSlug !== '') {
            $parent = $this->categoryRepo->findBySlug($parentSlug, null);
            if ($parent === null) {
                return null;
            }
            $parentId = (int) $parent['id'];
        }
        return $this->categoryRepo->findBySlug($slug, $parentId);
    }

    /** @return array<int, array> */
    public function getBreadcrumbsForCategory(int $categoryId): array
    {
        return $this->categoryRepo->getBreadcrumbs($categoryId);
    }

    /** @return array<int, array> */
    public function getSubcategories(int $categoryId): array
    {
        return $this->categoryRepo->getChildren($categoryId);
    }

    /**
     * @return array{items: array, total: int, category: array}
     */
    public function listProductsInCategory(int $categoryId, array $options = []): array
    {
        $result = $this->productRepo->listByCategory($categoryId, $options);
        $category = $this->categoryRepo->findById($categoryId);
        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'category' => $category ?: [],
        ];
    }

    public function getProductBySlug(string $slug): ?array
    {
        return $this->productRepo->findBySlug($slug);
    }

    /** @return array<int, array> */
    public function getProductImages(int $productId): array
    {
        return $this->productRepo->getImages($productId);
    }

    public function getProductPrimaryImagePath(int $productId, ?int $primaryImageId = null): ?string
    {
        return $this->productRepo->getPrimaryImagePath($productId, $primaryImageId);
    }

    /** @return array<int, array> */
    public function getFeaturedProducts(int $limit = 12): array
    {
        return $this->productRepo->getFeatured($limit);
    }

    /**
     * @return array{items: array, total: int}
     */
    public function searchProducts(string $query, int $page = 1, int $perPage = 24): array
    {
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;
        return $this->productRepo->search($query, $perPage, $offset);
    }

    /** @return array<int, array> */
    public function getRootCategories(): array
    {
        return $this->categoryRepo->getRootCategories();
    }
}
