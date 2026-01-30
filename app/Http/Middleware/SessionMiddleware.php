<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class SessionMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => (int) (\App\Support\Env::string('SESSION_LIFETIME', '120') ?: 0),
                'path' => '/',
                'domain' => '',
                'secure' => ($request->uri() !== '' && str_starts_with(\App\Support\Env::string('APP_URL', ''), 'https')),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
        return $next($request);
    }
}
