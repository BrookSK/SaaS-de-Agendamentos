<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class AuditLog
{
    /** @param array<string,mixed>|null $metadata */
    public static function create(?int $tenantId, ?int $userId, string $action, ?string $entity, ?int $entityId, ?array $metadata, ?string $ip): void
    {
        $pdo = Db::pdo();

        $metadataJson = null;
        if ($metadata !== null) {
            $encoded = json_encode($metadata);
            $metadataJson = is_string($encoded) ? $encoded : null;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (tenant_id, user_id, action, entity, entity_id, metadata_json, ip)
             VALUES (:tenant_id, :user_id, :action, :entity, :entity_id, CAST(:metadata_json AS JSON), :ip)'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'metadata_json' => $metadataJson,
            'ip' => $ip,
        ]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function latest(?int $tenantId, int $limit = 50): array
    {
        $pdo = Db::pdo();
        if ($tenantId === null) {
            $stmt = $pdo->prepare('SELECT * FROM audit_logs ORDER BY id DESC LIMIT :lim');
            $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT * FROM audit_logs WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
