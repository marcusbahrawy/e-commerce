<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Support\Auth;

class ProfileController
{
    public function __construct(private UserRepository $userRepo)
    {
    }

    public function passwordForm(Request $request, array $params): Response
    {
        $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => null, 'success' => null]);
        return Response::html($html);
    }

    public function passwordUpdate(Request $request, array $params): Response
    {
        $userId = Auth::userId();
        if ($userId === null) {
            return Response::redirect('/admin/login', 302);
        }
        if (!$request->isPost()) {
            return Response::redirect('/admin/passord', 302);
        }
        $current = $request->input('current_password', '') ?? '';
        $new = $request->input('new_password', '') ?? '';
        $confirm = $request->input('new_password_confirm', '') ?? '';
        if ($current === '' || $new === '' || $confirm === '') {
            $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => 'Fyll inn alle felt.', 'success' => null]);
            return Response::html($html);
        }
        if (strlen($new) < 8) {
            $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => 'Nytt passord må være minst 8 tegn.', 'success' => null]);
            return Response::html($html);
        }
        if ($new !== $confirm) {
            $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => 'Nytt passord og bekreftelse stemmer ikke overens.', 'success' => null]);
            return Response::html($html);
        }
        $user = $this->userRepo->findById($userId);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => 'Nåværende passord er feil.', 'success' => null]);
            return Response::html($html);
        }
        $this->userRepo->updatePassword($userId, password_hash($new, PASSWORD_DEFAULT));
        $html = $this->render('admin/profile/password', ['title' => 'Bytt passord', 'error' => null, 'success' => 'Passordet er oppdatert.']);
        return Response::html($html);
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
