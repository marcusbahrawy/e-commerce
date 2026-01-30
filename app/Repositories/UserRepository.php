<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash, first_name, last_name, is_active FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, email, password_hash, first_name, last_name, is_active FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function hasRole(int $userId, string $roleKey): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM user_roles ur INNER JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = ? AND r.`key` = ? LIMIT 1'
        );
        $stmt->execute([$userId, $roleKey]);
        return $stmt->fetchColumn() !== false;
    }

    public function updateLastLogin(int $userId): void
    {
        $this->pdo->prepare('UPDATE users SET last_login_at = CURRENT_TIMESTAMP(6) WHERE id = ?')->execute([$userId]);
    }

    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        if ($excludeUserId !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id != ? LIMIT 1');
            $stmt->execute([$email, $excludeUserId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
        }
        return $stmt->fetchColumn() !== false;
    }

    public function updateProfile(int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?');
        $stmt->execute([
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['email'],
            $userId,
        ]);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $userId]);
    }

    public function createCustomer(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, is_active) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute([
            $data['email'],
            $data['password_hash'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function createPasswordResetToken(int $userId, string $token, \DateTimeInterface $expiresAt): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $token, $expiresAt->format('Y-m-d H:i:s.u')]);
    }

    public function findValidPasswordResetToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT prt.id, prt.user_id, u.email FROM password_reset_tokens prt
             INNER JOIN users u ON u.id = prt.user_id
             WHERE prt.token = ? AND prt.expires_at > NOW() AND u.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function deletePasswordResetToken(string $token): void
    {
        $this->pdo->prepare('DELETE FROM password_reset_tokens WHERE token = ?')->execute([$token]);
    }

    public function deleteExpiredPasswordResetTokens(): void
    {
        $this->pdo->exec('DELETE FROM password_reset_tokens WHERE expires_at < NOW()');
    }
}
