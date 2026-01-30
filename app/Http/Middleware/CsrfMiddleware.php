<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Support\Csrf;

class CsrfMiddleware
{
    /** @var string[] */
    private array $methods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __invoke(Request $request, callable $next): Response
    {
        if (!in_array($request->method(), $this->methods, true)) {
            return $next($request);
        }
        if (str_starts_with($request->path(), '/webhooks/')) {
            return $next($request);
        }
        if (!Csrf::validate($request->csrfToken())) {
            return Response::html('<h1>419 Page Expired</h1>', 419);
        }
        return $next($request);
    }
}
