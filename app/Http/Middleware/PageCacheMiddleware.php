<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class PageCacheMiddleware
{
    private const CACHEABLE_PREFIXES = ['/', '/kategori/', '/produkt/', '/side/'];

    public function __construct(
        private string $storageDir,
        private int $ttlSeconds = 900
    ) {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        $path = $request->path();
        if (!$this->isCacheablePath($path)) {
            return $next($request);
        }
        $query = $request->uri();
        $pos = strpos($query, '?');
        $queryString = $pos !== false ? substr($query, $pos + 1) : '';
        $key = md5($path . '|' . $queryString);
        $subdir = substr($key, 0, 2);
        $dir = $this->storageDir . '/page/' . $subdir;
        $file = $dir . '/' . $key . '.cache';

        if (is_file($file)) {
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $data = @json_decode($raw, true);
                if (is_array($data) && isset($data['expires'], $data['body']) && $data['expires'] > time()) {
                    return Response::html($data['body']);
                }
            }
        }

        $response = $next($request);
        if (!($response instanceof Response)) {
            $response = Response::html((string) $response);
        }
        if ($response->statusCode() === 200 && $response->body() !== '') {
            $this->store($file, $dir, $response->body());
        }
        return $response;
    }

    private function isCacheablePath(string $path): bool
    {
        if ($path === '/') {
            return true;
        }
        foreach (self::CACHEABLE_PREFIXES as $prefix) {
            if ($prefix !== '/' && str_starts_with($path, $prefix)) {
                return true;
            }
        }
        return false;
    }

    private function store(string $file, string $dir, string $body): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $data = [
            'expires' => time() + $this->ttlSeconds,
            'body' => $body,
        ];
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /** Clear all page cache (e.g. after product/category update). */
    public static function purge(string $storageDir): void
    {
        $dir = rtrim($storageDir, '/') . '/page';
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $entry) {
            if ($entry->isFile()) {
                @unlink($entry->getPathname());
            } elseif ($entry->isDir()) {
                @rmdir($entry->getPathname());
            }
        }
        @rmdir($dir);
    }
}
