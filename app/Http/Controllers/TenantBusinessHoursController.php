<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\TenantBusinessHour;

final class TenantBusinessHoursController extends Controller
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

        $hours = TenantBusinessHour::listByTenant($tenant->tenantId);

        return $this->view('tenant/settings/business_hours', [
            'tenant' => $tenant,
            'hours' => $hours,
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

        for ($w = 0; $w <= 6; $w++) {
            $open = (string)$request->input('open_' . $w, '09:00');
            $close = (string)$request->input('close_' . $w, '18:00');
            $active = (int)$request->input('active_' . $w, 0);

            if (!preg_match('/^\d{2}:\d{2}$/', $open) || !preg_match('/^\d{2}:\d{2}$/', $close)) {
                continue;
            }

            TenantBusinessHour::upsert($tenant->tenantId, $w, $open . ':00', $close . ':00', $active === 1 ? 1 : 0);
        }

        return Response::redirect($tenant->urlPrefix() . '/settings/business-hours?message=Salvo');
    }
}
