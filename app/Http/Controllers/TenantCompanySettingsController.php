<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\Employee;
use App\Models\EmployeeWorkHour;
use App\Models\TenantBusinessHour;
use App\Models\TenantHoliday;
use App\Models\TenantNotificationSetting;
use App\Models\Tenant as TenantModel;
use App\Models\TenantTimeBlock;

final class TenantCompanySettingsController extends Controller
{
    private const NOTIFICATION_EVENTS = [
        'appointment.created',
        'appointment.confirmed',
        'appointment.canceled',
        'client.created',
        'employee.created',
        'service.created',
        'finance.transaction.created',
        'finance.title.created',
        'finance.title.paid',
    ];

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

        $company = TenantModel::findById($tenant->tenantId);

        $data = [
            'tenant' => $tenant,
            'company' => $company,
            'tab' => $tab,
            'message' => $request->query('message'),
            'error' => $request->query('error'),
        ];

        if ($tab === 'business_hours') {
            $data['hours'] = TenantBusinessHour::listByTenant($tenant->tenantId);
        } elseif ($tab === 'employee_hours') {
            $employees = Employee::allByTenant($tenant->tenantId);
            $employeeId = (int)$request->query('employee_id', $employees[0]->id ?? 0);
            $rows = $employeeId > 0 ? EmployeeWorkHour::listByEmployee($tenant->tenantId, $employeeId) : [];
            $data['employees'] = $employees;
            $data['employeeId'] = $employeeId;
            $data['rows'] = $rows;
        } elseif ($tab === 'holidays') {
            $data['holidays'] = TenantHoliday::listByTenant($tenant->tenantId);
        } elseif ($tab === 'time_blocks') {
            $data['employees'] = Employee::allByTenant($tenant->tenantId);
            $data['blocks'] = TenantTimeBlock::listByTenant($tenant->tenantId);
        } elseif ($tab === 'notifications') {
            $rows = TenantNotificationSetting::listByTenant($tenant->tenantId);
            $map = [];
            foreach ($rows as $r) {
                $map[(string)$r['event_name']] = $r;
            }
            $data['events'] = self::NOTIFICATION_EVENTS;
            $data['map'] = $map;
        }

        return $this->view('tenant/settings/company', $data);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        $tenant = Tenant::current();
        if ($tenant === null || $tenant->tenantId === null) {
            return Response::html('Tenant inválido. Acesse via /t/{slug}/settings/company', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $tab = (string)$request->input('tab', 'company');
        $allowed = [
            'company',
            'business_hours',
            'employee_hours',
            'holidays',
            'time_blocks',
            'notifications',
        ];
        if (!in_array($tab, $allowed, true)) {
            $tab = 'company';
        }

        if ($tab === 'business_hours') {
            for ($w = 0; $w <= 6; $w++) {
                $open = (string)$request->input('open_' . $w, '09:00');
                $close = (string)$request->input('close_' . $w, '18:00');
                $active = (int)$request->input('active_' . $w, 0);

                if (!preg_match('/^\d{2}:\d{2}$/', $open) || !preg_match('/^\d{2}:\d{2}$/', $close)) {
                    continue;
                }

                TenantBusinessHour::upsert($tenant->tenantId, $w, $open . ':00', $close . ':00', $active === 1 ? 1 : 0);
            }

            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=business_hours&message=Salvo');
        }

        if ($tab === 'employee_hours') {
            $employeeId = (int)$request->input('employee_id', 0);
            if ($employeeId <= 0) {
                return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=employee_hours');
            }

            for ($w = 0; $w <= 6; $w++) {
                $start = (string)$request->input('start_' . $w, '09:00');
                $end = (string)$request->input('end_' . $w, '18:00');
                $active = (int)$request->input('active_' . $w, 0);

                if (!preg_match('/^\d{2}:\d{2}$/', $start) || !preg_match('/^\d{2}:\d{2}$/', $end)) {
                    continue;
                }

                EmployeeWorkHour::upsert($tenant->tenantId, $employeeId, $w, $start . ':00', $end . ':00', $active === 1 ? 1 : 0);
            }

            return Response::redirect(
                $tenant->urlPrefix() . '/settings/company?tab=employee_hours&employee_id=' . rawurlencode((string)$employeeId) . '&message=Salvo'
            );
        }

        if ($tab === 'holidays') {
            $day = (string)$request->input('day', '');
            $name = trim((string)$request->input('name', ''));
            $closed = (int)$request->input('closed', 1);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
                return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=holidays');
            }

            TenantHoliday::upsert($tenant->tenantId, $day, $name !== '' ? $name : null, $closed === 1 ? 1 : 0);

            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=holidays&message=Salvo');
        }

        if ($tab === 'time_blocks') {
            $employeeIdRaw = (string)$request->input('employee_id', '');
            $employeeId = $employeeIdRaw === '' ? null : (int)$employeeIdRaw;
            $startsAt = (string)$request->input('starts_at', '');
            $endsAt = (string)$request->input('ends_at', '');
            $reason = trim((string)$request->input('reason', ''));

            if ($startsAt === '' || $endsAt === '' || strtotime($startsAt) === false || strtotime($endsAt) === false) {
                return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=time_blocks');
            }

            TenantTimeBlock::create($tenant->tenantId, $employeeId, $startsAt, $endsAt, $reason !== '' ? $reason : null);

            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=time_blocks&message=Salvo');
        }

        if ($tab === 'notifications') {
            foreach (self::NOTIFICATION_EVENTS as $event) {
                $notifyClient = (int)$request->input($event . '_notify_client', 0);
                $notifyEmployee = (int)$request->input($event . '_notify_employee', 0);
                $notifyAdmin = (int)$request->input($event . '_notify_admin', 0);
                $channelEmail = (int)$request->input($event . '_channel_email', 0);
                $channelWebhook = (int)$request->input($event . '_channel_webhook', 1);

                $subject = trim((string)$request->input($event . '_subject', ''));
                $body = trim((string)$request->input($event . '_body', ''));

                $channels = [
                    'email' => $channelEmail === 1,
                    'webhook' => $channelWebhook === 1,
                ];

                TenantNotificationSetting::upsert(
                    $tenant->tenantId,
                    $event,
                    $notifyClient === 1 ? 1 : 0,
                    $notifyEmployee === 1 ? 1 : 0,
                    $notifyAdmin === 1 ? 1 : 0,
                    $channels,
                    $subject !== '' ? $subject : null,
                    $body !== '' ? $body : null
                );
            }

            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=notifications&message=Salvo');
        }

        $name = trim((string)$request->input('name', ''));
        $email = trim((string)$request->input('email', ''));
        $phone = trim((string)$request->input('phone', ''));
        $cpfCnpj = trim((string)$request->input('cpf_cnpj', ''));

        if ($name === '') {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=company&error=' . rawurlencode('Informe o nome da empresa.'));
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=company&error=' . rawurlencode('E-mail inválido.'));
        }

        $t = TenantModel::findById($tenant->tenantId);
        if ($t === null) {
            return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=company&error=' . rawurlencode('Empresa não encontrada.'));
        }

        TenantModel::updateById(
            $t->id,
            $name,
            $t->slug,
            $t->status,
            $email !== '' ? $email : null,
            $phone !== '' ? $phone : null,
            $cpfCnpj !== '' ? $cpfCnpj : null
        );

        return Response::redirect($tenant->urlPrefix() . '/settings/company?tab=company&message=Salvo');
    }
}
