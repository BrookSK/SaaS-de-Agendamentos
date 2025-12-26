<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\ScheduleValidator;
use App\Core\WebhookDispatcher;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;

final class AgendaController extends Controller
{
    /** @param array<string,string> $params */
    public function day(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/agenda', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $day = (string)$request->query('day', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
            $day = date('Y-m-d');
        }

        $appointments = Appointment::listForDay($tenant->tenantId, $day);

        return $this->view('tenant/agenda/day', [
            'tenant' => $tenant,
            'day' => $day,
            'appointments' => $appointments,
            'services' => Service::allByTenant($tenant->tenantId),
            'employees' => Employee::allByTenant($tenant->tenantId),
            'clients' => Client::allByTenant($tenant->tenantId),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin', 'employee')) {
            return $resp;
        }

        $day = (string)$request->input('day', date('Y-m-d'));
        $time = (string)$request->input('time', '09:00');
        $duration = (int)$request->input('duration_minutes', 30);

        $employeeId = (int)$request->input('employee_id', 0);
        $serviceId = (int)$request->input('service_id', 0);
        $clientId = (int)$request->input('client_id', 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            return Response::redirect($tenant->urlPrefix() . '/agenda');
        }

        if ($employeeId <= 0 || $serviceId <= 0 || $clientId <= 0) {
            return Response::redirect($tenant->urlPrefix() . '/agenda?day=' . rawurlencode($day));
        }

        $startsAt = $day . ' ' . $time . ':00';
        $endsAtTs = strtotime($startsAt . ' +' . max(1, $duration) . ' minutes');
        if ($endsAtTs === false) {
            return Response::redirect($tenant->urlPrefix() . '/agenda?day=' . rawurlencode($day));
        }
        $endsAt = date('Y-m-d H:i:s', $endsAtTs);

        $validation = ScheduleValidator::validate($tenant->tenantId, $employeeId, $startsAt, $endsAt);
        if (!$validation['ok']) {
            return Response::redirect($tenant->urlPrefix() . '/agenda?day=' . rawurlencode($day) . '&error=' . rawurlencode((string)$validation['error']));
        }

        Appointment::create($tenant->tenantId, $employeeId, $clientId, $serviceId, $startsAt, $endsAt);

        Audit::log('appointment.create', 'appointment', null, [
            'employee_id' => $employeeId,
            'client_id' => $clientId,
            'service_id' => $serviceId,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        WebhookDispatcher::dispatch('appointment.created', [
            'tenant' => [
                'id' => $tenant->tenantId,
                'slug' => $tenant->slug,
            ],
            'appointment' => [
                'employee_id' => $employeeId,
                'client_id' => $clientId,
                'service_id' => $serviceId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'scheduled',
            ],
        ]);

        return Response::redirect($tenant->urlPrefix() . '/agenda?day=' . rawurlencode($day));
    }
}
