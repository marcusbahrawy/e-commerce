<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\MenuRepository;
use App\Repositories\PageRepository;

class MenusController
{
    private const MENU_KEYS = [
        'header_main' => 'Header hovedmeny',
        'footer_1' => 'Footer kolonne 1',
        'footer_2' => 'Footer kolonne 2',
    ];

    public function __construct(
        private MenuRepository $menuRepo,
        private PageRepository $pageRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $menus = [];
        foreach (self::MENU_KEYS as $key => $name) {
            $menu = $this->menuRepo->getByKey($key);
            $menus[] = [
                'key' => $key,
                'name' => $name,
                'id' => $menu['id'] ?? null,
                'item_count' => $menu ? count($this->menuRepo->getItems((int) $menu['id'])) : 0,
            ];
        }
        $html = $this->render('admin/menus/index', ['title' => 'Menyer', 'menus' => $menus]);
        return Response::html($html);
    }

    public function editForm(Request $request, array $params): Response
    {
        $key = $params['key'] ?? '';
        if (!isset(self::MENU_KEYS[$key])) {
            return Response::html('<h1>404</h1>', 404);
        }
        $menuId = $this->menuRepo->getOrCreateMenu($key, self::MENU_KEYS[$key]);
        $items = $this->menuRepo->getItems($menuId);
        $pages = $this->pageRepo->listAllForAdmin();
        $html = $this->render('admin/menus/form', [
            'title' => 'Rediger ' . self::MENU_KEYS[$key],
            'menu_key' => $key,
            'menu_name' => self::MENU_KEYS[$key],
            'items' => $items,
            'pages' => $pages,
        ]);
        return Response::html($html);
    }

    public function update(Request $request, array $params): Response
    {
        if (!$request->isPost()) {
            return Response::redirect('/admin/menyer', 302);
        }
        $key = $params['key'] ?? '';
        if (!isset(self::MENU_KEYS[$key])) {
            return Response::redirect('/admin/menyer', 302);
        }
        $menuId = $this->menuRepo->getOrCreateMenu($key, self::MENU_KEYS[$key]);
        $items = [];
        $labels = $request->input('item_label');
        if (!is_array($labels)) {
            $labels = $labels !== null && $labels !== '' ? [$labels] : [];
        }
        $urls = $request->input('item_url');
        $urls = is_array($urls) ? $urls : [];
        $types = $request->input('item_type');
        $types = is_array($types) ? $types : [];
        foreach ($labels as $i => $label) {
                $label = trim($label ?? '');
                if ($label === '') {
                    continue;
                }
                $url = isset($urls[$i]) ? trim($urls[$i] ?? '') : '';
                $type = isset($types[$i]) ? ($types[$i] ?? 'url') : 'url';
                if ($type === 'page' && $url !== '') {
                    $url = url('/side/' . ltrim($url, '/'));
                }
                $items[] = [
                    'type' => $type,
                    'label' => $label,
                    'url' => $url ?: null,
                    'ref_id' => null,
                    'parent_id' => null,
                    'sort_order' => $i,
                    'is_active' => 1,
            ];
        }
        $this->menuRepo->setItems($menuId, $items);
        return Response::redirect('/admin/menyer', 302);
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
