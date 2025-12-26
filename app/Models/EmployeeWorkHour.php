<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class EmployeeWorkHour
{
    /** @return array<int, array<string,mixed>> */
    public static function listByTenant(int $tenantId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT ewh.*, e.name AS employee_name
             FROM employee_work_hours ewh
             INNER JOIN employees e ON e.id = ewh.employee_id
             WHERE ewh.tenant_id = :tenant_id
             ORDER BY ewh.employee_id ASC, ewh.weekday ASC'
        );
        $stmt->execute(['tenant_id' => $tenantId]);
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string,mixed>> */
    public static function listByEmployee(int $tenantId, int $employeeId): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM employee_work_hours
             WHERE tenant_id = :tenant_id AND employee_id = :employee_id
             ORDER BY weekday ASC'
        );
        $stmt->execute(['tenant_id' => $tenantId, 'employee_id' => $employeeId]);
        return $stmt->fetchAll();
    }

    public static function upsert(int $tenantId, int $employeeId, int $weekday, string $startTime, string $endTime, int $active): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO employee_work_hours (tenant_id, employee_id, weekday, start_time, end_time, active)
             VALUES (:tenant_id, :employee_id, :weekday, :start_time, :end_time, :active)
             ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), end_time = VALUES(end_time), active = VALUES(active), updated_at = NOW()'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'weekday' => $weekday,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'active' => $active,
        ]);
    }

    public static function hasAnyForEmployee(int $tenantId, int $employeeId): bool
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM employee_work_hours WHERE tenant_id = :tenant_id AND employee_id = :employee_id');
        $stmt->execute(['tenant_id' => $tenantId, 'employee_id' => $employeeId]);
        $row = $stmt->fetch();
        return is_array($row) && (int)$row['c'] > 0;
    }

    /** @return array<string,mixed>|null */
    public static function findForEmployeeWeekday(int $tenantId, int $employeeId, int $weekday): ?array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM employee_work_hours
             WHERE tenant_id = :tenant_id AND employee_id = :employee_id AND weekday = :weekday
             LIMIT 1'
        );
        $stmt->execute(['tenant_id' => $tenantId, 'employee_id' => $employeeId, 'weekday' => $weekday]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
