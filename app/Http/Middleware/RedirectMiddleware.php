<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\RedirectRepository;
use App\Support\Env;
use PDOException;

class RedirectMiddleware
{
    public function __construct(private RedirectRepository $redirectRepo)
    {
    }

    public function __invoke(Request $request, callable $next): Response
    {
        try {
            $path = $request->path();
            $redirect = $this->redirectRepo->findByOldPath($path);
        } catch (PDOException) {
            return $next($request);
        }
        if ($redirect !== null) {
            $this->redirectRepo->incrementHits((int) $redirect['id']);
            $newPath = $redirect['new_path'];
            if (($q = $request->uri()) !== '' && ($pos = strpos($q, '?')) !== false) {
                $newPath .= substr($q, $pos);
            }
            if (str_starts_with($newPath, '/') && !str_starts_with($newPath, '//')) {
                $base = rtrim(Env::string('APP_URL', ''), '/');
                $newPath = $base . $newPath;
            }
            $statusCode = (int) ($redirect['status_code'] ?? 301);
            if ($statusCode < 300 || $statusCode > 399) {
                $statusCode = 301;
            }
            return Response::redirect($newPath, $statusCode);
        }
        return $next($request);
    }
}
