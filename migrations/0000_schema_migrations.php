<?php

declare(strict_types=1);

return [
    'up' => <<<'SQL'
CREATE TABLE schema_migrations (
    version VARCHAR(255) NOT NULL PRIMARY KEY,
    executed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
,
    'down' => 'DROP TABLE IF EXISTS schema_migrations;',
];
