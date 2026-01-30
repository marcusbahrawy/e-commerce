<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const INPUT_NAME = '_token';

    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::INPUT_NAME . '" value="' . e(self::token()) . '">';
    }

    public static function validate(?string $token): bool
    {
        return $token !== null && hash_equals(self::token(), $token);
    }

    public static function rotate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}
