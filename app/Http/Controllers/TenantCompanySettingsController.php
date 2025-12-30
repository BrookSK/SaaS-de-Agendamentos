<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;

final class TenantCompanySettingsController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/settings/company', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $tab = (string)$request->query('tab', 'company');
        $allowed = [
            'company',
            'general',
            'appearance',
            'business_hours',
            'holidays',
            'embed',
            'qr',
            'payments',
            'notifications',
        ];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'company';
        }

        return $this->view('tenant/settings/company', [
            'tenant' => $tenant,
            'tab' => $tab,
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ]);
    }
}
