<?php

declare(strict_types=1);

use App\Http\Request;
use App\Support\Html;
use App\Support\Csrf;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return Html::escape($value ?? '');
    }
}

if (!function_exists('url')) {
    function url(string $path = '', array $query = []): string
    {
        $base = rtrim(\App\Support\Env::string('APP_URL', ''), '/');
        $path = $path === '' ? '' : '/' . ltrim($path, '/');
        $url = $base . $path;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('request')) {
    function request(): ?Request
    {
        return $GLOBALS['__request'] ?? null;
    }
}
