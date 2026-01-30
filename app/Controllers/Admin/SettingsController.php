<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\SettingsRepository;

class SettingsController
{
    private const EDITABLE_KEYS = [
        'site_name' => ['label' => 'Sidenavn', 'type' => 'text'],
        'contact_email' => ['label' => 'Kontakt e-post', 'type' => 'email'],
        'contact_phone' => ['label' => 'Kontakt telefon', 'type' => 'text'],
        'footer_text' => ['label' => 'Footer-tekst', 'type' => 'textarea'],
    ];

    public function __construct(private SettingsRepository $settingsRepo)
    {
    }

    public function index(Request $request, array $params): Response
    {
        $settings = $this->settingsRepo->all();
        $html = $this->render('admin/settings/index', [
            'title' => 'Innstillinger',
            'settings' => $settings,
            'keys' => self::EDITABLE_KEYS,
        ]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/innstillinger', 302);
        }
        foreach (array_keys(self::EDITABLE_KEYS) as $key) {
            $value = trim($request->input('setting_' . $key, '') ?? '');
            $this->settingsRepo->set($key, $value);
        }
        return Response::redirect('/admin/innstillinger?ok=1', 302);
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
