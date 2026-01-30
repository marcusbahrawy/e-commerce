<?php

declare(strict_types=1);

return [
    'up' => <<<'SQL'
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_id VARCHAR(26) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'unpaid',
    fulfillment_status VARCHAR(50) NOT NULL DEFAULT 'unfulfilled',
    currency VARCHAR(3) NOT NULL DEFAULT 'NOK',
    subtotal_ore INT NOT NULL DEFAULT 0,
    shipping_ore INT NOT NULL DEFAULT 0,
    tax_ore INT NOT NULL DEFAULT 0,
    discount_ore INT NOT NULL DEFAULT 0,
    total_ore INT NOT NULL DEFAULT 0,
    shipping_address_json JSON NULL,
    billing_address_json JSON NULL,
    shipping_method_snapshot JSON NULL,
    notes TEXT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    UNIQUE KEY uq_orders_public_id (public_id),
    KEY idx_orders_status (status, created_at),
    KEY idx_orders_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variant_id BIGINT UNSIGNED NULL,
    sku_snapshot VARCHAR(100) NULL,
    title_snapshot VARCHAR(500) NOT NULL,
    unit_price_ore INT NOT NULL,
    qty INT UNSIGNED NOT NULL DEFAULT 1,
    line_total_ore INT NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    KEY idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(50) NOT NULL DEFAULT 'stripe',
    provider_payment_intent_id VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'created',
    amount_ore INT NOT NULL,
    raw_json JSON NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    KEY idx_order_payments_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
,
    'down' => <<<'SQL'
DROP TABLE IF EXISTS order_payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
SQL
,
];
