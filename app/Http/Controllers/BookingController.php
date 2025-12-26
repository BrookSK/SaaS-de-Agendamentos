<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\ScheduleValidator;
use App\Core\Tenant;
use App\Core\WebhookDispatcher;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;

final class BookingController extends Controller
{
    /** @param array<string,string> $params */
    public function show(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Página de agendamento deve ser acessada via /t/{slug}/book', 400);
        }

        return $this->view('public/book', [
            'tenant' => $tenant,
            'services' => Service::allByTenant($tenant->tenantId),
            'employees' => Employee::allByTenant($tenant->tenantId),
            'error' => $request->query('error'),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido.', 400);
        }

        $clientName = trim((string)$request->input('client_name', ''));
        $clientPhone = trim((string)$request->input('client_phone', ''));
        $clientEmail = trim((string)$request->input('client_email', ''));

        $serviceId = (int)$request->input('service_id', 0);
        $employeeId = (int)$request->input('employee_id', 0);

        $day = (string)$request->input('day', date('Y-m-d'));
        $time = (string)$request->input('time', '09:00');
        $duration = (int)$request->input('duration_minutes', 30);

        if ($clientName === '' || $serviceId <= 0 || $employeeId <= 0) {
            return Response::redirect($tenant->urlPrefix() . '/book');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
            return Response::redirect($tenant->urlPrefix() . '/book');
        }

        $clientId = Client::create(
            $tenant->tenantId,
            $clientName,
            $clientPhone !== '' ? $clientPhone : null,
            $clientEmail !== '' ? $clientEmail : null
        );

        $startsAt = $day . ' ' . $time . ':00';
        $endsAtTs = strtotime($startsAt . ' +' . max(1, $duration) . ' minutes');
        if ($endsAtTs === false) {
            return Response::redirect($tenant->urlPrefix() . '/book');
        }
        $endsAt = date('Y-m-d H:i:s', $endsAtTs);

        $validation = ScheduleValidator::validate($tenant->tenantId, $employeeId, $startsAt, $endsAt);
        if (!$validation['ok']) {
            return Response::redirect($tenant->urlPrefix() . '/book?error=' . rawurlencode((string)$validation['error']));
        }

        Appointment::create($tenant->tenantId, $employeeId, $clientId, $serviceId, $startsAt, $endsAt, 'scheduled');

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
                'source' => 'public_booking',
            ],
        ]);

        return $this->view('public/book_success', [
            'tenant' => $tenant,
            'startsAt' => $startsAt,
        ]);
    }
}
