<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;
use App\Support\Auth;

class UsersController
{
    public function __construct(
        private UserRepository $userRepo,
        private AuditLogRepository $auditLogRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $users = $this->userRepo->listUsersWithRole('admin');
        $html = $this->render('admin/users/index', ['title' => 'Brukere', 'users' => $users]);
        return Response::html($html);
    }

    public function createForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/users/form', ['title' => 'Ny adminbruker', 'user' => null]);
        return Response::html($html);
    }

    public function create(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/brukere', 302);
        }
        $email = trim($request->input('email', '') ?? '');
        $password = $request->input('password', '') ?? '';
        $firstName = trim($request->input('first_name', '') ?? '') ?: null;
        if ($email === '' || $password === '') {
            $html = $this->render('admin/users/form', ['title' => 'Ny adminbruker', 'user' => null, 'error' => 'E-post og passord er påkrevd.']);
            return Response::html($html);
        }
        if (strlen($password) < 8) {
            $html = $this->render('admin/users/form', ['title' => 'Ny adminbruker', 'user' => null, 'error' => 'Passord må være minst 8 tegn.']);
            return Response::html($html);
        }
        if ($this->userRepo->emailExists($email)) {
            $html = $this->render('admin/users/form', ['title' => 'Ny adminbruker', 'user' => null, 'error' => 'E-postadressen er allerede i bruk.']);
            return Response::html($html);
        }
        $userId = $this->userRepo->createAdminUser([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
        ]);
        $this->auditLogRepo->log(Auth::userId(), 'user.create', 'user', (string) $userId, $email, $request->ip());
        return Response::redirect('/admin/brukere', 302);
    }

    public function editForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $user = $this->userRepo->findById($id);
        if ($user === null || !$this->userRepo->hasRole($id, 'admin')) {
            return Response::html('<h1>404</h1>', 404);
        }
        unset($user['password_hash']);
        $html = $this->render('admin/users/form', ['title' => 'Rediger bruker', 'user' => $user]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/brukere', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $user = $this->userRepo->findById($id);
        if ($user === null || !$this->userRepo->hasRole($id, 'admin')) {
            return Response::html('<h1>404</h1>', 404);
        }
        $email = trim($request->input('email', '') ?? '');
        $firstName = trim($request->input('first_name', '') ?? '') ?: null;
        $isActive = $request->input('is_active', '1') ? 1 : 0;
        if ($email === '') {
            $html = $this->render('admin/users/form', ['title' => 'Rediger bruker', 'user' => array_merge($user, ['email' => $request->input('email'), 'first_name' => $firstName, 'is_active' => $isActive]), 'error' => 'E-post er påkrevd.']);
            return Response::html($html);
        }
        if ($this->userRepo->emailExists($email, $id)) {
            $html = $this->render('admin/users/form', ['title' => 'Rediger bruker', 'user' => array_merge($user, ['email' => $email, 'first_name' => $firstName, 'is_active' => $isActive]), 'error' => 'E-postadressen er allerede i bruk.']);
            return Response::html($html);
        }
        $this->userRepo->updateProfile($id, ['email' => $email, 'first_name' => $firstName, 'last_name' => $user['last_name'] ?? null]);
        $this->userRepo->updateActive($id, (bool) $isActive);
        $this->auditLogRepo->log(Auth::userId(), 'user.update', 'user', (string) $id, $email, $request->ip());
        return Response::redirect('/admin/brukere', 302);
    }

    public function setPasswordForm(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $user = $this->userRepo->findById($id);
        if ($user === null || !$this->userRepo->hasRole($id, 'admin')) {
            return Response::html('<h1>404</h1>', 404);
        }
        unset($user['password_hash']);
        $html = $this->render('admin/users/password', ['title' => 'Sett passord for ' . ($user['email'] ?? ''), 'user' => $user]);
        return Response::html($html);
    }

    public function setPassword(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/brukere', 302);
        }
        $id = (int) ($params['id'] ?? 0);
        $user = $this->userRepo->findById($id);
        if ($user === null || !$this->userRepo->hasRole($id, 'admin')) {
            return Response::html('<h1>404</h1>', 404);
        }
        $password = $request->input('new_password', '') ?? '';
        $confirm = $request->input('new_password_confirm', '') ?? '';
        if ($password === '' || $confirm === '') {
            unset($user['password_hash']);
            $html = $this->render('admin/users/password', ['title' => 'Sett passord for ' . ($user['email'] ?? ''), 'user' => $user, 'error' => 'Fyll inn passord og bekreftelse.']);
            return Response::html($html);
        }
        if (strlen($password) < 8) {
            unset($user['password_hash']);
            $html = $this->render('admin/users/password', ['title' => 'Sett passord for ' . ($user['email'] ?? ''), 'user' => $user, 'error' => 'Passord må være minst 8 tegn.']);
            return Response::html($html);
        }
        if ($password !== $confirm) {
            unset($user['password_hash']);
            $html = $this->render('admin/users/password', ['title' => 'Sett passord for ' . ($user['email'] ?? ''), 'user' => $user, 'error' => 'Passord og bekreftelse stemmer ikke overens.']);
            return Response::html($html);
        }
        $this->userRepo->updatePassword($id, password_hash($password, PASSWORD_DEFAULT));
        $this->auditLogRepo->log(Auth::userId(), 'user.set_password', 'user', (string) $id, $user['email'] ?? null, $request->ip());
        return Response::redirect('/admin/brukere?ok=passord', 302);
    }

    private function render(string $view, array $data = []): string
    {
        $base = dirname(__DIR__, 2) . '/Templates';
        $layoutPath = $base . '/admin/layout.php';
        $viewPath = $base . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewPath)) {
            return '<p>Side ikke funnet.</p>';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();
        $data['content'] = $content;
        $data['title'] = $data['title'] ?? 'Admin';
        extract($data, EXTR_SKIP);
        ob_start();
        require $layoutPath;
        return (string) ob_get_clean();
    }
}
