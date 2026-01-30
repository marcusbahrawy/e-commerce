<?php

declare(strict_types=1);

namespace App\Support;

final class RateLimiter
{
    private string $dir;
    private int $maxAttempts;
    private int $windowSeconds;

    public function __construct(string $storageDir, int $maxAttempts = 5, int $windowSeconds = 900)
    {
        $this->dir = rtrim($storageDir, '/') . '/rate_limit';
        $this->maxAttempts = $maxAttempts;
        $this->windowSeconds = $windowSeconds;
    }

    public function keyFromIp(string $ip): string
    {
        $ip = trim(explode(',', $ip)[0] ?? $ip);
        return preg_replace('/[^a-fA-F0-9.]/', '', $ip) ?: 'unknown';
    }

    private function filePath(string $key): string
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        return $this->dir . '/' . $key . '.txt';
    }

    public function isLimited(string $ip): bool
    {
        $key = $this->keyFromIp($ip);
        $path = $this->filePath($key);
        if (!is_file($path)) {
            return false;
        }
        $content = @file_get_contents($path);
        if ($content === false) {
            return false;
        }
        $parts = explode("\n", trim($content), 2);
        $count = (int) ($parts[0] ?? 0);
        $timestamp = (int) ($parts[1] ?? 0);
        $now = time();
        if ($now - $timestamp > $this->windowSeconds) {
            @unlink($path);
            return false;
        }
        return $count >= $this->maxAttempts;
    }

    public function recordFailure(string $ip): void
    {
        $key = $this->keyFromIp($ip);
        $path = $this->filePath($key);
        $now = time();
        $count = 0;
        $timestamp = $now;
        if (is_file($path)) {
            $content = @file_get_contents($path);
            if ($content !== false) {
                $parts = explode("\n", trim($content), 2);
                $count = (int) ($parts[0] ?? 0);
                $timestamp = (int) ($parts[1] ?? $now);
                if ($now - $timestamp > $this->windowSeconds) {
                    $count = 0;
                    $timestamp = $now;
                }
            }
        }
        $count++;
        file_put_contents($path, $count . "\n" . $timestamp, LOCK_EX);
    }

    public function clear(string $ip): void
    {
        $key = $this->keyFromIp($ip);
        $path = $this->filePath($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
