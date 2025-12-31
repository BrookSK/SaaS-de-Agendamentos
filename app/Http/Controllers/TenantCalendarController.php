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

        $view = (string)$request->query('view', 'month');
        if (!in_array($view, ['month', 'week', 'day'], true)) {
            $view = 'month';
        }

        $day = (string)$request->query('day', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $day = date('Y-m-d');
        }

        $year = (int)$request->query('y', (string)date('Y'));
        $month = (int)$request->query('m', (string)date('n'));
        if ($year < 2000 || $year > 2100) {
            $year = (int)date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }

        $appointments = [];
        $weekStart = null;
        $weekEnd = null;

        if ($view === 'month') {
            $appointments = Appointment::listForMonth($tenant->tenantId, $year, $month);
        } elseif ($view === 'day') {
            $appointments = Appointment::listForDay($tenant->tenantId, $day);
        } else {
            $ts = strtotime($day);
            $ts = $ts !== false ? $ts : time();
            $w = (int)date('w', $ts);
            $delta = $w === 0 ? -6 : (1 - $w);
            $startTs = strtotime(date('Y-m-d', $ts) . ' ' . ($delta >= 0 ? '+' : '') . $delta . ' days');
            $startTs = $startTs !== false ? $startTs : $ts;
            $endTs = strtotime(date('Y-m-d', $startTs) . ' +7 days');
            $endTs = $endTs !== false ? $endTs : ($startTs + 7 * 86400);

            $weekStart = date('Y-m-d', $startTs);
            $weekEnd = date('Y-m-d', $endTs);

            $appointments = Appointment::listBetween(
                $tenant->tenantId,
                $weekStart . ' 00:00:00',
                $weekEnd . ' 00:00:00'
            );
        }

        return $this->view('tenant/calendars/index', [
            'tenant' => $tenant,
            'view' => $view,
            'day' => $day,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'year' => $year,
            'month' => $month,
            'appointments' => $appointments,
        ]);
    }
}
