<?php

declare(strict_types=1);

namespace App\Http;

class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $cookies;
    private string $method;
    private string $uri;
    private string $path;

    public function __construct(array $query = [], array $body = [], array $server = [], array $cookies = [])
    {
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $server['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH) ?: '/';
        $this->path = '/' . trim($this->path, '/');
        if ($this->path === '') {
            $this->path = '/';
        }
    }

    public static function fromGlobals(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE ?? []
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function query(string $key, ?string $default = null): ?string
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, ?string $default = null): ?string
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function csrfToken(): ?string
    {
        return $this->body['_token'] ?? $this->server['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['HTTP_X_REAL_IP'] ?? $this->server['REMOTE_ADDR'] ?? '';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = $this->server[$key] ?? null;
        return is_string($value) ? $value : null;
    }
}
