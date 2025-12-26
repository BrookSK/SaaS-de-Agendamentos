<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Appointment;
use App\Models\FinancialTransaction;

final class TenantReportsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $from = (string)$request->query('from', date('Y-m-01'));
        $to = (string)$request->query('to', date('Y-m-t'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $appointments = $this->appointmentsByPeriod($tenant->tenantId, $from, $to);
        $financeTotals = FinancialTransaction::totalsByPeriod($tenant->tenantId, $from, $to);

        return $this->view('tenant/reports/index', [
            'tenant' => $tenant,
            'from' => $from,
            'to' => $to,
            'appointments' => $appointments,
            'financeTotals' => $financeTotals,
        ]);
    }

    /** @param array<string,string> $params */
    public function appointmentsCsv(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $from = (string)$request->query('from', date('Y-m-01'));
        $to = (string)$request->query('to', date('Y-m-t'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $rows = $this->appointmentsByPeriod($tenant->tenantId, $from, $to);

        $csv = "starts_at,ends_at,employee,service,client,status\n";
        foreach ($rows as $r) {
            $csv .= $this->csvLine([
                (string)$r['starts_at'],
                (string)$r['ends_at'],
                (string)$r['employee_name'],
                (string)$r['service_name'],
                (string)$r['client_name'],
                (string)$r['status'],
            ]);
        }

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="appointments.csv"',
        ]);
    }

    /** @param array<string,string> $params */
    public function financeCsv(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $from = (string)$request->query('from', date('Y-m-01'));
        $to = (string)$request->query('to', date('Y-m-t'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }

        $items = FinancialTransaction::listByPeriod($tenant->tenantId, $from, $to);
        $csv = "occurred_on,type,amount_cents,category,description\n";
        foreach ($items as $it) {
            $csv .= $this->csvLine([
                (string)$it['occurred_on'],
                (string)$it['type'],
                (string)$it['amount_cents'],
                (string)($it['category'] ?? ''),
                (string)($it['description'] ?? ''),
            ]);
        }

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="finance.csv"',
        ]);
    }

    /** @return array<int, array<string,mixed>> */
    private function appointmentsByPeriod(int $tenantId, string $fromYmd, string $toYmd): array
    {
        // reusing Appointment model via a custom query (keeps minimal changes)
        $pdo = \App\Core\Db::pdo();
        $stmt = $pdo->prepare(
            'SELECT a.*, e.name AS employee_name, c.name AS client_name, s.name AS service_name
             FROM appointments a
             INNER JOIN employees e ON e.id = a.employee_id
             INNER JOIN clients c ON c.id = a.client_id
             INNER JOIN services s ON s.id = a.service_id
             WHERE a.tenant_id = :tenant_id
               AND DATE(a.starts_at) BETWEEN :from AND :to
             ORDER BY a.starts_at ASC'
        );
        $stmt->execute(['tenant_id' => $tenantId, 'from' => $fromYmd, 'to' => $toYmd]);
        return $stmt->fetchAll();
    }

    /** @param array<int, string> $fields */
    private function csvLine(array $fields): string
    {
        $escaped = array_map(function (string $v): string {
            $v = str_replace('"', '""', $v);
            return '"' . $v . '"';
        }, $fields);

        return implode(',', $escaped) . "\n";
    }
}
