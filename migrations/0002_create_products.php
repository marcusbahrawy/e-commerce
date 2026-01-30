<?php

declare(strict_types=1);

return [
    'up' => <<<'SQL'
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(100) NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(500) NOT NULL,
    subtitle VARCHAR(500) NULL,
    brand_id BIGINT UNSIGNED NULL,
    description_short TEXT NULL,
    description_html MEDIUMTEXT NULL,
    weight_grams INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    primary_category_id BIGINT UNSIGNED NULL,
    primary_image_id BIGINT UNSIGNED NULL,
    price_from_ore INT NULL,
    price_to_ore INT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    deleted_at DATETIME(6) NULL,
    UNIQUE KEY uq_products_slug (slug),
    KEY idx_products_brand (brand_id),
    KEY idx_products_active (is_active),
    KEY idx_products_featured (is_featured),
    KEY idx_products_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_categories (
    product_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (product_id, category_id),
    KEY idx_product_categories_category (category_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    variant_id BIGINT UNSIGNED NULL,
    path_original VARCHAR(500) NOT NULL,
    path_webp VARCHAR(500) NULL,
    alt_text VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    KEY idx_product_images_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
,
    'down' => <<<'SQL'
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS product_categories;
DROP TABLE IF EXISTS products;
SQL
,
];
