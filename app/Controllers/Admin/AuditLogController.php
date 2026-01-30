<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;

class AuditLogController
{
    public function __construct(
        private AuditLogRepository $auditLogRepo,
        private UserRepository $userRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $limit = max(1, min(500, (int) ($request->query('limit', '100') ?? 100)));
        $logs = $this->auditLogRepo->listRecent($limit);
        $userIds = array_unique(array_filter(array_column($logs, 'user_id')));
        $users = [];
        foreach ($userIds as $id) {
            $u = $this->userRepo->findById((int) $id);
            if ($u !== null) {
                $users[(int) $id] = $u['email'] ?? '#' . $id;
            }
        }
        $html = $this->render('admin/audit/index', [
            'title' => 'Audit-logg',
            'logs' => $logs,
            'users' => $users,
        ]);
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
