<?php

declare(strict_types=1);

return [
    'up' => <<<'SQL'
CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME(6) NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    UNIQUE KEY uq_password_reset_tokens_token (token),
    KEY idx_password_reset_tokens_expires (expires_at),
    KEY idx_password_reset_tokens_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
,
    'down' => <<<'SQL'
DROP TABLE IF EXISTS password_reset_tokens;
SQL
,
];
