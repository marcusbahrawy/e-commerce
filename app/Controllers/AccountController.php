<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Support\CustomerAuth;

class AccountController
{
    public function __construct(
        private UserRepository $userRepo,
        private OrderRepository $orderRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        return Response::redirect(url('/konto/login'), 302);
    }

    public function loginForm(Request $request, array $params): Response
    {
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        $content = $this->render('pages/account/login', ['error' => null]);
        $html = $this->layout($content, ['title' => 'Logg inn — Min konto', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function registerForm(Request $request, array $params): Response
    {
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        $content = $this->render('pages/account/register', ['error' => null]);
        $html = $this->layout($content, ['title' => 'Opprett konto — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function register(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/konto/registrer'), 302);
        }
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        $email = trim($request->input('email', '') ?? '');
        $password = $request->input('password', '') ?? '';
        $passwordConfirm = $request->input('password_confirm', '') ?? '';
        $firstName = trim($request->input('first_name', '') ?? '') ?: null;
        $lastName = trim($request->input('last_name', '') ?? '') ?: null;
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $content = $this->render('pages/account/register', ['error' => 'Ugyldig e-postadresse.']);
            $html = $this->layout($content, ['title' => 'Opprett konto — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($this->userRepo->emailExists($email)) {
            $content = $this->render('pages/account/register', ['error' => 'E-postadressen er allerede i bruk.']);
            $html = $this->layout($content, ['title' => 'Opprett konto — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if (strlen($password) < 8) {
            $content = $this->render('pages/account/register', ['error' => 'Passordet må være minst 8 tegn.']);
            $html = $this->layout($content, ['title' => 'Opprett konto — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($password !== $passwordConfirm) {
            $content = $this->render('pages/account/register', ['error' => 'Passordene stemmer ikke overens.']);
            $html = $this->layout($content, ['title' => 'Opprett konto — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $this->userRepo->createCustomer([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
        $user = $this->userRepo->findByEmail($email);
        if ($user !== null) {
            CustomerAuth::login((int) $user['id']);
        }
        return Response::redirect(url('/konto/ordre'), 302);
    }

    public function login(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $email = trim($request->input('email', '') ?? '');
        $password = $request->input('password', '') ?? '';
        if ($email === '' || $password === '') {
            $content = $this->render('pages/account/login', ['error' => 'Fyll inn e-post og passord.']);
            $html = $this->layout($content, ['title' => 'Logg inn — Min konto', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !$user['is_active']) {
            $content = $this->render('pages/account/login', ['error' => 'Ugyldig e-post eller passord.']);
            $html = $this->layout($content, ['title' => 'Logg inn — Min konto', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if (!password_verify($password, $user['password_hash'])) {
            $content = $this->render('pages/account/login', ['error' => 'Ugyldig e-post eller passord.']);
            $html = $this->layout($content, ['title' => 'Logg inn — Min konto', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($this->userRepo->hasRole((int) $user['id'], 'admin')) {
            $content = $this->render('pages/account/login', ['error' => 'Bruk admin-panel for å logge inn som administrator.']);
            $html = $this->layout($content, ['title' => 'Logg inn — Min konto', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        CustomerAuth::login((int) $user['id']);
        return Response::redirect(url('/konto/ordre'), 302);
    }

    public function logout(Request $request, array $params): Response
    {
        CustomerAuth::logout();
        return Response::redirect(url('/'), 302);
    }

    public function orders(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $orders = $this->orderRepo->listByUserId($userId);
        $content = $this->render('pages/account/orders', ['orders' => $orders]);
        $html = $this->layout($content, ['title' => 'Mine ordre — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function orderDetail(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $publicId = $params['id'] ?? '';
        $order = $publicId !== '' ? $this->orderRepo->findOrderByPublicIdAndUser($publicId, $userId) : null;
        if ($order === null) {
            return Response::html('<h1>404 — Ordre ikke funnet</h1>', 404);
        }
        $items = $this->orderRepo->getItems((int) $order['id']);
        $content = $this->render('pages/account/order-detail', ['order' => $order, 'items' => $items]);
        $html = $this->layout($content, ['title' => 'Ordre ' . e($order['public_id']) . ' — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function profileForm(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $user = $this->userRepo->findById($userId);
        if ($user === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $content = $this->render('pages/account/profile', ['user' => $user, 'error' => null, 'success' => null]);
        $html = $this->layout($content, ['title' => 'Min profil — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function profileUpdate(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null || !$request->isPost()) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $user = $this->userRepo->findById($userId);
        if ($user === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $email = trim($request->input('email', '') ?? '');
        $firstName = trim($request->input('first_name', '') ?? '') ?: null;
        $lastName = trim($request->input('last_name', '') ?? '') ?: null;
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $content = $this->render('pages/account/profile', ['user' => $user, 'error' => 'Ugyldig e-postadresse.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Min profil — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($this->userRepo->emailExists($email, $userId)) {
            $content = $this->render('pages/account/profile', ['user' => $user, 'error' => 'E-postadressen er allerede i bruk.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Min profil — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $this->userRepo->updateProfile($userId, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);
        $user = $this->userRepo->findById($userId);
        $content = $this->render('pages/account/profile', ['user' => $user, 'error' => null, 'success' => 'Profilen er oppdatert.']);
        $html = $this->layout($content, ['title' => 'Min profil — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function passwordForm(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $content = $this->render('pages/account/password', ['error' => null, 'success' => null]);
        $html = $this->layout($content, ['title' => 'Bytt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function passwordUpdate(Request $request, array $params): Response
    {
        $userId = CustomerAuth::userId();
        if ($userId === null || !$request->isPost()) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $current = $request->input('current_password', '') ?? '';
        $newPassword = $request->input('new_password', '') ?? '';
        $confirm = $request->input('new_password_confirm', '') ?? '';
        $user = $this->userRepo->findById($userId);
        if ($user === null) {
            return Response::redirect(url('/konto/login'), 302);
        }
        if (!password_verify($current, $user['password_hash'])) {
            $content = $this->render('pages/account/password', ['error' => 'Nåværende passord er feil.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Bytt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if (strlen($newPassword) < 8) {
            $content = $this->render('pages/account/password', ['error' => 'Nytt passord må være minst 8 tegn.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Bytt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($newPassword !== $confirm) {
            $content = $this->render('pages/account/password', ['error' => 'Bekreftelsen stemmer ikke.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Bytt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $this->userRepo->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        $content = $this->render('pages/account/password', ['error' => null, 'success' => 'Passordet er endret.']);
        $html = $this->layout($content, ['title' => 'Bytt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function forgotPasswordForm(Request $request, array $params): Response
    {
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        $content = $this->render('pages/account/forgot-password', ['error' => null, 'success' => null]);
        $html = $this->layout($content, ['title' => 'Glemt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function forgotPassword(Request $request, array $params): Response
    {
        if (!$request->isPost() || CustomerAuth::check()) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $email = trim($request->input('email', '') ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $content = $this->render('pages/account/forgot-password', ['error' => 'Skriv inn en gyldig e-postadresse.', 'success' => null]);
            $html = $this->layout($content, ['title' => 'Glemt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $user = $this->userRepo->findByEmail($email);
        if ($user !== null && !$this->userRepo->hasRole((int) $user['id'], 'admin')) {
            $this->userRepo->deleteExpiredPasswordResetTokens();
            $token = bin2hex(random_bytes(32));
            $expiresAt = new \DateTimeImmutable('+1 hour');
            $this->userRepo->createPasswordResetToken((int) $user['id'], $token, $expiresAt);
            $resetUrl = url('/konto/tilbakestill-passord?token=' . $token);
            $from = \App\Support\Env::string('MAIL_FROM', 'noreply@motorleaks.no');
            $subject = 'Tilbakestill passord — Motorleaks';
            $body = "Hei,\n\nDu ba om å tilbakestille passordet. Klikk på lenken under (gyldig i 1 time):\n\n" . $resetUrl . "\n\nHvis du ikke ba om dette, kan du se bort fra e-posten.\n\n— Motorleaks";
            $headers = 'From: ' . $from . "\r\n" . 'Content-Type: text/plain; charset=UTF-8';
            @mail($email, $subject, $body, $headers);
        }
        $content = $this->render('pages/account/forgot-password', ['error' => null, 'success' => 'Hvis e-posten finnes i vårt system, har du fått en lenke for å tilbakestille passordet. Sjekk også søppelpost.']);
        $html = $this->layout($content, ['title' => 'Glemt passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function resetPasswordForm(Request $request, array $params): Response
    {
        if (CustomerAuth::check()) {
            return Response::redirect(url('/konto/ordre'), 302);
        }
        $token = trim($request->query('token', '') ?? '');
        if ($token === '') {
            $content = $this->render('pages/account/reset-password', ['error' => 'Manglende eller ugyldig lenke.', 'token' => null]);
            $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $content = $this->render('pages/account/reset-password', ['error' => null, 'token' => $token]);
        $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    public function resetPassword(Request $request, array $params): Response
    {
        if (!$request->isPost() || CustomerAuth::check()) {
            return Response::redirect(url('/konto/login'), 302);
        }
        $token = trim($request->input('token', '') ?? '');
        $newPassword = $request->input('new_password', '') ?? '';
        $confirm = $request->input('new_password_confirm', '') ?? '';
        if ($token === '') {
            return Response::redirect(url('/konto/glemt-passord'), 302);
        }
        $row = $this->userRepo->findValidPasswordResetToken($token);
        if ($row === null) {
            $content = $this->render('pages/account/reset-password', ['error' => 'Lenken er utløpt eller ugyldig. Be om en ny lenke.', 'token' => null]);
            $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if (strlen($newPassword) < 8) {
            $content = $this->render('pages/account/reset-password', ['error' => 'Passordet må være minst 8 tegn.', 'token' => $token]);
            $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        if ($newPassword !== $confirm) {
            $content = $this->render('pages/account/reset-password', ['error' => 'Bekreftelsen stemmer ikke.', 'token' => $token]);
            $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
            return Response::html($html);
        }
        $this->userRepo->updatePassword((int) $row['user_id'], password_hash($newPassword, PASSWORD_DEFAULT));
        $this->userRepo->deletePasswordResetToken($token);
        $content = $this->render('pages/account/reset-password', ['error' => null, 'token' => null, 'success' => 'Passordet er endret. Du kan nå logge inn.']);
        $html = $this->layout($content, ['title' => 'Tilbakestill passord — Motorleaks', 'meta_description' => '', 'content' => $content]);
        return Response::html($html);
    }

    private function layout(string $content, array $data = []): string
    {
        $data['content'] = $content;
        ob_start();
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Templates/layout.php';
        return (string) ob_get_clean();
    }

    private function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__) . '/Templates/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
