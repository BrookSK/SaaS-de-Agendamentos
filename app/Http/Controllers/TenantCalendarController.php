<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Appointment;

final class TenantCalendarController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/calendars', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $year = (int)$request->query('y', (string)date('Y'));
        $month = (int)$request->query('m', (string)date('n'));
        if ($year < 2000 || $year > 2100) {
            $year = (int)date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        $appointments = Appointment::listForMonth($tenant->tenantId, $year, $month);

        return $this->view('tenant/calendars/index', [
            'tenant' => $tenant,
            'year' => $year,
            'month' => $month,
            'appointments' => $appointments,
        ]);
    }
}
