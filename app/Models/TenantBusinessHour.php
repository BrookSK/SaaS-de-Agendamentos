<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantBusinessHour
{
    /** @return array<int, array<string,mixed>> */
    public static function listByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_business_hours WHERE tenant_id = :tenant_id ORDER BY weekday ASC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $tenantId, int $weekday, string $openTime, string $closeTime, int $active): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO tenant_business_hours (tenant_id, weekday, open_time, close_time, active)
             VALUES (:tenant_id, :weekday, :open_time, :close_time, :active)
             ON DUPLICATE KEY UPDATE open_time = VALUES(open_time), close_time = VALUES(close_time), active = VALUES(active), updated_at = NOW()'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'weekday' => $weekday,
            'open_time' => $openTime,
            'close_time' => $closeTime,
            'active' => $active,
        ]);
    }

    /** @return array<string,mixed>|null */
    public static function findForWeekday(int $tenantId, int $weekday): ?array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_business_hours WHERE tenant_id = :tenant_id AND weekday = :weekday LIMIT 1');
        $stmt->execute(['tenant_id' => $tenantId, 'weekday' => $weekday]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
