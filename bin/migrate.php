<?php

declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

\App\Support\Env::load($root . '/.env');

$pdo = \App\Support\Database::pdo();

// Ensure migrations table
$pdo->exec("CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(255) NOT NULL PRIMARY KEY, executed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6))");

$run = $pdo->prepare('INSERT INTO schema_migrations (version) VALUES (?)');
$migrationsDir = $root . '/migrations';
$files = glob($migrationsDir . '/*.php');
sort($files);

foreach ($files as $file) {
    $basename = basename($file, '.php');
    if ($basename === '0000_schema_migrations') {
        continue;
    }
    $version = $basename;
    $exists = $pdo->query("SELECT 1 FROM schema_migrations WHERE version = " . $pdo->quote($version))->fetch();
    if ($exists) {
        echo "Skip: $version\n";
        continue;
    }
    $m = require $file;
    if (!isset($m['up'])) {
        echo "Invalid migration: $file\n";
        continue;
    }
    $sql = trim($m['up']);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
        if ($statement !== '') {
            $pdo->exec($statement . ';');
        }
    }
    $run->execute([$version]);
    echo "Ran: $version\n";
}

echo "Done.\n";
