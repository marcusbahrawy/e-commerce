<?php

declare(strict_types=1);

namespace App\Support;

final class Env
{
    private static ?array $loaded = null;

    public static function load(string $path): void
    {
        if (self::$loaded !== null) {
            return;
        }
        if (!is_file($path)) {
            self::$loaded = [];
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        self::$loaded = [];
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
            self::$loaded[$name] = $value;
        }
    }

    public static function string(string $key, string $default = ''): string
    {
        $v = getenv($key);
        return $v !== false ? (string) $v : $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = getenv($key);
        return $v !== false ? (int) $v : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = getenv($key);
        if ($v === false) {
            return $default;
        }
        return in_array(strtolower($v), ['1', 'true', 'on', 'yes'], true);
    }
}
