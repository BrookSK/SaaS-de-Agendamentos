<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\TenantHoliday;

final class TenantHolidaysController extends Controller
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

        return $this->view('tenant/settings/holidays', [
            'tenant' => $tenant,
            'holidays' => TenantHoliday::listByTenant($tenant->tenantId),
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

        $day = (string)$request->input('day', '');
        $name = trim((string)$request->input('name', ''));
        $closed = (int)$request->input('closed', 1);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            return Response::redirect($tenant->urlPrefix() . '/settings/holidays');
        }

        TenantHoliday::upsert($tenant->tenantId, $day, $name !== '' ? $name : null, $closed === 1 ? 1 : 0);

        return Response::redirect($tenant->urlPrefix() . '/settings/holidays?message=Salvo');
    }
}
