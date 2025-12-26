<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class AsaasWebhookEvent
{
    public static function create(?string $eventType, ?string $resourceId, string $payloadJson): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO asaas_webhook_events (event_type, resource_id, payload_json) VALUES (:event_type, :resource_id, CAST(:payload_json AS JSON))');
        $stmt->execute([
            'event_type' => $eventType,
            'resource_id' => $resourceId,
            'payload_json' => $payloadJson,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function markProcessed(int $id): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE asaas_webhook_events SET processed_at = NOW(), processing_error = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function markFailed(int $id, string $error): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('UPDATE asaas_webhook_events SET processed_at = NOW(), processing_error = :err WHERE id = :id');
        $stmt->execute(['id' => $id, 'err' => $error]);
    }

    /** @return array<int, array<string,mixed>> */
    public static function latest(int $limit = 50): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT id, event_type, resource_id, received_at, processed_at, processing_error FROM asaas_webhook_events ORDER BY id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
