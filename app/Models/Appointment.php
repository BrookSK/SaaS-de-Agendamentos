<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Db;

final class Appointment
{
    /** @return array<int, array<string, mixed>> */
    public static function listForDay(int $tenantId, string $dateYmd): array
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT a.*, e.name AS employee_name, c.name AS client_name, s.name AS service_name
             FROM appointments a
             INNER JOIN employees e ON e.id = a.employee_id
             INNER JOIN clients c ON c.id = a.client_id
             INNER JOIN services s ON s.id = a.service_id
             WHERE a.tenant_id = :tenant_id
               AND DATE(a.starts_at) = :day
             ORDER BY a.starts_at ASC'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'day' => $dateYmd,
        ]);

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public static function listForMonth(int $tenantId, int $year, int $month): array
    {
        $month = max(1, min(12, $month));
        $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));

        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT a.*, e.name AS employee_name, c.name AS client_name, s.name AS service_name
             FROM appointments a
             INNER JOIN employees e ON e.id = a.employee_id
             INNER JOIN clients c ON c.id = a.client_id
             INNER JOIN services s ON s.id = a.service_id
             WHERE a.tenant_id = :tenant_id
               AND a.starts_at >= :start
               AND a.starts_at < :end
             ORDER BY a.starts_at ASC'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'start' => $start,
            'end' => $end,
        ]);

        return $stmt->fetchAll();
    }

    public static function create(
        int $tenantId,
        int $employeeId,
        int $clientId,
        int $serviceId,
        string $startsAt,
        string $endsAt,
        string $status = 'scheduled',
        ?string $notes = null
    ): void {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO appointments (tenant_id, employee_id, client_id, service_id, starts_at, ends_at, status, notes)
             VALUES (:tenant_id, :employee_id, :client_id, :service_id, :starts_at, :ends_at, :status, :notes)'
        );
        $stmt->execute([
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $status,
            'notes' => $notes,
        ]);
    }

    public static function hasOverlapForEmployee(int $tenantId, int $employeeId, string $startsAt, string $endsAt): bool
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS c
             FROM appointments
             WHERE tenant_id = :tenant_id
               AND employee_id = :employee_id
               AND status NOT IN (\'canceled\')
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
