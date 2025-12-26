<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Employee;
use App\Models\TenantTimeBlock;

final class TenantTimeBlocksController extends Controller
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

        return $this->view('tenant/settings/time_blocks', [
            'tenant' => $tenant,
            'employees' => Employee::allByTenant($tenant->tenantId),
            'blocks' => TenantTimeBlock::listByTenant($tenant->tenantId),
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

        $employeeIdRaw = (string)$request->input('employee_id', '');
        $employeeId = $employeeIdRaw === '' ? null : (int)$employeeIdRaw;

        $startsAt = (string)$request->input('starts_at', '');
        $endsAt = (string)$request->input('ends_at', '');
        $reason = trim((string)$request->input('reason', ''));

        if ($startsAt === '' || $endsAt === '' || strtotime($startsAt) === false || strtotime($endsAt) === false) {
            return Response::redirect($tenant->urlPrefix() . '/settings/time-blocks');
        }

        TenantTimeBlock::create($tenant->tenantId, $employeeId, $startsAt, $endsAt, $reason !== '' ? $reason : null);

        return Response::redirect($tenant->urlPrefix() . '/settings/time-blocks?message=Salvo');
    }
}
