<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantNotificationSetting
{
    /** @return array<int, array<string,mixed>> */
    public static function listByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_notification_settings WHERE tenant_id = :tenant_id ORDER BY event_name ASC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public static function upsert(
        int $tenantId,
        string $eventName,
        int $notifyClient,
        int $notifyEmployee,
        int $notifyAdmin,
        ?array $channels,
        ?string $subject,
        ?string $body
    ): void {
        $pdo = Db::pdo();

        $channelsJson = null;
        if ($channels !== null) {
            $encoded = json_encode($channels);
            $channelsJson = is_string($encoded) ? $encoded : null;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO tenant_notification_settings
                (tenant_id, event_name, notify_client, notify_employee, notify_admin, channels_json, template_subject, template_body)
             VALUES
                (:tenant_id, :event_name, :notify_client, :notify_employee, :notify_admin, CAST(:channels_json AS JSON), :template_subject, :template_body)
             ON DUPLICATE KEY UPDATE
                notify_client = VALUES(notify_client),
                notify_employee = VALUES(notify_employee),
                notify_admin = VALUES(notify_admin),
                channels_json = VALUES(channels_json),
                template_subject = VALUES(template_subject),
                template_body = VALUES(template_body),
                updated_at = NOW()'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'event_name' => $eventName,
            'notify_client' => $notifyClient,
            'notify_employee' => $notifyEmployee,
            'notify_admin' => $notifyAdmin,
            'channels_json' => $channelsJson,
            'template_subject' => $subject,
            'template_body' => $body,
        ]);
    }
}
