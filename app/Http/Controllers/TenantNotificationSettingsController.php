<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Tenant;
use App\Models\TenantNotificationSetting;

final class TenantNotificationSettingsController extends Controller
{
    private const EVENTS = [
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
            return Response::html('Tenant inválido', 400);
        }

        if ($resp = Auth::requireRole('tenant_admin')) {
            return $resp;
        }

        $rows = TenantNotificationSetting::listByTenant($tenant->tenantId);
        $map = [];
        foreach ($rows as $r) {
            $map[(string)$r['event_name']] = $r;
        }

        return $this->view('tenant/settings/notifications', [
            'tenant' => $tenant,
            'events' => self::EVENTS,
            'map' => $map,
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

        foreach (self::EVENTS as $event) {
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

        return Response::redirect($tenant->urlPrefix() . '/settings/notifications?message=Salvo');
    }
}
