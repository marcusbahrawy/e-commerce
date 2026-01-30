<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

\App\Support\Env::load($root . '/.env');

$pdo = \App\Support\Database::pdo();

// Ensure migrations have run (minimal check)
$pdo->query('SELECT 1 FROM products LIMIT 1');
$pdo->query('SELECT 1 FROM categories LIMIT 1');

// One brand
$pdo->exec("INSERT IGNORE INTO brands (id, name, slug) VALUES (1, 'Motorleaks', 'motorleaks')");

// Root category
$pdo->exec("INSERT IGNORE INTO categories (id, parent_id, slug, name, sort_order, is_active) VALUES (1, NULL, 'deler', 'Deler', 0, 1)");

// One product
$pdo->exec("INSERT IGNORE INTO products (id, slug, title, brand_id, is_active, is_featured, price_from_ore, primary_category_id) VALUES (1, 'eksempel-produkt', 'Eksempelprodukt', 1, 1, 1, 19900, 1)");
$pdo->exec('INSERT IGNORE INTO product_categories (product_id, category_id, is_primary) VALUES (1, 1, 1)');

// CMS pages (placeholders)
$pages = [
    ['om-oss', 'Om oss', 'Om Motorleaks', 'Om oss', '<p>Informasjon om oss kommer her.</p>'],
    ['kjopsbetingelser', 'Kjøpsbetingelser', 'Kjøpsbetingelser', 'Kjøpsbetingelser', '<p>Kjøpsbetingelser kommer her.</p>'],
    ['angrerett-retur', 'Angrerett og retur', 'Angrerett og retur', 'Angrerett og retur', '<p>Angrerett og returinformasjon.</p>'],
    ['personvern', 'Personvern', 'Personvern', 'Personvern', '<p>Personvernerklæring.</p>'],
];
$stmt = $pdo->prepare('INSERT IGNORE INTO pages (slug, title, meta_title, meta_description, content_html, is_active) VALUES (?, ?, ?, ?, ?, 1)');
foreach ($pages as $p) {
    $stmt->execute($p);
}

// Default shipping method
try {
    $pdo->query('SELECT 1 FROM shipping_methods LIMIT 1');
    $stmt = $pdo->prepare('INSERT IGNORE INTO shipping_methods (code, name, price_ore, free_over_ore, is_active, sort_order) VALUES (?, ?, ?, ?, 1, 0)');
    $stmt->execute(['standard', 'Standard frakt', 9900, 50000]);
} catch (Throwable) {
    // table not present
}

// Admin user (requires migration 0006)
try {
    $pdo->query('SELECT 1 FROM users LIMIT 1');
    $pdo->exec("INSERT IGNORE INTO roles (id, `key`, name) VALUES (1, 'admin', 'Administrator')");
    $hash = password_hash('changeme', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (id, email, password_hash, first_name, is_active) VALUES (1, 'admin@motorleaks.no', '$hash', 'Admin', 1)");
    $pdo->exec('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (1, 1)');
    $customerHash = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (email, password_hash, first_name, is_active) VALUES ('customer@motorleaks.no', ?, 'Kunde', 1)");
    $stmt->execute([$customerHash]);
} catch (Throwable) {
    // users/roles tables not present
}

echo "Seed done.\n";
