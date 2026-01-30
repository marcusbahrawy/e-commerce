<?php

declare(strict_types=1);

namespace App\Support;

final class Auth
{
    private const SESSION_KEY = 'admin_user_id';

    public static function login(int $userId): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        Csrf::rotate();
        $_SESSION[self::SESSION_KEY] = $userId;
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function userId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = $_SESSION[self::SESSION_KEY] ?? null;
        return $id !== null ? (int) $id : null;
    }

    public static function check(): bool
    {
        return self::userId() !== null;
    }
}
