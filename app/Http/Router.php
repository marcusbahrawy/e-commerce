<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    /** @var array<string, array{method: string, pattern: string, handler: callable, middleware: array}> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $namedRoutes = [];

    /** @var callable[] */
    private array $globalMiddleware = [];

    public function get(string $path, callable $handler, ?string $name = null): self
    {
        return $this->add('GET', $path, $handler, [], $name);
    }

    public function post(string $path, callable $handler, ?string $name = null): self
    {
        return $this->add('POST', $path, $handler, [], $name);
    }

    public function add(string $method, string $path, callable $handler, array $middleware = [], ?string $name = null): self
    {
        $pattern = $this->pathToRegex($path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => $path,
            'regex' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        if ($name !== null) {
            $this->namedRoutes[$name] = $path;
        }
        return $this;
    }

    public function middleware(callable ...$middleware): self
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }
            $params = $this->match($route['regex'], $request->path());
            if ($params === null) {
                continue;
            }
            $handler = $route['handler'];
            $middleware = array_merge($this->globalMiddleware, $route['middleware'] ?? []);
            $next = function (Request $req) use ($handler, $params) {
                return $handler($req, $params);
            };
            foreach (array_reverse($middleware) as $mw) {
                $next = function (Request $req) use ($mw, $next) {
                    return $mw($req, $next);
                };
            }
            $response = $next($request);
            return $response instanceof Response ? $response : Response::html((string) $response);
        }
        return Response::html('<h1>404 Not Found</h1>', 404);
    }

    public function route(string $name, array $params = []): string
    {
        $path = $this->namedRoutes[$name] ?? $name;
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string) $value, $path);
        }
        return $path;
    }

    private function pathToRegex(string $path): string
    {
        $path = preg_quote($path, '#');
        $path = preg_replace('#\\\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $path . '$#';
    }

    private function match(string $regex, string $path): ?array
    {
        if (preg_match($regex, $path, $m) !== 1) {
            return null;
        }
        $params = [];
        foreach ($m as $k => $v) {
            if (!is_int($k)) {
                $params[$k] = $v;
            }
        }
        return $params;
    }
}
