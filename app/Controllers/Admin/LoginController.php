<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Support\Auth;

class LoginController
{
    public function __construct(private UserRepository $userRepo)
    {
    }

    public function show(Request $request, array $params): Response
    {
        if (Auth::check()) {
            return Response::redirect('/admin', 302);
        }
        $html = $this->render('admin/login', ['error' => null]);
        return Response::html($html);
    }

    public function login(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/login', 302);
        }
        $email = trim($request->input('email', '') ?? '');
        $password = $request->input('password', '') ?? '';
        if ($email === '' || $password === '') {
            $html = $this->render('admin/login', ['error' => 'Fyll inn e-post og passord.']);
            return Response::html($html);
        }
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !$user['is_active']) {
            $html = $this->render('admin/login', ['error' => 'Ugyldig e-post eller passord.']);
            return Response::html($html);
        }
        if (!password_verify($password, $user['password_hash'])) {
            $html = $this->render('admin/login', ['error' => 'Ugyldig e-post eller passord.']);
            return Response::html($html);
        }
        if (!$this->userRepo->hasRole((int) $user['id'], 'admin')) {
            $html = $this->render('admin/login', ['error' => 'Ingen tilgang.']);
            return Response::html($html);
        }
        Auth::login((int) $user['id']);
        $this->userRepo->updateLastLogin((int) $user['id']);
        return Response::redirect('/admin', 302);
    }

    public function logout(Request $request, array $params): Response
    {
        Auth::logout();
        return Response::redirect('/admin/login', 302);
    }

    private function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__, 2) . '/Templates/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
