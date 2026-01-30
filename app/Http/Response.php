<?php

declare(strict_types=1);

namespace App\Http;

class Response
{
    private int $statusCode;
    private array $headers;
    private string $body;

    public function __construct(string $body = '', int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value", true);
        }
        echo $this->body;
    }

    public static function html(string $html, int $statusCode = 200): self
    {
        return new self($html, $statusCode, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, [
            'Location' => $url,
        ]);
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $statusCode,
            [
                'Content-Type' => 'application/json; charset=UTF-8',
            ]
        );
    }
}
