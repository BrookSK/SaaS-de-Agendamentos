<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\WebhookDispatcher;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Employee;

final class EmployeesController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/employees', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $employees = Employee::allByTenant($tenant->tenantId);

        return $this->view('tenant/employees/index', [
            'tenant' => $tenant,
            'employees' => $employees,
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        if ($name === '') {
            return Response::redirect($tenant->urlPrefix() . '/employees');
        }

        Employee::create($tenant->tenantId, $name);

        Audit::log('employee.create', 'employee', null, [
            'name' => $name,
        ]);

        WebhookDispatcher::dispatch('employee.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'employee' => [
                'name' => $name,
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/employees');
    }
}
