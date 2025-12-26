<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Employee;
use App\Models\EmployeeWorkHour;

final class TenantEmployeeHoursController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $employees = Employee::allByTenant($tenant->tenantId);
        $employeeId = (int)$request->query('employee_id', $employees[0]->id ?? 0);
        $rows = $employeeId > 0 ? EmployeeWorkHour::listByEmployee($tenant->tenantId, $employeeId) : [];

        return $this->view('tenant/settings/employee_hours', [
            'tenant' => $tenant,
            'employees' => $employees,
            'employeeId' => $employeeId,
            'rows' => $rows,
            'message' => $request->query('message'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $employeeId = (int)$request->input('employee_id', 0);
        if ($employeeId <= 0) {
            return Response::redirect($tenant->urlPrefix() . '/settings/employee-hours');
        }

        for ($w = 0; $w <= 6; $w++) {
            $start = (string)$request->input('start_' . $w, '09:00');
            $end = (string)$request->input('end_' . $w, '18:00');
            $active = (int)$request->input('active_' . $w, 0);

            if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                continue;
            }

            EmployeeWorkHour::upsert($tenant->tenantId, $employeeId, $w, $start . ':00', $end . ':00', $active === 1 ? 1 : 0);
        }

        return Response::redirect($tenant->urlPrefix() . '/settings/employee-hours?employee_id=' . rawurlencode((string)$employeeId) . '&message=Salvo');
    }
}
