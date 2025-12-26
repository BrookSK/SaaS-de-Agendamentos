<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class TenantTimeBlock
{
    /** @return array<int, array<string,mixed>> */
    public static function listByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT ttb.*, e.name AS employee_name
             FROM tenant_time_blocks ttb
             LEFT JOIN employees e ON e.id = ttb.employee_id
             WHERE ttb.tenant_id = :tenant_id
             ORDER BY ttb.starts_at DESC'
        );
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    public static function create(int $tenantId, ?int $employeeId, string $startsAt, string $endsAt, ?string $reason): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO tenant_time_blocks (tenant_id, employee_id, starts_at, ends_at, reason)
             VALUES (:tenant_id, :employee_id, :starts_at, :ends_at, :reason)'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $reason,
        ]);
    }

    public static function hasOverlap(int $tenantId, ?int $employeeId, string $startsAt, string $endsAt): bool
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS c
             FROM tenant_time_blocks
             WHERE tenant_id = :tenant_id
               AND (employee_id IS NULL OR employee_id = :employee_id)
               AND starts_at < :ends_at
               AND ends_at > :starts_at'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
        $row = $stmt->fetch();
        return is_array($row) && (int)$row['c'] > 0;
    }
}
