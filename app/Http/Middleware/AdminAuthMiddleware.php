<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Support\Auth;

class AdminAuthMiddleware
{
    public function __invoke(Request $request, callable $next): Response
    {
        $path = $request->path();
        if (!str_starts_with($path, '/admin')) {
            return $next($request);
        }
        if (str_starts_with($path, '/admin/login') || $path === '/admin/login') {
            return $next($request);
        }
        if (Auth::check()) {
            return $next($request);
        }
        return Response::redirect('/admin/login', 302);
    }
}
