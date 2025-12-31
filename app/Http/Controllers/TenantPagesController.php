<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;

final class TenantPagesController extends Controller
{
    /** @param array<string,string> $params */
    public function affiliate(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        return $this->view('tenant/affiliate/index', [
            'tenant' => $tenant,
        ]);
    }

    /** @param array<string,string> $params */
    public function domain(Request $request, array $params): Response
    {
        return $this->placeholder($request, 'Domínio personalizado', 'Em breve');
    }

    /** @param array<string,string> $params */
    private function placeholder(Request $request, string $title, string $subtitle): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        return $this->view('tenant/placeholder', [
            'tenant' => $tenant,
            'title' => $title,
            'subtitle' => $subtitle,
        ]);
    }
}
