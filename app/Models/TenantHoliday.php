<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantHoliday
{
    /** @return array<int, array<string,mixed>> */
    public static function listByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM tenant_holidays WHERE tenant_id = :tenant_id ORDER BY day DESC');
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $tenantId, string $day, ?string $name, int $closed): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO tenant_holidays (tenant_id, day, name, closed)
             VALUES (:tenant_id, :day, :name, :closed)
             ON DUPLICATE KEY UPDATE name = VALUES(name), closed = VALUES(closed)'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'day' => $day,
            'name' => $name,
            'closed' => $closed,
        ]);
    }

    public static function isClosedDay(int $tenantId, string $day): bool
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT closed FROM tenant_holidays WHERE tenant_id = :tenant_id AND day = :day LIMIT 1');
        $stmt->execute(['tenant_id' => $tenantId, 'day' => $day]);
        $row = $stmt->fetch();
        return is_array($row) && (int)$row['closed'] === 1;
    }
}
