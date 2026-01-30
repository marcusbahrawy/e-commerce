<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class SecurityHeadersMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        $response = $next($request);
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');
        $response = $response->withHeader('X-Frame-Options', 'DENY');
        $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        return $response;
    }
}
