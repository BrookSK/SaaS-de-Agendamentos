<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class SystemWebhookDelivery
{
    /** @return array<int, array<string,mixed>> */
    public static function latest(int $limit = 200): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, w.url
             FROM system_webhook_deliveries d
             INNER JOIN system_webhooks w ON w.id = d.webhook_id
             ORDER BY d.id DESC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function due(int $limit = 50): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.*, w.url, w.secret
             FROM system_webhook_deliveries d
             INNER JOIN system_webhooks w ON w.id = d.webhook_id
             WHERE d.status = "failed" AND d.next_attempt_at IS NOT NULL AND d.next_attempt_at <= NOW()
             ORDER BY d.next_attempt_at ASC
             LIMIT :lim'
        );
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
