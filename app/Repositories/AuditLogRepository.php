<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

final class AuditLogRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::pdo();
    }

    public function log(?int $userId, string $action, string $entityType, ?string $entityId = null, ?string $details = null, ?string $ip = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $details !== null && strlen($details) > 65535 ? substr($details, 0, 65535) : $details,
            $ip !== null && strlen($ip) > 45 ? substr($ip, 0, 45) : $ip,
        ]);
    }

    /** @return list<array{id:int, user_id:int|null, action:string, entity_type:string, entity_id:string|null, details:string|null, ip:string|null, created_at:string}> */
    public function listRecent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, action, entity_type, entity_id, details, ip, created_at FROM audit_logs ORDER BY id DESC LIMIT ' . $limit
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows ?: [];
    }
}
