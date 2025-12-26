<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;

final class TenantDashboardController extends Controller
{
    /** @param array<string,string> $params */
    public function dashboard(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null) {
            return Response::html('Acesse com /t/{empresa}/dashboard', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        return $this->view('tenant/dashboard', [
            'user' => Auth::user(),
            'tenant' => $tenant,
        ]);
    }
}
